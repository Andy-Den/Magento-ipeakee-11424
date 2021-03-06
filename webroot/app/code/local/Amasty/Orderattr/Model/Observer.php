<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */
class Amasty_Orderattr_Model_Observer
{
    protected $_attributes = null;
    protected $_permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
    protected $_exportActions = array('exportCsv', 'exportExcel');
    protected $_controllerNames = array('sales_', 'orderspro_');
    protected $_otherClasses = array('Mage_Adminhtml_Block_Sales_Order_Grid',
                                     'EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid',
                                     'MageWorx_Adminhtml_Block_Orderspro_Sales_Order_Grid',
                                     'Excellence_Salesgrid_Block_Adminhtml_Sales_Order_Grid',
                                     'AW_Ordertags_Block_Adminhtml_Sales_Order_Grid',
                                     'Raveinfosys_Deleteorder_Block_Adminhtml_Sales_Order_Grid',
                                     'Magik_Magikfees_Block_Adminhtml_Sales_Order_Grid');
    
    protected function _prepareOrderAttributes()
    {
        if (Mage::app()->getRequest()->getPost('amorderattr'))
        {
            $checkout = Mage::getSingleton('checkout/type_onepage')->getCheckout();
            $orderAttributes = $checkout->getAmastyOrderAttributes();
            if (!$orderAttributes)
            {
                $orderAttributes = array();
            }
            if (!Mage::registry('attributeClear')){
                $orderAttributes = array_merge($orderAttributes, Mage::app()->getRequest()->getPost('amorderattr'));
            }
            $checkout->setAmastyOrderAttributes($orderAttributes);
            Mage::getSingleton('customer/session')->setAmastyOrderAttributes($orderAttributes);
        }
    }
    
    public function onSalesQuoteSaveAfter($observer)
    {
        $this->_prepareOrderAttributes();
    }
    
    public function onCheckoutTypeOnepageSaveOrderAfter($observer)
    {
        if(!Mage::registry('amorderattr_saved')){
            $this->_prepareOrderAttributes();
            $order = $observer->getOrder();
            if (!$order->getId()) {
                return false;
            }
            $checkout = Mage::getSingleton('checkout/type_onepage')->getCheckout();
            $orderAttributes = $checkout->getAmastyOrderAttributes();
            if (!$orderAttributes) {
                $orderAttributes = Mage::getSingleton('customer/session')->getAmastyOrderAttributes();
            }
            $attributes = Mage::getModel('amorderattr/attribute');
            $attributes->load($order->getId(), 'order_id');
            if ($attributes->getId())
            {
                return false;
            }
           
            if (is_array($orderAttributes) && !empty($orderAttributes))
            {
                $collection = Mage::getModel('eav/entity_attribute')->getCollection();
                $collection->addFieldToFilter('is_visible_on_front', 1);
                $collection->addFieldToFilter('entity_type_id',Mage::getModel('eav/entity')->setType('order')->getTypeId());
                $attributesList = $collection->load();
                
                foreach ($attributesList as $attribute)
                {                   
                    if ('checkboxes' == $attribute->getFrontend()->getInputType())
                    {
                       if (array_key_exists($attribute->getAttributeCode(), $orderAttributes)) {
                           $orderAttributes[$attribute->getAttributeCode()] = implode(',', $orderAttributes[$attribute->getAttributeCode()]);
                       }
                       
                    }
                    if ('radios' == $attribute->getFrontend()->getInputType()){
                        $orderAttributes[$attribute->getAttributeCode()] = $orderAttributes[$attribute->getAttributeCode()][0];
                    }
                    if ('file' == $attribute->getFrontend()->getInputType()){
                        $fileName =  $orderAttributes[$attribute->getAttributeCode()];
                        $dir = Mage::getBaseDir('media') . DS . 'amorderattr' . DS . 'tmp';
                        $file = $dir . $fileName;
                        if ( file_exists($file) && $fileName ) {
                            try {
                                $newPath = Mage::getBaseDir('media') . DS . 'amorderattr' . DS . 'original' . $fileName;
                                $pos = strrpos($newPath, "/");
                                if ($pos) {
                                    $destinationDir = substr($newPath, 0, $pos);
                                    if (!is_dir($destinationDir)) {
                                        mkdir($destinationDir, 0755, true);
                                    }
                                }
                                $result = rename(
                                    $file, $newPath
                                );
                            }
                            catch(Exception $ex){
                                $orderAttributes[$attribute->getAttributeCode()] = '';
                            }
                        }
                    }
                }
                $attributes->addData($orderAttributes);  
            }
            
            $attributes->setData('order_id', $order->getId());
            $this->_applyDefaultValues($order, $attributes);
            $attributes->save();
            Mage::register('amorderattr_saved', true, true);
            $checkout->setAmastyOrderAttributes(array());
            Mage::register('attributeClear',true, true);
        }
    }

