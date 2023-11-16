<?php
defined('_JEXEC') or die('Restricted access');

$db = \JFactory::getDbo();

$db->setQuery("DELETE FROM `#__extensions` WHERE element = 'monoparts' AND folder = 'jshoppingorder' AND `type` = 'plugin'");
$db->execute();

$db->setQuery("DELETE FROM `#__extensions` WHERE element = 'monoparts' AND folder = 'jshoppingadmin' AND `type` = 'plugin'");
$db->execute();

$db->setQuery("DELETE FROM `#__extensions` WHERE element = 'monoparts' AND folder = 'jshoppingcheckout' AND `type` = 'plugin'");
$db->execute();

$db->setQuery("DELETE FROM `#__jshopping_payment_method` WHERE payment_code = 'monoparts'");
$db->execute();

$db->setQuery("ALTER TABLE `#__jshopping_products` DROP COLUMN `pm_monoparts_max_parts`");
$db->execute();

jimport('joomla.filesystem.folder');
foreach (array(
             'components/com_jshopping/addons/monoparts/',
             'components/com_jshopping/lang/monoparts/',
             'components/com_jshopping/payments/pm_monoparts/',
             'plugins/jshoppingadmin/monoparts/',
             'plugins/jshoppingorder/monoparts/',
             'plugins/jshoppingcheckout/monoparts/',
         ) as $folder) {
    JFolder::delete(JPATH_ROOT . '/' . $folder);
}