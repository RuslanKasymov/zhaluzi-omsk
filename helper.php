<?php

	defined('_JEXEC') or die;

	
	class VitalikjShopCategoriesHelper
	{
		public static $products = null;
		
		function getLastProducts($count, $array_categories = null, $filters = array(), $params)
		{
			$jshopConfig = JSFactory::getConfig();
			$db = JFactory::getDBO();
			$product = JTable::getInstance('product', 'jshop');
			$adv_query = "";
			$adv_from = "";
			$adv_result = $product->getBuildQueryListProductDefaultResult();
			$product->getBuildQueryListProductSimpleList("last", $array_categories, $filters, $adv_query, $adv_from, $adv_result);
			$order_query = "ORDER BY name";
			
	 
			$query = "SELECT $adv_result FROM `#__jshopping_products` AS prod
					  INNER JOIN `#__jshopping_products_to_categories` AS pr_cat ON pr_cat.product_id = prod.product_id
					  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id
					  $adv_from
					  WHERE prod.product_publish = '1' AND cat.category_publish='1' ".$adv_query."
					  GROUP BY prod.product_id ORDER BY ".$params->get('order_products', 'prod.product_id')." ".$params->get('dir_order_products', 'id')." LIMIT ".$count;
			$db->setQuery($query);
			$products = $db->loadObjectList();
			$products = listProductUpdateData($products);
			return $products;
		}
	
		public static function getProducts($category_id = 0, $params)
		{
			if (!self::$products)
			{
				$product = JTable::getInstance('product', 'jshop');
				$products_arr = VitalikjShopCategoriesHelper::getLastProducts(999, null, array(), $params);
				$products = array();
				foreach($products_arr as $key=>$value)
				{
					if (!is_array($products[$value->category_id]))
					{
						$products[$value->category_id] = array();
					}
					$value->product_link = SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='.$value->category_id.'&product_id='.$value->product_id, 1);
					$products[$value->category_id][] = $value;
				}
				self::$products = $products;
			}
			if ($category_id)
			{
				return self::$products[$category_id];
			}
			else
			{
				return self::$products;
			}
		}
		
		public static function getSubcategories($order, $ordering, $category_id, $params)
		{
			// Получаем объект таблицы категорий
			$category = JTable::getInstance('category', 'jshop');        
			// Загружаем текущую категорию
			$category->load($category_id);
			// Получаем дочерние категории
			$subcategories = $category->getChildCategories($order, $ordering);
			// Если есть подкатегории
			if (is_array($subcategories) && count($subcategories))
			{
				// Проходим по массиву подкатегорий
				foreach ($subcategories as $category)
				{
					//  
					$category->products = self::getProducts($category->category_id, $params);
					$category->subcategories = self::getSubcategories($order, $ordering, $category->category_id, $params);
				}
			}
			// 
			return $subcategories;
		}
		
		
		public static function getCatsArray($order, $ordering, $params)
		{
			// 
			$category = JTable::getInstance('category', 'jshop');
			// 
			$category->category_parent_id = 0;
			// 
			$categories = $category->getSisterCategories($order, $ordering);
			// 
			foreach($categories as $key=>$value)
			{ 
				$value->subcategories = self::getSubcategories($order, $ordering, $value->category_id, $params);
				// 
				$value->products = self::getProducts($value->category_id,$params);
			}
			// 
			return $categories;
		}
	}