    public function onSalesOrderSaveBefore($observer)
    {
        if (false !== strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order')
            && 'save' == Mage::app()->getRequest()->getActionName()
            && !Mage::registry('amorderattr_saved')
            && $orderAttributes = Mage::app()->getRequest()->getPost('amorderattr'))
        {
            Mage::getSingleton('adminhtml/session')->setAmastyOrderAttributes($orderAttributes);
        }

    }

    /**
     * @param $observer
     */
    public function salesOrderPlaceBefore($observer)
    {
        $this->_checkRequiredFields();
    }

    /**
     * check if all required attributes exist in data
     */
    protected function _checkRequiredFields()
    {

        $checkout = Mage::getSingleton('checkout/type_onepage')->getCheckout();
        $orderAttributes = $checkout->getAmastyOrderAttributes();
        if (!$orderAttributes) {
            $orderAttributes = Mage::getSingleton('customer/session')->getAmastyOrderAttributes();
        }

        $entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
        $collection = Mage::getModel('eav/entity_attribute')->getCollection()
            ->addFieldToFilter('is_visible_on_front', 1)
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('is_required', 1);
        
        $currentStore = Mage::app()->getStore()->getId();

        foreach ($collection as $attribute) {
            $storeIds = explode(',', $attribute->getData('store_ids'));
            if (!in_array($currentStore, $storeIds)
            && !in_array(0, $storeIds)) {
                continue;
            }
            $code = $attribute->getAttributeCode();
            if (!array_key_exists($code, $orderAttributes) || $orderAttributes[$code] == "") {
                Mage::throwException(
                    Mage::helper('amorderattr')->__(
                        'Please fill in required field \"%s\" to complete the checkout.',
                        $attribute->getFrontendLabel()
                    )
                );
            }
        }
    }
    
    // this will be used when creating/editing order in the backend
    public function onSalesOrderSaveAfter($observer)
    {
        if (false !== strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order')
            && 'save' == Mage::app()->getRequest()->getActionName() && !Mage::registry('amorderattr_saved')
        ) {
            $checkout = Mage::getSingleton('checkout/type_onepage')->getCheckout();
            $orderAttributes = $checkout->getAmastyOrderAttributes();

            $order = $observer->getOrder();
            if (!$order->getId()) {
                return false;
            }
            //$orderAttributes = Mage::app()->getRequest()->getPost('amorderattr');
            
            $attributes = Mage::getModel('amorderattr/attribute');
            $attributes->load($order->getId(), 'order_id');
            if ($attributes->getId()) {
                return false;
            }
            
            if (is_array($orderAttributes) && !empty($orderAttributes)) {
                foreach ($orderAttributes as $key => $val) {
                    if ($val) {
                        if (is_array($val)) {
                           $val = implode(', ', $val);
                        } else {
                            //for attribute type = file
                            $dir = Mage::getBaseDir('media') . DS . 'amorderattr' . DS . 'tmp';
                            $file = $dir . $val;
                            if (file_exists($file) && $val) {
                                try {
                                    $newPath = Mage::getBaseDir('media') . DS . 'amorderattr' . DS . 'original' . $val;
                                    $pos = strrpos($newPath, "/");
                                    if ($pos) {
                                        $destinationDir = substr($newPath, 0, $pos);
                                        if (!is_dir($destinationDir)) {
                                            mkdir($destinationDir, 0755, true);
                                        }
                                    }
                                    $result = rename(
                                        $file, $newPath
                                    );
                                } catch (Exception $ex) {
                                    $val = '';
                                }
                            }
                        }

                        $attributes->setData($key, $val);
                    }
                }
            }
           
            $attributes->setData('order_id', $order->getId());
            $this->_applyDefaultValues($order, $attributes); // $attributes might be modified in that function
            $attributes->save();
            Mage::register('amorderattr_saved', true, true);
            Mage::getSingleton('adminhtml/session')->setAmastyOrderAttributes(null);
            $checkout->setAmastyOrderAttributes(array());
            Mage::register('attributeClear', true, true);
        }
    }
    
