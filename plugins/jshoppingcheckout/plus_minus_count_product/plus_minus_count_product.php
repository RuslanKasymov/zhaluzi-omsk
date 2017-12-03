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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class plgJshoppingCheckoutPlus_Minus_Count_Product extends JPlugin{


public function onAfterInitialise()
	{
		JFactory::getDocument()->addScriptDeclaration('	function changeQuantityCartLight(curInput){
						curInput = "input[name=\'"+curInput+"\']";
						console.log(curInput);
			            var initBlock = jQuery(curInput).parents(".quantity");
			            console.log(initBlock);
			            var qVal = parseInt(jQuery(curInput, initBlock).val());
			            var minVal = parseInt(jQuery(".min_quantity", initBlock).val());
			            console.log(qVal);
			            console.log(minVal);
									if (isNaN(minVal))
										minVal = 1;
			            if ( (typeof qVal !=="undefined") && ( qVal !="") && ( qVal >= minVal) ) {
			                jQuery(".check_prod_quantity", initBlock).val("0");
			                jQuery(".cart_reload", initBlock).removeClass(\'no_reload_please\');
							
			                console.log("в норме");
			            } else {
			                jQuery(".check_prod_quantity", initBlock).val("1");
			                jQuery(".cart_reload", initBlock).addClass(\'no_reload_please\');
			                console.log("не проходит");
			            };
			        };');
	}



	function onBeforeDisplayCartView(&$view){
		foreach($view->products as $key => $product){
			$view->products[$key]['_qty_unit'] =
			'&nbsp;<input class = "product_minus" type = "button" onclick = "
			var qty_el = document.getElementsByName(\'quantity['.$key.']\');
			for ( keyVar in qty_el) {
			if( !isNaN( qty_el[keyVar].value ) && qty_el[keyVar].value > 1) qty_el[keyVar].value--;
			} changeQuantityCartLight(\'quantity['.$key.']\'); return false;">
			<input class = "product_plus" type = "button" onclick = "
			var qty_el = document.getElementsByName(\'quantity['.$key.']\');
			for ( keyVar in qty_el) {
			if( !isNaN( qty_el[keyVar].value )) qty_el[keyVar].value++;
			}; changeQuantityCartLight(\'quantity['.$key.']\'); return false;">';
		}
	}
}
?>