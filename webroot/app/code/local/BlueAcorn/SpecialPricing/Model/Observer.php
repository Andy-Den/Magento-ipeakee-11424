<?php

/**
 * @package     BlueAcorn\SpecialPricing
 * @version
 * @author      Blue Acorn, Inc. <code@blueacorn.com>
 * @copyright   Copyright © 2015 Blue Acorn, Inc.
 */
class BlueAcorn_SpecialPricing_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Change the price to the MSRP price rather than lower MAP price
     * when the add to cart button is used
     *
     * @param Varien_Event_Observer $observer Observer object
     */
    public function defaultMsrpPrice(Varien_Event_Observer $observer)
    {
        // Get Quote item to switch
        /** @var Varien_Event $event */
        $event = $observer->getEvent();
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $event->getQuoteItem();

        // Alter price if msrp exists
        $productMsrp = $quoteItem->getProduct()->getMsrp();
        if ($productMsrp) {
            $quoteItem->setOriginalCustomPrice($productMsrp);
        }
    }
}