<?php

/**
 * Class TNW_Salesforce_Adminhtml_Salesforcesync_ProductsyncController
 */
class TNW_Salesforce_Adminhtml_Salesforcesync_ProductsyncController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('grid', 'index');

    /**
     * @return $this
     */
    protected function _initLayout()
    {
        if (!Mage::helper('tnw_salesforce')->isEnabled() || !Mage::helper('tnw_salesforce/salesforce_data')->isLoggedIn()) {
            Mage::getSingleton('adminhtml/session')->addNotice("Salesforce integration is not working! Refer to the config or the log files for more information.");
        }
        $this->loadLayout()
            ->_setActiveMenu('tnw_salesforce')
            ->_addBreadcrumb(Mage::helper('tnw_salesforce')->__('Manual Product Synchronization'), Mage::helper('tnw_salesforce')->__('Manual Product Synchronization'));

        return $this;
    }

    /**
     * Index Action
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Salesforce API'))->_title($this->__('Manual Sync'))->_title($this->__('Products'));

        $this->_initLayout()
            ->_addContent($this->getLayout()->createBlock('adminhtml/store_switcher'));
        $this->_addContent($this->getLayout()->createBlock('tnw_salesforce/adminhtml_productsync'));

        Mage::helper('tnw_salesforce')->addAdminhtmlVersion('TNW_Salesforce');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Sync Action
     *
     */
    public function syncAction()
    {
        $session = $this->_getSession();
        if (!Mage::helper('tnw_salesforce')->isEnabled()) {
            $session->addError("API Integration is disabled.");
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'salesforce'));
            return;
        }

        $productId = $this->getRequest()->getParam('product_id');
        if (!$productId) {
            $session->addError($this->__('Incorrect product id'));
        } else {
            try {
                if (Mage::helper('tnw_salesforce')->getObjectSyncType() != 'sync_type_realtime') {
                    // pass data to local storage
                    $res = Mage::getModel('tnw_salesforce/localstorage')
                        ->addObjectProduct(array($productId), 'Product', 'product');
                    if ($res) {
                        $session->addSuccess($this->__('Record was added to synchronization queue!'));
                    } else {
                        $session->addError($this->__('Could not add products to the queue!'));
                    }
                } else {
                    $sync = Mage::helper('tnw_salesforce/salesforce_product');
                    if ($sync->reset()) {

                        $sync->setSalesforceServerDomain(
                            Mage::getSingleton('core/session')->getSalesforceServerDomain());
                        $sync->setSalesforceSessionId(
                            Mage::helper('tnw_salesforce/test_authentication')->getStorage('salesforce_session_id'));

                        $sync->massAdd(array($this->getRequest()->getParam('product_id')));
                        $sync->process();
                        if (!$session->getMessages()->getErrors()
                            && Mage::helper('tnw_salesforce/salesforce_data')->isLoggedIn()
                        ) {
                            $session->addSuccess($this->__('Product was successfully synchronized'));
                        }
                    } else {
                        $session->addError($this->__('Salesforce connection could not be established!'));
                    }
                }
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                Mage::logException($e);
            }
        }

        $this->_redirectReferer($this->getUrl('*/*/index', array('_current' => true)));
    }

    public function massSyncAction()
    {
        if (!Mage::helper('tnw_salesforce')->isEnabled()) {
            Mage::getSingleton('adminhtml/session')->addError("API Integration is disabled.");
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/system_config/edit", array('section' => 'salesforce')));
            Mage::app()->getResponse()->sendResponse();
        }
        $itemIds = $this->getRequest()->getParam('products');
        if (!is_array($itemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tnw_salesforce')->__('Please select products(s)'));
        } elseif (Mage::helper('tnw_salesforce')->getType() != "PRO") {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tnw_salesforce')->__('Mass syncronization is not allowed using Basic version. Please visit <a href="http://powersync.biz" target="_blank">http://powersync.biz</a> to request an upgrade.'));
        } elseif(((Mage::helper('tnw_salesforce')->getObjectSyncType() == 'sync_type_realtime')) && (count($itemIds) > 50)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tnw_salesforce')->__('For history synchronization containing more than 50 records change configuration to use interval based synchronization.'));
        } else {
            try {
                if (Mage::helper('tnw_salesforce')->getObjectSyncType() != 'sync_type_realtime') {
                    $_chunks = array_chunk($itemIds, TNW_Salesforce_Helper_Queue::UPDATE_LIMIT);
                    unset($itemIds);
                    foreach($_chunks as $_chunk) {
                        Mage::helper('tnw_salesforce/queue')->prepareRecordsToBeAddedToQueue($_chunk, 'Product', 'product');
                    }

                    if (!Mage::getSingleton('adminhtml/session')->getMessages()->getErrors()) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            $this->__('Records are pending addition into the queue!')
                        );
                    }
                } else {
                    $manualSync = Mage::helper('tnw_salesforce/bulk_product');
                    if ($manualSync->reset()) {
                        $manualSync->setSalesforceServerDomain(Mage::getSingleton('core/session')->getSalesforceServerDomain());
                        $manualSync->setSalesforceSessionId(Mage::helper('tnw_salesforce/test_authentication')->getStorage('salesforce_session_id'));

                        $manualSync->massAdd($itemIds);
                        $manualSync->process();
                        if (!Mage::getSingleton('adminhtml/session')->getMessages()->getErrors()
                            && Mage::helper('tnw_salesforce/salesforce_data')->isLoggedIn()) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(
                                Mage::helper('adminhtml')->__(
                                    'Total of %d record(s) were successfully synchronized', count($itemIds)
                                )
                            );
                        }
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError('Salesforce Connection failed!');
                    }
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $url = '*/*/index';
        if (Mage::helper('tnw_salesforce')->getStoreId() != 0) {
            $url .= '/store/' . Mage::helper('tnw_salesforce')->getStoreId();
        }
        $this->_redirect($url);
    }
}