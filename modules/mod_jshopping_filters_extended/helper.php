<?php
defined('_JEXEC') or die;
class modJshopping_filters_extendedHelper {

	public static function getMainId(){
    static $main;
        if (!isset($main)){
			$db = JFactory::getDbo();
			$query = "SELECT id FROM #__jshopping_vendors WHERE `main`=1";
			$db->setQuery($query);
			$main = intval($db->loadResult());
        }
	return $main;
    }
	
	public static function getExtraFieldsIdForType($type = 1) {
		$db = JFactory::getDbo();
		$query = 'SELECT id FROM `#__jshopping_products_extra_fields` WHERE type='.$type;  
		$db->setQuery($query);
		return $db->loadColumn();
	}
	
	public static function _getWhere($category_id, $manufacturer_id, $vendor_id, &$join) {
		$where = '';
		if ($category_id != '0') {
			$join = " LEFT JOIN `#__jshopping_products_to_categories` as pc ON (pc.product_id=p.product_id) ";
			$where .= " AND pc.category_id = '".$category_id."' "; 
		}
		if ($manufacturer_id != '0') {
			$where .= " AND p.product_manufacturer_id = '".$manufacturer_id."' ";
		}
		if ($vendor_id != '0'){
			if ($vendor_id == self::getMainId())
				$where .= " AND p.vendor_id = '0' "; 
			else 
				$where .= " AND p.vendor_id = '".$vendor_id."' ";
		}
		return $where;
	}
	
