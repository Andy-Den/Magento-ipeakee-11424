<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->dropForeignKey($installer->getTable('design_change'), 'FK_DESIGN_CHANGE_STORE');

$storeIds = $installer->getConnection()->fetchCol(
    "SELECT store_id FROM {$installer->getTable('core_store')}"
);

if (!empty($storeIds)) {
    $storeIds = implode(',', $storeIds);
    $installer->run("DELETE FROM {$installer->getTable('design_change')} WHERE store_id NOT IN ($storeIds)");
}

$installer->getConnection()->addConstraint(
    'FK_DESIGN_CHANGE_STORE',
    $installer->getTable('design_change'), 'store_id',
    $installer->getTable('core_store'),    'store_id'
);

$installer->endSetup();
