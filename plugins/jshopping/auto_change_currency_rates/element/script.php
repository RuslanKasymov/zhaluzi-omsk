<?php
/**
* @package Joomla
* @subpackage JoomShopping
* @author Dmitry Stashenko
* @website http://nevigen.com/
* @email support@nevigen.com
* @copyright Copyright © Nevigen.com. All rights reserved.
* @license Proprietary. Copyrighted Commercial Software
* @license agreement http://nevigen.com/license-agreement.html
**/

defined('_JEXEC') or die;

class JFormFieldScript extends JFormField {

	protected function getLabel(){
		return;
	}

	protected function getInput(){
		$script = "
jQuery(document).ready(function(){
	check_selector_bank();
	jQuery('#jform_params_bank').change(function(){
		check_selector_bank();
	});

});

function check_selector_bank() {
	if (jQuery('#jform_params_bank').val() == 7)
		jQuery('#jform_params_xml, #jform_params_xml_date, #jform_params_node, #jform_params_node_iso, #jform_params_node_rate, #jform_params_node_nominal, #jform_params_type, #jform_params_currency').parent().parent().show();
	else
		jQuery('#jform_params_xml, #jform_params_xml_date, #jform_params_node, #jform_params_node_iso, #jform_params_node_rate, #jform_params_node_nominal, #jform_params_type, #jform_params_currency').parent().parent().hide();
}
";
		JFactory::getDocument()->addScriptDeclaration($script);
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'refresh', JText::_('MOD_AUTO_CHANGE_CURRENCY_RATES_UPDATE_NOW'), JURI::root().'index.php?option=com_jshopping&update_currency_rates=1&back='.urlencode($_SERVER['REQUEST_URI']));
		return;
	}
}
?>