	public static function getListProductExtraField($category_id, $manufacturer_id, $vendor_id, $show_characteristics_id, $_list_extrafields) {
		$db = JFactory::getDbo();
		$tmp = array();
		foreach($_list_extrafields as $key => $_extrafield_id){
			if (in_array($_extrafield_id, $show_characteristics_id) || in_array(0, $show_characteristics_id)){
				$tmp[] = "extra_field_".$_extrafield_id;
			}
		}
        if (count($tmp)==0){
            return array();
        }
        
		$query_field_product = implode(", ", $tmp);
		
		$join = "";
		$where = self::_getWhere($category_id, $manufacturer_id, $vendor_id, $join);
		
		$query = "SELECT distinct ".$query_field_product." FROM `#__jshopping_products` as p ".$join." WHERE p.product_publish='1' ".$where;  
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	static function getInProductsCharacteristic($category_id, $manufacturer_id, $vendor_id, $show_characteristics_id) {
		$_list_extrafields = self::getExtraFieldsIdForType(0);
		if (count($_list_extrafields) == 0) return array();
		$list_product_extra_fields = self::getListProductExtraField($category_id, $manufacturer_id, $vendor_id, $show_characteristics_id, $_list_extrafields);
		$_list_active_values = array();
		foreach($list_product_extra_fields as $fieldsval){
			$test = array();
			foreach($fieldsval as $k=>$v){
				if ($v != '') {
					$test[] = $v;
				}
			}
			if (count($test)) {
				$_list_active_values = array_merge($_list_active_values, array(implode(',', $test)));
			}
		}
		$_list_active_values = array_unique($_list_active_values);
		
		$list_active_values = array();
		foreach($_list_active_values as $k=>$v){
			$list_active_values = array_merge($list_active_values, explode(",",$v));
		}
		$list_active_values = array_unique($list_active_values);
		return $list_active_values;
	}

	static function getInProductsCharacteristicText($category_id,$manufacturer_id,$vendor_id, $show_characteristics_id){
		$_list_extrafields = self::getExtraFieldsIdForType(1);
		if (count($_list_extrafields) == 0) return array();
		$list_product_extra_fields = self::getListProductExtraField($category_id, $manufacturer_id, $vendor_id, $show_characteristics_id, $_list_extrafields);
		$_list_active_values = array();
		foreach($list_product_extra_fields as $fieldsval){
			$test = array();
			foreach($_list_extrafields as $v){
				if ($fieldsval->{'extra_field_'.$v} != '') {
					$test[] = $v;
				}
			}
			if (count($test)) {
				$_list_active_values = array_merge($_list_active_values, array(implode(',', $test)));
			}
		}
		$_list_active_values = array_unique($_list_active_values);
		
		$list_active_values = array();
		foreach($_list_active_values as $k=>$v){
			$list_active_values = array_merge($list_active_values, explode(",",$v));
		}        
		$list_active_values = array_unique($list_active_values);
		
		return $list_active_values;
	}

	static function getInProductsAttribut($category_id, $manufacturer_id, $vendor_id){
		$db = JFactory::getDbo();
		$jshopConfig = JSFactory::getConfig();
		
		$join = "";
		$where = self::_getWhere($category_id, $manufacturer_id, $vendor_id, $join);

		// independent
		$query = "SELECT DISTINCT a.attr_value_id FROM `#__jshopping_products_attr2` AS a
				LEFT JOIN `#__jshopping_products` AS p ON (a.product_id=p.product_id) ".$join."
				WHERE p.product_publish='1' ".$where." GROUP BY a.attr_value_id";        
		$db->setQuery($query);
		$arr_independent = $db->loadColumn();        

		//depended
		$query = "SELECT `attr_id` FROM `#__jshopping_attr` WHERE `independent`='0'";  
		$db->setQuery($query); 
		$alldependattr = $db->loadColumn();

		$arr_dependent = array();
		if ($jshopConfig->hide_product_not_avaible_stock){
			$where .= " and a.count>0 ";
		}
		if ($alldependattr) 
			foreach($alldependattr as $attr){
				$query = "SELECT distinct a.attr_".$attr." FROM `#__jshopping_products_attr` a 
						LEFT JOIN `#__jshopping_products` AS p ON (a.product_id=p.product_id)
						".$join." 
						WHERE p.product_publish='1' ".$where;            
				$db->setQuery($query);
				$tmplist = $db->loadColumn();            
				$arr_dependent = array_merge($arr_dependent, $tmplist);
			}
		$arr_dependent = array_unique($arr_dependent);
		
		return array_merge($arr_independent, $arr_dependent);
	}

	static function getInProductsLabels($category_id, $manufacturer_id, $vendor_id) {
		$db = JFactory::getDbo();
		
		$join = "";
		$where = self::_getWhere($category_id, $manufacturer_id, $vendor_id, $join);

		$query = " SELECT distinct label_id FROM `#__jshopping_products` as p ".$join." WHERE p.product_publish='1' AND label_id!='0' ".$where;
		$db->setQuery($query);
		$res = $db->loadObjectList();
		$arr = array();
		if ($res) {
			foreach ($res as $_res){
				$arr = array_merge($arr, array($_res->label_id));
			}
			$arr = array_unique($arr);
		}
		return $arr;
	}

	static function getInProductsDelivery_time($category_id, $manufacturer_id, $vendor_id) {
		$db = JFactory::getDbo();

		$join = "";
		$where = self::_getWhere($category_id, $manufacturer_id, $vendor_id, $join);
		
		$query = "SELECT distinct delivery_times_id FROM `#__jshopping_products` as p ".$join." WHERE p.product_publish='1' AND delivery_times_id!='0' ".$where;
		$db->setQuery($query);
		$res = $db->loadObjectList(); 
		$arr = array();
		if ($res) {
			foreach ($res as $_res){
				$arr = array_merge($arr,array($_res->delivery_times_id));
			}
			$arr = array_unique($arr);
		}
		return  $arr;
	}

	static function getInProductsMaxMinPrice($category_id, $manufacturer_id, $vendor_id) {
		$db = JFactory::getDbo();

		$join = "";
		$where = self::_getWhere($category_id, $manufacturer_id, $vendor_id, $join);
		
		$query = "SELECT p.`product_id` FROM `#__jshopping_products` as p ".$join." WHERE p.product_publish='1' ".$where;
		$db->setQuery($query);
		$res = $db->loadObjectList(); 

		$arr = array();
		$minprice = 0;
		$maxprice = 0;
		if ($res) {
			foreach ($res as $_res){
				$prod = JTable::getInstance('product', 'jshop');
				$prod->load($_res->product_id); 
				$active_price = $prod->getPrice();          
				if ($active_price > $maxprice)
					$maxprice = $active_price;
				if ($active_price < $minprice || !$minprice)
					$minprice = $active_price; 
				unset($prod);
			}
		}
		$arr['min_price'] = (int)$minprice;
		$arr['max_price'] = (int)$maxprice+1;
		$arr['count'] = count($res);
		return  $arr;
	}    

	static function getVendorsForCategory($category_id){
		$db = JFactory::getDbo();
		$jshopConfig = JSFactory::getConfig();
		$user = JFactory::getUser();
		$adv_query = "";
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$adv_query .= ' AND prod.access IN ('.$groups.')';
		if ($jshopConfig->hide_product_not_avaible_stock){
			$adv_query .= " AND prod.product_quantity > 0";
		}
		$query = "SELECT distinct man.id as id, man.`shop_name` FROM `#__jshopping_products` AS prod
				LEFT JOIN `#__jshopping_products_to_categories` AS categ USING (product_id)
				LEFT JOIN `#__jshopping_vendors` as man on (prod.vendor_id=man.id OR (prod.vendor_id=0 AND man.main=1) )
				WHERE categ.category_id = '".$category_id."' AND prod.product_publish = '1' ".$adv_query." order by shop_name";
		$db->setQuery($query);
		$list = $db->loadObjectList();         
		return $list;
	}

	static function getVendorsForManufacturer($manufacturer_id){
		$db = JFactory::getDbo();
		$jshopConfig = JSFactory::getConfig();
		$user = JFactory::getUser();
		$adv_query = "";
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$adv_query .= ' AND prod.access IN ('.$groups.')';
		if ($jshopConfig->hide_product_not_avaible_stock){
			$adv_query .= " AND prod.product_quantity > 0";
		}
		$query = "SELECT distinct man.id as id, man.`shop_name` FROM `#__jshopping_products` AS prod
				LEFT JOIN `#__jshopping_vendors` as man on (prod.vendor_id=man.id OR (prod.vendor_id=0 AND man.main=1) )
				WHERE prod.product_manufacturer_id = '".$manufacturer_id."' AND prod.product_publish = '1' ".$adv_query." order by shop_name";
		$db->setQuery($query);
		$list = $db->loadObjectList();         
		return $list;     
	}  

	static function getManufacturersForVendors($vendor_id){
		$db = JFactory::getDbo();
		$jshopConfig = JSFactory::getConfig();
		$user = JFactory::getUser();
		$lang = JSFactory::getLang();
		$adv_query = "";
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$adv_query .= ' AND prod.access IN ('.$groups.')';
		if ($jshopConfig->hide_product_not_avaible_stock){
			$adv_query .= " AND prod.product_quantity > 0";
		}
		$query = "SELECT distinct man.manufacturer_id as id, man.`".$lang->get('name')."` as name FROM `#__jshopping_products` AS prod
				LEFT JOIN `#__jshopping_manufacturers` as man on prod.product_manufacturer_id=man.manufacturer_id 
				LEFT JOIN `#__jshopping_vendors` as v on (prod.vendor_id=v.id OR (prod.vendor_id=0 AND v.main=1) ) 
				WHERE v.id = '".$vendor_id."' AND prod.product_publish = '1' AND prod.product_manufacturer_id!=0 ".$adv_query." order by name";
		$db->setQuery($query);
		$list = $db->loadObjectList();       
		return $list;
	} 

	static function getCategorysForVendors($vendor_id){
		$db = JFactory::getDbo();
		$jshopConfig = JSFactory::getConfig();
		$user = JFactory::getUser();
		$lang = JSFactory::getLang();
		$adv_query = "";
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$adv_query .= ' AND prod.access IN ('.$groups.') AND cat.access IN ('.$groups.')';
		if ($jshopConfig->hide_product_not_avaible_stock){
			$adv_query .= " AND prod.product_quantity > 0";
		}
		$query = "SELECT distinct cat.category_id as id, cat.`".$lang->get('name')."` as name FROM `#__jshopping_products` AS prod
				LEFT JOIN `#__jshopping_products_to_categories` AS categ USING (product_id)
				LEFT JOIN `#__jshopping_categories` as cat on cat.category_id=categ.category_id
				LEFT JOIN `#__jshopping_vendors` as v on (prod.vendor_id=v.id OR (prod.vendor_id=0 AND v.main=1) ) 
				WHERE prod.product_publish = '1' AND v.id = '".$vendor_id."' AND cat.category_publish='1' ".$adv_query." order by name";
		$db->setQuery($query);
		$list = $db->loadObjectList();        
		return $list;        
	}
}