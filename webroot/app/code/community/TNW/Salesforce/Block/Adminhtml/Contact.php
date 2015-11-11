<?php

/**
 * Class TNW_Salesforce_Block_Adminhtml_Contact
 */
class TNW_Salesforce_Block_Adminhtml_Contact extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tnw_salesforce';
        $this->_controller = 'adminhtml_contact';
        $this->_headerText = Mage::helper('tnw_salesforce')->__('Contact Mapping');
        parent::__construct();
        $this->_updateButton('add', 'label', Mage::helper('tnw_salesforce')->__('Add New Mapping'));
    }
}