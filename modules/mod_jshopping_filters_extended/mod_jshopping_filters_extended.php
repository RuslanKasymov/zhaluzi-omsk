<?php
/**
* @version      4.1.2
* @author       MAXXmarketing GmbH
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      MAXXmarketing
*/
defined('_JEXEC') or die('Restricted access');
error_reporting(error_reporting() & ~E_NOTICE);

if (!file_exists(JPATH_SITE.'/components/com_jshopping/jshopping.php')){
    JError::raiseError(500,"Please install component \"joomshopping\"");
}

require_once(JPATH_SITE.'/components/com_jshopping/lib/factory.php'); 
require_once(JPATH_SITE.'/components/com_jshopping/lib/functions.php');
JSFactory::loadCssFiles();
JSFactory::loadLanguageFile();
$jshopConfig = JSFactory::getConfig();
$mainframe = JFactory::getApplication(); 
$show_manufacturers = $params->get('show_manufacturers');         
$show_categorys = $params->get('show_categorys');  
$show_vendors = $params->get('show_vendors');         
$show_prices = $params->get('show_prices');
$show_prices_slider = $params->get('show_prices_slider');
$show_labels = $params->get('show_labels');
$show_characteristics = $params->get('show_characteristics');
$show_attribute_image = (array)$params->get('show_attribute_image');  
$show_attributes = $params->get('show_attributes');   
$show_quantity = $params->get('show_quantity'); 
$show_photo_filter = $params->get('show_photo_filter');   
$show_delivery_time = $params->get('show_delivery_time'); 
$show_attributes_id = (array)$params->get('attr_id');
$show_characteristics_id = (array)$params->get('char_id');
$display_unavailable_value = $params->get('display_unavailable_value');
$_filter_order = $params->get('filter_order');
$filter_order = explode(',',$_filter_order);
$show_horizontal = $params->get('show_horizontal');
$params_columns_count = $params->get('columns_count', 1);
$span = $show_horizontal ? ' span'.$params_columns_count : '';
$columns_count = (int)(12/$params_columns_count);
$show_characteristics_button = $params->get('show_characteristics_button');
$show_price_button = $params->get('show_price_button');
$use_select_chosen = $params->get('use_select_chosen');
$use_select_chosen_multiple = $params->get('use_select_chosen_multiple');
require_once dirname(__FILE__).'/helper.php';
$document = JFactory::getDocument(); 
$document->addStyleSheet(JURI::base()."modules/mod_jshopping_filters_extended/css/mod_jshopping_filters.css");
$document->addStyleSheet(JURI::base()."modules/mod_jshopping_filters_extended/css/chosen.css");
$document->addScript(JURI::base()."modules/mod_jshopping_filters_extended/js/chosen.jquery.js");
$document->addScript(JURI::base()."modules/mod_jshopping_filters_extended/js/chosen.js");
$document->addStyleSheet(JURI::base()."modules/mod_jshopping_filters_extended/css/j2.css");
$session = JFactory::getSession();  
$display_fileters = 0;
$task = JRequest::getVar("task");
$controller = JRequest::getVar("controller");
if ($task=="") $task = "display";

$show_on_all_pages = $params->get('show_on_all_pages', 0);

if (($controller=="category" && JRequest::getInt("category_id") && $session->get('show_mod_in_category')) ||
	($controller=="manufacturer" && JRequest::getInt("manufacturer_id")) ||
	($controller=="vendor" && JRequest::getInt("vendor_id")) ||
	($controller=="products" && $task=="display")) {
		$display_fileters = 1;
		$form_action = $_SERVER['REQUEST_URI'];
	}

if ($show_on_all_pages) $display_fileters = 1;

if (!$display_fileters) return "";

if (!isset($form_action)){
	$form_action = SEFLink('index.php?option=com_jshopping&controller=products', 1, 1);
}

$prices_list = array();

for($i=1;$i<=10;$i++){
    $prices_list[$i] = array($params->get('pricef'.$i), $params->get('pricet'.$i));
    if (!$prices_list[$i][0] && !$prices_list[$i][1]) unset($prices_list[$i]);
}

$category_id = JRequest::getInt('category_id');
$manufacturer_id = JRequest::getInt('manufacturer_id');
$vendor_id = JRequest::getInt("vendor_id"); 

