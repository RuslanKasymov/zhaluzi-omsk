<?php
/**
 * @package Joomla.JoomShopping.Products
 * @version 1.6.0
 * @author Linfuby (Meling Vadim)
 * @website http://dell3r.ru/
 * @email support@dell3r.ru
 * @copyright Copyright by Linfuby. All rights reserved.
 * @license The MIT License (MIT); See \components\com_jshopping\addons\jshopping_plus_minus_count_product\license.txt
 */
defined("_JEXEC") or die;
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');

	$AddonAlias		= "plus_minus_count_product";
	$PluginDir		= array("products", "checkout");

	$DataBase = JFactory::getDBO();
	foreach($PluginDirs as $Plugin){
		$Query = $DataBase->getQuery(true);
		$Query->delete("#__extensions");
		$Query->where("element	= '".$AddonAlias."'");
		$Query->where("folder	= 'jshopping".$Plugin."'");
		$DataBase->setQuery($Query);
		$DataBase->query();
		JFolder::Delete(JPATH_ROOT."/plugins/jshopping".$Plugin."/".$AddonAlias);
	}
	JFolder::Delete(JPATH_COMPONENT_SITE."/addons/jshopping_".$AddonAlias);