    protected function _applyDefaultValues($order, $attributes)
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('order')->getTypeId());
                             
        $collection->getSelect()
            ->where('main_table.is_user_defined = ?', 1)
            ->where('main_table.apply_default = ?', 1);
            
        if ($collection->getSize() > 0) {
            foreach ($collection as $attributeToApply) {
                if (!$attributes->getData($attributeToApply->getAttributeCode())
                    && $attributeToApply->getDefaultValue()
                ) {
                   $attributes->setData($attributeToApply->getAttributeCode(), $attributeToApply->getDefaultValue());
                }
            }
        }
    }
    
    protected function _getAttributes()
    {
        if ($this->_attributes === null) {
            $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
            $attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
            $attributes->addFieldToFilter('show_on_grid', 1);
            $this->_attributes = $attributes;
        }
        return $this->_attributes;
    }

    /**
     * @param $collection
     * @param string $place
     * @param string $column
     * @return mixed
     */
    protected function _prepareCollection($collection, $place = 'order', $column = 'entity_id')
    {
        if ($this->_isJoined($collection->getSelect()->getPart('from')))
            return $collection;
            
        if (!$this->_isControllerName($place))
            return $collection;
        
        $attributes = $this->_getAttributes();
        if ($attributes->getSize()) {
            $fields = array();
            foreach ($attributes as $attribute) {
                $fields[] = $attribute->getAttributeCode();
            }
            
            $isVersionOneFour = ! Mage::helper('ambase')->isVersionLessThan(1, 4);
            
            $alias = $isVersionOneFour ? 'main_table' : 'e';
            $collection->getSelect()
                       ->joinLeft(
                            array(
                                'custom_attributes' =>
                                    Mage::getModel('amorderattr/attribute')->getResource()->getTable('amorderattr/order_attribute')),
                            "$alias . $column = custom_attributes.order_id",
                            $fields
                       );
        }
        return $collection;
    }
    
    protected function _isControllerName($place)
    {
        $found = false;
        foreach ($this->_controllerNames as $controllerName) {
            if (false !== strpos(Mage::app()->getRequest()->getControllerName(), $controllerName . $place)) {
                $found = true;
            }
        }
        return $found;
    }
    
    protected function _prepareColumns(&$grid, $export = false, $place = 'order', $after = 'grand_total')
    {
        if (!$this->_isControllerName($place) || 
            !in_array(Mage::app()->getRequest()->getActionName(), $this->_permissibleActions) )
            return $grid;
        
        $attributes = $this->_getAttributes();
        if ($attributes->getSize() > 0) {
            foreach ($attributes as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput())
                {
                    case 'date':
                            if ('time' == $attribute->getNote())
                            {
                                $column = array(
                                    'header'       => Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                    'type'         => 'datetime',
                                    'align'        => 'center',
                                    'index'        => $attribute->getAttributeCode(),
                                    'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                    'gmtoffset'    => false,
                                    'renderer'     => 'amorderattr/adminhtml_order_grid_renderer_datetime',
                                );
                            } else 
                            {
                                $column = array(
                                    'header'       => Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                    'type'         => 'date',
                                    'align'        => 'center',
                                    'index'        => $attribute->getAttributeCode(),
                                    'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                    'gmtoffset'    => false,
                                    'renderer'     => 'amorderattr/adminhtml_order_grid_renderer_datetime',
                                );
                            }
                            
                            break;
                        case 'text':
                        case 'textarea':
                            $column = array(
                                'header'       => Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                'filter'       => 'adminhtml/widget_grid_column_filter_text',
                                'sortable'     => true,
                                'renderer'     => 'amorderattr/adminhtml_order_grid_renderer_text' . ($export ? '_export' : ''),
                            );
                            break;
                        case 'boolean':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       =>  Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                'index'        =>  $attribute->getAttributeCode(),
                                'align'        => 'center',
                                'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                'type'         => 'options',
                                'options'      =>  $options,
                                'filter'       => 'adminhtml/widget_grid_column_filter_select',                                
                            );                         
                            break;                                                    
                        case 'select':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       =>  Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                'index'        =>  $attribute->getAttributeCode(),
                                'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      =>  $options,
                            );
                            break;
                        case 'multiselect':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       =>  Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                'index'        =>  $attribute->getAttributeCode(),
                                'align'        => 'center',
                                'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                'type'         => 'options',
                                'options'      =>  $options,
                            );
                            break;
                         case 'checkboxes':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       =>  Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                                'type'         => 'options',
                                'align'        => 'center',
                                'options'      =>  $options,
                                'index'        =>  $attribute->getAttributeCode(),
                                'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                                'filter'       => 'amorderattr/adminhtml_order_grid_filter_checkboxes',
                                'renderer'     => 'amorderattr/adminhtml_order_grid_renderer_checkboxes',
                            );
                            break;
                    case 'radios':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       =>  Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                            'type'         => 'options',
                            'align'        => 'center',
                            'options'      =>  $options,
                            'index'        =>  $attribute->getAttributeCode(),
                            'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode()
                        );
                        break;
                    case 'file':
                        $column = array(
                            'header'       => Mage::helper('amorderattr')->__($attribute->getFrontend()->getLabel()),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => 'custom_attributes.'.$attribute->getAttributeCode(),
                            'filter'       => 'adminhtml/widget_grid_column_filter_text',
                            'sortable'     => true,
                            'renderer'     => 'amorderattr/adminhtml_order_grid_renderer_file',
                        );
                        break;
                }
                $columnAfter = $attribute->getOrderGridAfter();
                $after = $columnAfter ? $columnAfter : $after;
                $grid->addColumnAfter($column['index'], $column, $after);
                $after = $column['index'];
            }
        }
        return $grid;
    }
    
    public function onSalesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $this->_prepareCollection($observer->getOrderGridCollection());
    }
    
    public function onSalesOrderInvoiceGridCollectionLoadBefore($observer)
    {
        if (!Mage::getStoreConfig('amorderattr/invoices_shipments/invoice_grid'))
            return;
        
        $collection = $this->_prepareCollection($observer->getOrderInvoiceGridCollection(), 'invoice', 'order_id');
    }
    
    public function onSalesOrderShipmentGridCollectionLoadBefore($observer)
    {
        if (!Mage::getStoreConfig('amorderattr/invoices_shipments/shipment_grid')) 
            return;
            
        $collection = $this->_prepareCollection($observer->getOrderShipmentGridCollection(), 'shipment', 'order_id');
    }
    
    protected function _isInstanceOf($block)
    {
        $found = false;
        foreach ($this->_otherClasses as $className) {
            if ($block instanceof $className) {
                $found = true;
                break;
            }
        }
        return $found;
    }
    
    public function onCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        // Order Grid
        if ($this->_isInstanceOf($block)) {
            $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions));
        }
        /*if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid || $block instanceof EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid) {
            $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions));
        }*/
        // Invoice Grid
        if ($block instanceof Mage_Adminhtml_Block_Sales_Invoice_Grid && Mage::getStoreConfig('amorderattr/invoices_shipments/invoice_grid')) {
            $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions), 'invoice');
        }
        // Shipment Grid
        if ($block instanceof Mage_Adminhtml_Block_Sales_Shipment_Grid && Mage::getStoreConfig('amorderattr/invoices_shipments/shipment_grid')) {
            $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions), 'shipment', 'total_qty');
        }
    }
    
    protected function _isJoined($from)
    {
        $found = false;
        foreach ($from as $alias => $data) {
            if ('custom_attributes' === $alias) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    protected function _prepareBackendHtml($html)
    {
        if (false === strpos($html, 'BEGIN `Amasty: Order Attributes`')) {
            $list = Mage::app()->getLayout()->createBlock('amorderattr/adminhtml_order_attribute_view_list');
            if (false === strpos($html, 'BEGIN `Amasty: Delivery Date`')) {
                $html = preg_replace('@<div class="entry-edit">(\s*)<div class="entry-edit-head(.*?)">(\s*)(.*?)head-products@',
                    $list->toHtml() .'<div class="entry-edit"><div class="entry-edit-head$2">$4head-products', $html, 1);
            } else {
                $pos = strpos($html, '<!-- BEGIN `Amasty: Delivery Date` -->');
                $html = substr_replace($html, $list->toHtml(), $pos-1, 0);
            }
        }
        return $html;
    }
    
    protected function _prepareFrontendHtml($transport, $fields, $where = '<div class="buttons-set"', $begin = true)
    {
        $html = $transport->getHtml();
        if (false === strpos($html, 'amorderattr')) {
            if ($begin) {
                $pos = strpos($html, $where);
                $pos--;
            } else {
                $pos = strrpos($html, $where);
                $pos += 7;
            }
            $insert = Mage::helper('amorderattr')->fields($fields);
            if ('review' == $fields
                && Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')) {
                $insert = str_replace('<ul class="form-list">', '', $insert);
                $insert = str_replace('<li class="fields">', '', $insert);
                $insert = str_replace('</li>', '', $insert);
                $insert = str_replace('</ul>', '', $insert);
                $insert = str_replace('<form id="form_review">', '', $insert);
                $insert = str_replace('</form>', '', $insert);
            }
            $html = substr_replace($html, $insert, $pos, 0);
            $transport->setHtml($html);
        }
        return $html;
    }
    
    public function handleBlockOutput($observer)
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Tab_Info) {
            $html = $this->_prepareBackendHtml($html);
        }
        
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View) {
            if (Mage::getStoreConfig('amorderattr/invoices_shipments/invoice_view')) {
                $html = $this->_prepareBackendHtml($html);
            }
        }
        
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View) {
            if (Mage::getStoreConfig('amorderattr/invoices_shipments/shipment_view')) {
                $html = $this->_prepareBackendHtml($html);
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_billing');
        //if ($block instanceof Mage_Checkout_Block_Onepage_Billing) {
        if ($blockClass == get_class($block)) {
            if (!Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')
            || !Mage::getStoreConfig('amscheckout/general/enabled')) {
                $html = $this->_prepareFrontendHtml($transport, 'billing');
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_shipping');
        //if ($block instanceof Mage_Checkout_Block_Onepage_Shipping) {
        if ($blockClass == get_class($block)) {
            if (!Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')
            || !Mage::getStoreConfig('amscheckout/general/enabled')) {
                $html = $this->_prepareFrontendHtml($transport, 'shipping');
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_shipping_method');
        //if ($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method) {
        if ($blockClass == get_class($block)) {
            if (Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')
            && Mage::getStoreConfig('amscheckout/general/enabled')) {
                $html = $this->_prepareFrontendHtml($transport, 'shipping_method', '</div>', false);
            } else {
                $html = $this->_prepareFrontendHtml($transport, 'shipping_method');
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_payment');
        //if ($block instanceof Mage_Checkout_Block_Onepage_Payment) {
        if ($blockClass == get_class($block)) {
            if (Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')
            && Mage::getStoreConfig('amscheckout/general/enabled')) {
                $html = $this->_prepareFrontendHtml($transport, 'payment', '</div>', false);
            } else {
                $html = $this->_prepareFrontendHtml($transport, 'payment', '</form>');
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_review_info');
        //if ($block instanceof Mage_Checkout_Block_Onepage_Review_Info) {
        if ($blockClass == get_class($block)) {
            $html = $this->_prepareFrontendHtml($transport, 'review', '</tfoot>');
        }

        if ($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available){
            $html .= '<script>if (typeof(amOrderattrConditionObj) != "undefined"){amOrderattrConditionObj.check();}</script>';
        }

        $blockClass = Mage::getConfig()->getBlockClassName('sales/order_print');
        if ($blockClass == get_class($block)) {
            $pos = strripos($html, '<h2');
            $insert = Mage::app()->getLayout()->createBlock('amorderattr/sales_order_print_attributes');
            $insert->setOrder($block->getOrder());
            $html = substr_replace($html, $insert->toHtml(), $pos-1, 0);
        }
        
        // Order View Page
        $blockClass = Mage::getConfig()->getBlockClassName('sales/order_info');
        if ($blockClass == get_class($block)) {
            $deliveryDate = false;
            if (false !== strpos($html, '<!-- BEGIN Order View `Amasty: Delivery Date` -->')) {
                $pos = strpos($html, '<!-- BEGIN Order View `Amasty: Delivery Date` -->');
                $pos = strpos($html, '<div class="col-1">', $pos);
                $deliveryDate = true;
            } else {
                $pos = strpos($html, '<div class="order-items');
            }
            $insert = Mage::app()->getLayout()->createBlock('amorderattr/sales_order_view_attributes');
            if ($insert) {
                if ($deliveryDate) {
                    $html = substr_replace($html, $insert->toHtml() . '<div class="col-2">', $pos, 19);
                } else {
                    $html = substr_replace($html, '<div class="col2-set order-info-box">' . $insert->toHtml() . '</div>', $pos - 1, 0);
                }
            }
        }
        
        $transport->setHtml($html);
    }

    public function removeTmpFiles(){
        try {
            $dir = Mage::getBaseDir('media') . DS . 'amorderattr' . DS . 'tmp';
            $this->_rrmdir($dir);
        }catch(Exception $ex) {
            Mage::logException($ex);
        }
    }

    protected function _rrmdir($dir)
    {
        $now = time();
        if (is_dir($dir)) {
            $children = scandir($dir);
            foreach ($children as $child) {
                if ($child != "." && $child != "..") {
                    if (is_dir($dir."/".$child)) {
                        $this->_rrmdir($dir . "/" . $child);
                    } else {
                        $fileName = $dir . "/" . $child;
                        $fileTime = filemtime($fileName);
                        if ( ($now - $fileTime)/24/60/60 > 1) {
                            unlink($fileName);
                        }
                    }
                }
            }
            //rmdir($dir);
        }
    }
}