$contextfilter = "";
if ($controller=="category"){
    $contextfilter = "jshoping.list.front.product.cat.".$category_id;
}
if ($controller=="manufacturer"){
    $contextfilter = "jshoping.list.front.product.manf.".$manufacturer_id;
}    
if ($controller=="vendor"){
    $contextfilter = "jshoping.list.front.product.vendor.".$vendor_id;  
}  
if ($controller=="products"){
    $contextfilter = "jshoping.list.front.product.fulllist";
}
if ($show_manufacturers && $controller!='manufacturer'){
    $category = JTable::getInstance('category', 'jshop');
    $category->load($category_id);
    
    $manufacturers = $mainframe->getUserStateFromRequest($contextfilter.'manufacturers', 'manufacturers', array());
    $manufacturers = filterAllowValue($manufacturers, "int+");    
    
	if ($category_id!=''){
        $filter_manufactures = $category->getManufacturers();
    }elseif ($vendor_id!=''){
        $filter_manufactures = modJshopping_filters_extendedHelper::getManufacturersForVendors($vendor_id);  
    }else{
		$_manufacturers = JTable::getInstance('manufacturer', 'jshop');  
		$ordering = $jshopConfig->manufacturer_sorting==1 ? 'ordering' : 'name';
        $filter_manufactures = $_manufacturers->getAllManufacturers(1, $ordering);
        
        foreach ($filter_manufactures as $key=>$value){
            $filter_manufactures[$key]->id = $filter_manufactures[$key]->manufacturer_id;
        }
    }
}

if ($show_categorys && $controller!='category'){
    $manufacturer = JTable::getInstance('manufacturer', 'jshop');        
    $manufacturer->load($manufacturer_id);
    
    $categorys = $mainframe->getUserStateFromRequest( $contextfilter.'categorys', 'categorys', array());
    $categorys = filterAllowValue($categorys, "int+");
    
    if ($manufacturer_id!=''){
        $filter_categorys = $manufacturer->getCategorys();
    }elseif ($vendor_id!=''){
        $filter_categorys = modJshopping_filters_extendedHelper::getCategorysForVendors($vendor_id); 
    }else{
        $filter_categorys = buildTreeCategory(1); 
        foreach($filter_categorys as $key=>$value){
            $filter_categorys[$key]->id = $filter_categorys[$key]->category_id;
        }            
    }
}

if ($show_vendors && $controller!="vendor"){
    $vendor = JTable::getInstance('vendor', 'jshop');
    $vendors = $mainframe->getUserStateFromRequest( $contextfilter.'vendors', 'vendors', array());
    $vendors = filterAllowValue($vendors, "int+");
    
    if ($category_id!='')
        $filter_vendors = modJshopping_filters_extendedHelper::getVendorsForCategory($category_id);
    elseif($manufacturer_id!='')
        $filter_vendors = modJshopping_filters_extendedHelper::getVendorsForManufacturer($manufacturer_id);
    else
        $filter_vendors = $vendor->getAllVendors(1, 0, $vendor->getCountAllVendors(1));
}

$price_from = saveAsPrice(JRequest::getVar('price_from'));
$price_to = saveAsPrice(JRequest::getVar('price_to'));
$fprice_from = $mainframe->getUserStateFromRequest( $contextfilter.'fprice_from', 'fprice_from');
$fprice_from = saveAsPrice($fprice_from);
if (!$fprice_from) $fprice_from = $price_from;
$fprice_to = $mainframe->getUserStateFromRequest( $contextfilter.'fprice_to', 'fprice_to');
$fprice_to = saveAsPrice($fprice_to);
if (!$fprice_to) $fprice_to = $price_to;


if ($show_prices_slider){
    $maxminPrices = modJshopping_filters_extendedHelper::getInProductsMaxMinPrice($category_id,$manufacturer_id,$vendor_id);
    if ($maxminPrices[count]>1){
        $document->addStyleSheet(JURI::base()."modules/mod_jshopping_filters_extended/css/jquery-ui-slider.css");
        $document->addScript(JURI::base()."modules/mod_jshopping_filters_extended/js/jquery-ui.min-slider.js");
        $min_price = $fprice_from ? $fprice_from : $maxminPrices['min_price'];
        $max_price = $fprice_to ? $fprice_to : $maxminPrices['max_price'];
        $document->addScriptDeclaration(
            'var currency = "'.$jshopConfig->currency_code.'";'
            .'var max_price = "'.$max_price.'";'
            .'var min_price = "'.$min_price.'";'
            .'jQuery( document ).ready(function() {
            jQuery("#slider-range").prop("slide",null);    
            jQuery( "#slider-range" ).slider({
            range: true,
            min: '.$maxminPrices['min_price'].',
            max: '.$maxminPrices['max_price'].',
            values: [ '.$min_price.', '.$max_price.' ],
            slide: function( event, ui ) {
            jQuery( "#price_from" ).val(ui.values[ 0 ]);
            jQuery( "#price_to" ).val(ui.values[ 1 ]);
            jQuery( "#amount" ).text( currency +" "+ ui.values[ 0 ] + " - " + currency +" "+ ui.values[ 1 ] );
            }
            });
            jQuery( "#amount" ).text( currency +" "+ jQuery( "#slider-range" ).slider( "values", 0 ) +
              " - " + currency +" "+ jQuery( "#slider-range" ).slider( "values", 1 ) );    
            });
        ');          
    }  
}


