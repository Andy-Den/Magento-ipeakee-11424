<?php

class Wisepricer_Syncer_Model_Reprice extends Mage_Core_Model_Abstract{

    private $_parrentIds=array();

    private function _getConnection($type = 'core_read'){
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    private function _getTableName($tableName){
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    private function _getAttributeId($attribute_code = 'price'){
        $connection = $this->_getConnection('core_read');
        $sql = "SELECT attribute_id
                    FROM " . $this->_getTableName('eav_attribute') . "
                WHERE
                    entity_type_id = ?
                    AND attribute_code = ?";
        $entity_type_id = $this->_getEntityTypeId();
        return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
    }

    private function _getEntityTypeId($entity_type_code = 'catalog_product'){
        $connection = $this->_getConnection('core_read');
        $sql        = "SELECT entity_type_id FROM " . $this->_getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
        return $connection->fetchOne($sql, array($entity_type_code));
    }

    private function _getIdFromSku($sku){
        $connection = $this->_getConnection('core_read');
        $sql        = "SELECT entity_id FROM " . $this->_getTableName('catalog_product_entity') . " WHERE sku = ?";
        return $connection->fetchOne($sql, array($sku));

    }

    private function _getConfigurableIds($productId, $newPrice){

        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId);

        foreach($parentIds as $parId) {

            if(!array_key_exists($parId,$this->_parrentIds) || $this->_parrentIds[$parId] > $newPrice){
                $this->_parrentIds[$parId]= $newPrice;
            }
        }
    }

    private function _getSpecialPrice($prodId,$spAttrId){

       $connection     = $this->_getConnection('core_write');
       $sql    ="SELECT value FROM " . $this->_getTableName('catalog_product_entity_decimal') . " WHERE entity_id = ? AND attribute_id = ?";
       $res=$connection->fetchOne($sql, array($prodId,$spAttrId));
       return $res;
    }

    public function checkIfSkuExists($sku){
        $connection = $this->_getConnection('core_read');
        $sql        = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName('catalog_product_entity') . " WHERE sku = ?";
        $count      = $connection->fetchOne($sql, array($sku));
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function checkIfIdExists($sku){
        $connection = $this->_getConnection('core_read');
        $sql        = "SELECT COUNT(*) AS count_no FROM " . $this->_getTableName('catalog_product_entity') . " WHERE entity_id = ?";
        $count      = $connection->fetchOne($sql, array($sku));
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function updatePricesBySku($prodArr,$price_field){
        $connection     = $this->_getConnection('core_write');

        if(!is_array($prodArr)){
            $sku            = $prodArr->sku;
            $newPrice       = $prodArr->price;
        }else{
            $sku            = $prodArr['sku'];
            $newPrice       = $prodArr['price'];
        }
      try{
        $productId      = $this->_getIdFromSku($sku);
        $attributeId    = $this->_getAttributeId($price_field);
        $spAttributeId  = $this->_getAttributeId('special_price');
        $specialPrice   = $this->_getSpecialPrice($productId,$spAttributeId);

        if($specialPrice){
          $attributeId= $spAttributeId;
        }

          if($newPrice <= 0){
              throw new Exception("Price [$newPrice] is invalid Product Id: $productId");
          }

          $sql = "UPDATE " . $this->_getTableName('catalog_product_entity_decimal') . " cped
                    SET  cped.value = ?
                WHERE  cped.attribute_id = ?
                AND cped.entity_id = ?";
        $connection->query($sql, array($newPrice, $attributeId, $productId));
        $this->_getConfigurableIds($productId, $newPrice);

      }catch(Exception $e){
          Mage::log($e->getMessage(),null,'wplog.log');
          echo $e->getMessage();
      }
    }

    public function updatePricesById($prodArr,$price_field){
        $connection     = $this->_getConnection('core_write');

        if(!is_array($prodArr)){
            $productId      = $prodArr->sku;
            $newPrice       = $prodArr->price;
        }else{
            $productId      = $prodArr['sku'];
            $newPrice       = $prodArr['price'];
        }
        try{
             $attributeId    = $this->_getAttributeId($price_field);

             $spAttributeId  = $this->_getAttributeId('special_price');
             $specialPrice   = $this->_getSpecialPrice($productId,$spAttributeId);

             if($specialPrice){
                $attributeId= $spAttributeId;
             }

            if($newPrice <= 0){
                throw new Exception("Price [$newPrice] is invalid Product Id: $productId");
            }

              $sql = "UPDATE " . $this->_getTableName('catalog_product_entity_decimal') . " cped
                    SET  cped.value = ?
                WHERE  cped.attribute_id = ?
                AND cped.entity_id = ?";
             $connection->query($sql, array($newPrice, $attributeId, $productId));
             $this->_getConfigurableIds($productId, $newPrice);

        }catch(Exception $e){
            Mage::log($e->getMessage(),null,'wplog.log');
            echo $e->getMessage();
        }
    }

    public function getParrentIds(){
        return $this->_parrentIds;
    }

    public function repriceConfigurable($productId, $newPrice, $priceField){
        $product = Mage::getModel('catalog/product')->load($productId);
        $oldPrice = $this->getProductPrice($product);

        if($oldPrice <= $newPrice){
            throw new Exception("New price is larger or equal to old price Product Id: $productId");
        }

       $prodArr = array();
       $prodArr['sku'] = $productId;
       $prodArr['price'] = $newPrice;
       $this->updatePricesById($prodArr, $priceField);
       return $newPrice;
    }

    private function getProductPrice($product){
        $calcPriceRule = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product,$product->getPrice());
        if(isset($calcPriceRule) && $calcPriceRule > 0){
            return $calcPriceRule;
        }

        $specialPrice = $product->getSpecialPrice();
        if(isset($specialPrice) && $specialPrice > 0){
            return $specialPrice;
        }

        $finalPrice = $product->getFinalPrice();
        if(isset($finalPrice) && $finalPrice > 0){
            return $finalPrice;
        }

        return $product->getPrice();
    }
}
?>