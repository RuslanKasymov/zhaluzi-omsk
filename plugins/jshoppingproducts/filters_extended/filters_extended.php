<?php
defined('_JEXEC') or die('Restricted access');
error_reporting(error_reporting() & ~E_NOTICE);

class plgJshoppingProductsFilters_extended extends JPlugin
{
    var $cache_ext_query = null;
	
	function _getContextFilter() {
		$category_id = JRequest::getInt('category_id');
		$manufacturer_id = JRequest::getInt('manufacturer_id');
		$vendor_id = JRequest::getInt('vendor_id');

		$contextfilter = '';
		if (JRequest::getVar('controller')=='category'){
			$contextfilter = 'jshoping.list.front.product.cat.'.$category_id;
		}
		if (JRequest::getVar('controller')=='manufacturer'){
			$contextfilter = 'jshoping.list.front.product.manf.'.$manufacturer_id;
		}    
		if (JRequest::getVar('controller')=='vendor'){
			$contextfilter = 'jshoping.list.front.product.vendor.'.$vendor_id;  
		}  
		if (JRequest::getVar('controller')=='products'){
			$contextfilter = 'jshoping.list.front.product.fulllist';   
		}
		return $contextfilter;
	}
    
    function _getExtQuery($type, $adv_result, $adv_from, $adv_query){
        if ($this->cache_ext_query!==null) return $this->cache_ext_query;
        
        $cache_ext_query = "";
        $mainframe = JFactory::getApplication(); 
        $db = JFactory::getDBO(); 
        $jshopConfig = JSFactory::getConfig();
        
		$contextfilter = $this->_getContextFilter();
        
        $attribut_active_value = $mainframe->getUserStateFromRequest( $contextfilter.'attr_val', 'attr_val', array()); 
        $attribut_active_value = filterAllowValue($attribut_active_value, "int+");
        
        $quantity_filter = $mainframe->getUserStateFromRequest( $contextfilter.'quantity_filter', 'quantity_filter');   
        
        $photo_filter = $mainframe->getUserStateFromRequest( $contextfilter.'photo_filter', 'photo_filter'); 
        
        $delivery_time_active = $mainframe->getUserStateFromRequest( $contextfilter.'delivery_times', 'delivery_times', array());
        $delivery_time_active = filterAllowValue($delivery_time_active, "int+");   
        
        if ($attribut_active_value){
            $query = " SELECT `attr_id` FROM `#__jshopping_attr_values` WHERE `value_id` in (".implode(",",$attribut_active_value).") GROUP BY attr_id";  
            $db->setQuery($query); 
            $attr_id = $db->loadColumn();  
        }
        
        if ($attr_id){
            //independent attribut 
            $query = "SELECT a.attr_id, av.value_id, ap.product_id FROM `#__jshopping_attr` AS a 
            LEFT JOIN  `#__jshopping_attr_values` AS av ON (av.attr_id=a.attr_id)
            LEFT JOIN  `#__jshopping_products_attr2` AS ap ON (av.value_id=ap.attr_value_id) 
            WHERE av.value_id in (".implode(",",$attribut_active_value).") AND a.independent='1' ORDER BY a.attr_id";  
            $db->setQuery($query);
            $attr_array_independent = $db->loadObjectList();  


            if ($attr_array_independent){
                foreach ($attr_array_independent AS $_attr_arr)
                {
                    $attr_ind[$_attr_arr->attr_id] .= $_attr_arr->product_id.',';
                }
            }
            
            $adv_query_independent="";
            if ($attr_ind) foreach ($attr_ind AS $key=>$value)
            {
                $array_res = array_unique(explode(",",substr($value,0,strlen($value)-1)));
                $adv_query_independent.=" AND prod.product_id in (".implode(",",$array_res).") "; 
            }
            $cache_ext_query.= $adv_query_independent;
            
            //dependent attribut 
            $query = " SELECT `attr_id` FROM `#__jshopping_attr` WHERE `attr_id` in (".implode(",",$attr_id).") AND `independent`='0'";  
            $db->setQuery($query); 
            $attr_id_depend = $db->loadColumn();          

            $product_id_depend=array(); 
            if (count($attr_id_depend)>0)
            {

                $_attr_id_depend = implode(",",$attribut_active_value); 
                $_where = "";
                foreach ($attr_id_depend as $key => $attr_key) 
                {
                    $_where .= "  `attr_".$attr_key."` in (".$_attr_id_depend.") ";
                    if ($key<count($attr_id_depend)-1) $_where .= " AND "; 
                }
                if ($jshopConfig->hide_product_not_avaible_stock){
                    $_where.=" and `count`>0 ";
                }
                if ($_where!="") $_where = " WHERE ".$_where;

                $query = " SELECT `product_id` FROM `#__jshopping_products_attr` ".$_where." GROUP BY product_id";  
                $db->setQuery($query); 
                $product_id_depend = $db->loadColumn();                
                  
                if (count($product_id_depend)>0) 
                    $cache_ext_query.=" AND prod.product_id in (".implode(",",$product_id_depend).") "; 
                else 
                    $cache_ext_query.=" AND prod.product_id = '0' "; 
            }
        } 
        
        if ($quantity_filter == '1')   $cache_ext_query.=" AND (prod.product_quantity > '0' OR  prod.unlimited = '1') ";  
        if ($quantity_filter == '2')   $cache_ext_query.=" AND (prod.product_quantity = '0' AND prod.unlimited = '0') "; 
        
        if (version_compare(JVERSION, '3.0.0', '>=')){
            $image_field = 'image';
        } else {
            $image_field = 'product_name_image';
        }
        
        if ($photo_filter == '1')   $cache_ext_query.=" AND prod.".$image_field." != '' ";  
        if ($photo_filter == '2')   $cache_ext_query.=" AND prod.".$image_field." = '' "; 
        
        if (count($delivery_time_active)>0)  $cache_ext_query.=" AND prod.delivery_times_id in (".implode(",",$delivery_time_active).") ";
    
        $this->cache_ext_query = $cache_ext_query;
    return $this->cache_ext_query;
    }
    