if ($show_characteristics){
    $characteristic_fields = JSFactory::getAllProductExtraField();
    if ($controller=="category"){
        foreach($characteristic_fields as $k=>$val){
            $_display = 0;
            if ($val->allcats){
                $_display = 1; 
            }else{
                if (in_array($category_id, $val->cats)) $_display = 1;
            }
            if (!$_display) unset($characteristic_fields[$k]);
        }
    }
    $characteristic_fieldvalues = JSFactory::getAllProductExtraFieldValueDetail();
    $characteristic_fieldvaluesInProducts = modJshopping_filters_extendedHelper::getInProductsCharacteristic($category_id,$manufacturer_id,$vendor_id, $show_characteristics_id);
    $characteristic_fieldtextInProducts = modJshopping_filters_extendedHelper::getInProductsCharacteristicText($category_id,$manufacturer_id,$vendor_id, $show_characteristics_id); 
    $context_ef = $contextfilter.'extra_fields';        
    $extra_fields_active = $mainframe->getUserStateFromRequest( $context_ef, 'extra_fields', array());
    $extra_fields_active = filterAllowValue($extra_fields_active, "array_int_k_v+"); 
}

if ($show_labels){
    $productLabel = JTable::getInstance('productLabel', 'jshop');
    $listLabels = $productLabel->getListLabels();
    $labelInProducts = modJshopping_filters_extendedHelper::getInProductsLabels($category_id,$manufacturer_id,$vendor_id);
    $labels_active = $mainframe->getUserStateFromRequest( $contextfilter.'labels', 'labels', array());
    $labels_active = filterAllowValue($labels_active, "int+");
}
    
if ($show_attributes){
    $attribut = JTable::getInstance('attribut', 'jshop');
    $attributvalue = JTable::getInstance('attributvalue', 'jshop');
    $listAttribut = $attribut->getAllAttributes();
    $attributvaluesInProducts = modJshopping_filters_extendedHelper::getInProductsAttribut($category_id, $manufacturer_id, $vendor_id);
    foreach($listAttribut as $key=>$value){
        if ($controller=="category"){                
            $_display = 0;
            if ($value->allcats){
                $_display = 1; 
            }else{
                if (in_array($category_id, $value->cats)) $_display = 1;
            }
            if (!$_display){
                unset($listAttribut[$key]);
                continue;
            }
        }
        
        if (in_array($value->attr_id, $show_attributes_id) || in_array(0, $show_attributes_id)){
            $values_for_attribut = $attributvalue->getAllValues($value->attr_id);                
            if (!count($values_for_attribut)){
                unset($listAttribut[$key]);
                continue;
            }
            $listAttribut[$key]->values = $values_for_attribut;     
        }else{
            unset($listAttribut[$key]);
        }
    }
    
    $attribut_active = $mainframe->getUserStateFromRequest( $contextfilter.'attr_val', 'attr_val', array());
    $attribut_active = filterAllowValue($attribut_active, "int+");
}

if ($show_quantity){
    $quantity_filter = $mainframe->getUserStateFromRequest( $contextfilter.'quantity_filter', 'quantity_filter');
}  

if ($show_photo_filter) {
    $photo_filter = $mainframe->getUserStateFromRequest( $contextfilter.'photo_filter', 'photo_filter');
}
     
if ($show_delivery_time){
    $deliveryTimes = JTable::getInstance('deliveryTimes', 'jshop');
    $listDeliveryTimes = $deliveryTimes->getDeliveryTimes();
    $delivery_timeInProducts    = modJshopping_filters_extendedHelper::getInProductsDelivery_time($category_id,$manufacturer_id,$vendor_id);   
    $delivery_time_active = $mainframe->getUserStateFromRequest( $contextfilter.'delivery_times', 'delivery_times', array());
    $delivery_time_active = filterAllowValue($delivery_time_active, "int+");
}   

require(JModuleHelper::getLayoutPath('mod_jshopping_filters_extended'));        
?>