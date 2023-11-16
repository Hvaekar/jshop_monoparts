<?php
defined('_JEXEC') or die('Restricted access');

$name = 'JoomShopping MonoParts';
$type = 'plugin';
$element = 'monoparts';
$pmclass = 'pm_monoparts';
$folders = array('jshoppingorder', 'jshoppingadmin', 'jshoppingcheckout');
$version = '2.0.0';
$cache = '{"creationDate":"6.11.2023","author":"Hvaekar","authorEmail":"hvaekar@gmail.com","authorUrl":"https://github.com/Hvaekar","version":"' . $version . '"}';
$params = '{"test":"1","store_id":"test_store_with_confirm","sign_key":"secret_98765432--123-123","max_parts":"25","transaction_end_status":"0","transaction_pending_status":"0","transaction_failed_status":"0","return_money":"0","transaction_cancel_status":"0","transaction_confirm_status":"0","sum_type":"0"}';

$db = \JFactory::getDbo();
foreach ($folders as $folder) {
    $db->setQuery("SELECT `extension_id` FROM `#__extensions` WHERE `element`='" . $element . "' AND `folder`='" . $folder . "'");
    $id = $db->loadResult();
    if (!$id) {
        $query = "INSERT INTO `#__extensions`(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`) VALUES
			('" . $name . "', '" . $type . "', '" . $element . "', '" . $folder . "', 0, 1, 1, 0,'" . addslashes($cache) . "','" . addslashes($params) . "')";
    } else {
        $query = "UPDATE `#__extensions` SET `name`='" . $name . "', `manifest_cache`='" . addslashes($cache) . "', `params`='" . addslashes($params) . "' WHERE `extension_id`=" . $id;
    }
    $db->setQuery($query);
    $db->execute();
}

$db->setQuery("SELECT `payment_id` FROM `#__jshopping_payment_method` WHERE `payment_class`='" . $pmclass . "'");
$pm_id = $db->loadResult();
if (!$pm_id) {
    $model_langs = \JSFactory::getModel('Languages', 'JshoppingModel');
    $languages = $model_langs->getAllLanguages(1);

    foreach ($languages as $lang) {
        $names_into[] = '`name_' . $lang->language . '`';
        $names[] = "'" . $element . "'";
    }

    $db->setQuery("INSERT INTO `#__jshopping_payment_method`(" . implode(", ", $names_into) . ", `payment_code`, `payment_class`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `price`, `price_type`, `tax_id`, `show_descr_in_email`) VALUES
				(" . implode(", ", $names) . ", '" . $element . "', '" . $pmclass . "', 0, 1,'" . $params . "', 2, 0.00, 1, -1, 0)");
    $db->execute();
}

$db->setQuery("SELECT * FROM `#__jshopping_products` LIMIT 1");
$prod_arr = $db->loadAssocList();
if (!array_key_exists('pm_monoparts_max_parts', $prod_arr[0])) {
    $db->setQuery("ALTER TABLE `#__jshopping_products` ADD COLUMN `pm_monoparts_max_parts` SMALLINT DEFAULT -1");
    $db->execute();
}

$addon = \JSFactory::getTable('addon', 'jshop');
$addon->loadAlias($element);
$addon->set('name', $name);
$addon->set('version', $version);
$addon->set('uninstall', '/components/com_jshopping/addons/' . $element . '/uninstall.php');
$addon->store();