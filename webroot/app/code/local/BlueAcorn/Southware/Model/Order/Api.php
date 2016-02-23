<?php
/**
 * @package BlueAcorn_SouthwareApi
 * @version 1.0.0
 * @author BlueAcorn
 * @copyright Copyright (c) 2015 Blue Acorn, Inc.
 */

class BlueAcorn_Southware_Model_Order_Api extends Mage_Sales_Model_Order_Api
{
    const TABLE_NAME = 'amasty_amorderattr_order_attribute';

    /**
     * Sets Southware Order ID on Order provided by Southware
     *
     * @param $orderNumber
     * @param $southwareOrderId
     */
    public function setSouthwareOrderId($orderNumber, $southwareOrderId)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);
        $orderId = $order->getId();

        $query = "UPDATE " . self::TABLE_NAME . " SET southware_order_id = '{$southwareOrderId}' WHERE order_id = " . (int)$orderId;

        $writeConnection->query($query);
    }
}