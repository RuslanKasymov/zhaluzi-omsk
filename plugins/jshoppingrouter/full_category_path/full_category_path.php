<?php
/**
* @package Joomla
* @subpackage JoomShopping
* @author Nevigen.com
* @website http://nevigen.com/
* @email support@nevigen.com
* @copyright Copyright © Nevigen.com. All rights reserved.
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @license agreement http://nevigen.com/license-agreement.html
**/

defined( '_JEXEC' ) or die;

class plgJshoppingRouterFull_Category_Path extends JPlugin {

	protected static function getCategoryList() {
		static $categoryList;
		
		if ( !is_array($categoryList) ) {
			$allCategories = JTable::getInstance('Category', 'jshop')->getAllCategories();
			$categoryList = array();
			foreach ($allCategories as $row) {
				$categoryList[$row->category_id] = $row->category_parent_id;
			}
			unset($allCategories);
		}
		
		return $categoryList;
	}

	protected static function buildSegment($category_id) {
		static $segments = array();

		if (!isset($segments[$category_id])) {
			$categoryList = self::getCategoryList();
			$aliasCategory = JSFactory::getAliasCategory();
			$segments[$category_id] = array();

			$full_path = array();
			$categoryID = $category_id;
			while ($categoryList[$categoryID]) {
				$categoryID = $categoryList[$categoryID];
				$full_path[] = $categoryID;
			}
			$full_path = array_reverse($full_path);
			foreach ($full_path as $categoryID) {
				$segments[$category_id][] = $aliasCategory[$categoryID];
			}
		}
		
		return $segments[$category_id];
	}

	function onBeforeBuildRoute(&$query, &$segments) {
		static $categoryList;

		if ( !isset($query['controller'])
			|| ($query['controller']!='category' && $query['controller']!='product')
			|| $query['task']!='view'
			|| !$query['category_id']
			|| ($query['controller']=='product' && !$query['product_id']) ) return;

		$categoryItemidList = shopItemMenu::getInstance()->getListCategory();
		if ( $categoryItemidList[$query['category_id']] ) return;
		
		$segments = self::buildSegment($query['category_id']);
	}

	function onBeforeParseRoute(&$vars, &$segments) {
		$aliasCategory = JSFactory::getAliasCategory();
		$aliasProduct = JSFactory::getAliasProduct();
		$aliases = str_replace(":", "-", $segments);
		$productID = array_search(end($aliases), $aliasProduct);
		$countSegments = count($segments);
		if ($countSegments > 1 && $productID) {
			$productAlias = array_pop($aliases);
			$tProduct = JTable::getInstance('Product', 'jshop');
			$tProduct->load($productID);
			$listCategory = $tProduct->getCategories(1);
			$categoryAlias = array_pop($aliases);
			$categoryID = array_search($categoryAlias, $aliasCategory);
			if ($categoryID && in_array($categoryID, $listCategory)) {
				$buildSegments = self::buildSegment($categoryID);
				if ($aliases != $buildSegments) {
					JFactory::getApplication()->redirect(SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='.$categoryID.'&product_id='.$productID, 1));
				}
			} else {
				JFactory::getApplication()->redirect(SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='.$tProduct->getCategory().'&product_id='.$productID, 1));
			}
			$productSegment = array_pop($segments);
			$segments = array(end($segments), $productSegment);
		} else if ($countSegments > 0) {
			$categoryAlias = array_pop($aliases);
			$categoryID = array_search($categoryAlias, $aliasCategory);
			if ($categoryID) {
				$buildSegments = self::buildSegment($categoryID);
				if ($aliases != $buildSegments) {
					JFactory::getApplication()->redirect(SEFLink('index.php?option=com_jshopping&controller=category&task=view&category_id='.$categoryID, 1));
				}
				$segments = array(end($segments));
			}
		}
	}

}