    function onBeforeQueryGetProductList($type, &$adv_result, &$adv_from, &$adv_query, &$order_query){
        $ext_query = $this->_getExtQuery($type, $adv_result, $adv_from, $adv_query);
        $adv_query .= $ext_query;
    }
    
    function onBeforeQueryCountProductList($type, &$adv_result, &$adv_from, &$adv_query){
        $ext_query = $this->_getExtQuery($type, $adv_result, $adv_from, $adv_query);
        $adv_query .= $ext_query;
    }
    
    function onAfterWillBeUseFilterFunc(&$filters, &$res){
        $contextfilter = $this->_getContextFilter();
        $mainframe = JFactory::getApplication();
        $attribut_active_value = $mainframe->getUserStateFromRequest( $contextfilter.'attr_val', 'attr_val', array()); 
        $attribut_active_value = filterAllowValue($attribut_active_value, "int+");
        $quantity_filter = $mainframe->getUserStateFromRequest($contextfilter.'quantity_filter', 'quantity_filter');
        $photo_filter = $mainframe->getUserStateFromRequest($contextfilter.'photo_filter', 'photo_filter');        
        $delivery_time_active = $mainframe->getUserStateFromRequest($contextfilter.'delivery_times', 'delivery_times', array());
        $delivery_time_active = filterAllowValue($delivery_time_active, "int+");
        
        if (count($attribut_active_value)) $res = 1;
        if (count($delivery_time_active)) $res = 1;
        if ($quantity_filter) $res = 1;
        if ($photo_filter) $res = 1;
    }
    
    function onBeforeDisplayProductListView(&$view){
        $session = JFactory::getSession();
        if ($view->display_list_products =='0'){
            if ($view->filters['price_from']>0) $res = 1;
            if ($view->filters['price_to']>0) $res = 1;
            if (count($view->filters['categorys'])>0) $res = 1;
            if (count($view->filters['manufacturers'])>0) $res = 1;    
            if (count($view->filters['vendors'])>0) $res = 1;    
            if (count($view->filters['labels'])>0) $res = 1;
            if (count($view->filters['extra_fields'])>0) $res = 1;
			
			$contextfilter = $this->_getContextFilter();
			$mainframe = JFactory::getApplication();
			$attribut_active_value = $mainframe->getUserStateFromRequest( $contextfilter.'attr_val', 'attr_val', array()); 
			$attribut_active_value = filterAllowValue($attribut_active_value, "int+");
            
            $quantity_filter = $mainframe->getUserStateFromRequest($contextfilter.'quantity_filter', 'quantity_filter');        
            
            $photo_filter = $mainframe->getUserStateFromRequest($contextfilter.'photo_filter', 'photo_filter');             
            
            $delivery_time_active = $mainframe->getUserStateFromRequest($contextfilter.'delivery_times', 'delivery_times', array());
            $delivery_time_active = filterAllowValue($delivery_time_active, "int+");
            
            if (count($attribut_active_value)) $res = 1;
            if (count($delivery_time_active)) $res = 1;
            if ($quantity_filter) $res = 1;
            if ($photo_filter) $res = 1;
			
            $session->set('show_mod_in_category', $res);   
        }else{
            $session->set('show_mod_in_category', 1);
        }
    }
}