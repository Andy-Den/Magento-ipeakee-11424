<?php

/**
 * Product:       Xtento_StockImport (2.3.6)
 * ID:            Local Deploy
 * Packaged:      2016-10-18T22:31:59+02:00
 * Last Modified: 2015-10-29T09:38:48+01:00
 * File:          app/code/local/Xtento/StockImport/Model/Processor/Mapping/Abstract.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

abstract class Xtento_StockImport_Model_Processor_Mapping_Abstract extends Varien_Object
{
    protected $_mapping = null;
    protected $_importFields = null;

    public function getMapping()
    {
        if ($this->_mapping !== NULL) {
            return $this->_mapping;
        }

        $data = $this->getMappingData();

        $mapping = array();
        foreach ($data as $id => $field) {
            if (!isset($field['field'])) {
                continue;
            }
            if (!isset($field['value'])) {
                $value = '';
            } else {
                $value = $field['value'];
            }
            if (!isset($field['default_value'])) {
                $default_value = false;
            } else {
                $default_value = $field['default_value'];
            }
            $mapping[$field['field']] = array('id' => $id, 'field' => $field['field'], 'value' => $value, 'default_value' => $default_value);
        }
        $this->_mapping = $mapping;

        return $this->_mapping;
    }

    public function getMappingField($field)
    {
        $mapping = $this->getMapping();
        if (isset($mapping[$field])) {
            return $mapping[$field]['value'];
        }
        return '';
    }

    public function getMappingConfig() {
        $mappingConfig = new Varien_Object();
        foreach ($this->getMapping() as $field => $data) {
            if (!isset($data['value']) || $data['value'] == '') {
                continue;
            }
            $mappingConfig->setData($field, $data['value']);
        }
        return $mappingConfig;
    }

    public function getDefaultValues($entity)
    {
        $defaultValues = array();
        if ($entity == 'stock_status') {
            $defaultValues[1] = Mage::helper('xtento_stockimport')->__('In Stock');
            $defaultValues[0] = Mage::helper('xtento_stockimport')->__('Out Of Stock');
        }
        if ($entity == 'backorders') {
            $defaultValues[0] = Mage::helper('xtento_stockimport')->__('No Backorders');
            $defaultValues[1] = Mage::helper('xtento_stockimport')->__('Allow Qty Below 0');
            $defaultValues[2] = Mage::helper('xtento_stockimport')->__('Allow Qty Below 0 and Notify Customer');
        }
        if ($entity == 'yesno') {
            $defaultValues[0] = Mage::helper('xtento_stockimport')->__('No');
            $defaultValues[1] = Mage::helper('xtento_stockimport')->__('Yes');
        }
        return $defaultValues;
    }

    public function getDefaultValue($fieldName)
    {
        $mapping = $this->getMapping();
        if (isset($mapping[$fieldName])) {
            return $mapping[$fieldName]['default_value'];
        }
        return '';
    }
}
