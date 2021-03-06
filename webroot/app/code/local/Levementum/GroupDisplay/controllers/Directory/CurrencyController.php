<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency controller
 *
 * @category   Mage
 * @package    Mage_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 
require_once Mage::getModuleDir('controllers', 'Mage_Directory').DS.'CurrencyController.php';
 
class Ophirah_Qquoteadv_Directory_CurrencyController  extends Mage_Directory_CurrencyController
{
    public function switchAction()
    {
    	
		if ($curency = (string) $this->getRequest()->getParam('currency')) {
			if ( Mage::helper('qquoteadv')->isActiveConfirmMode()) {
			 	$message =  Mage::helper('qquoteadv')->__('Action is blocked in quote confirmation mode');
	            Mage::getSingleton('checkout/session')->addError($message);
	
				$link = Mage::getUrl('qquoteadv/view/outqqconfirmmode');
				$message = Mage::helper('qquoteadv')->__("To change your currency <a href='%s'>log out</a> from Quote confirmation mode.",$link);
	            Mage::getSingleton('checkout/session')->addNotice($message);
		 	}else{
				Mage::app()->getStore()->setCurrentCurrencyCode($curency);
			}	
        }
        $this->_redirectReferer(Mage::getBaseUrl());
    }
}
