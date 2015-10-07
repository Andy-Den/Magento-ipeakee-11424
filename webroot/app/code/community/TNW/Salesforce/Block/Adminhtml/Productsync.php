<?php

/**
 * Class TNW_Salesforce_Block_Adminhtml_Productsync
 */
class TNW_Salesforce_Block_Adminhtml_Productsync extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tnw_salesforce';
        $this->_controller = 'adminhtml_productsync';
        $this->_headerText = Mage::helper('tnw_salesforce')->__('Product Synchronization');
        parent::__construct();
        $this->removeButton('add');
    }
}