<?php

/**
 * Product:       Xtento_StockImport (2.3.6)
 * ID:            Local Deploy
 * Packaged:      2016-10-18T22:31:59+02:00
 * Last Modified: 2013-07-10T23:52:25+02:00
 * File:          app/code/local/Xtento/StockImport/Model/System/Config/Source/Stock/Identifier.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_StockImport_Model_System_Config_Source_Stock_Identifier
{

    public function toOptionArray()
    {
        $identifiers[] = array('value' => 'sku', 'label' => Mage::helper('xtento_stockimport')->__('SKU'));
        $identifiers[] = array('value' => 'entity_id', 'label' => Mage::helper('xtento_stockimport')->__('Product ID (entity_id)'));
        $identifiers[] = array('value' => 'attribute', 'label' => Mage::helper('xtento_stockimport')->__('Custom Product Attribute'));
        return $identifiers;
    }

}
