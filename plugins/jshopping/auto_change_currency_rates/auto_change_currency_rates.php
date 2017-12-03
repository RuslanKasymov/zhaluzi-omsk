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

class plgJshoppingAuto_Change_Currency_Rates extends JPlugin {

	function onLoadJshopConfig(&$config) {
		$this->nexthour = $this->params->get('nexthour', 0);
		$this->nextdate = $this->params->get('nextdate', 19700101);
		$this->app = JFactory::getApplication();
		$this->update_currency_rates = $this->app->input->getInt('update_currency_rates', 0);
		if (!$this->update_currency_rates && !((date('G') >= $this->nexthour && date('Ymd') == $this->nextdate) || date('Ymd') > $this->nextdate)) return;
		
		$this->bank_arr = array(
			'1' => array(
				'xml' => 'http://www.cbr.ru/scripts/XML_daily.asp',
				'node' => '$xml->Valute',
				'node_iso' => '$valute->CharCode',
				'node_rate' => '$valute->Value',
				'node_nominal' => '$valute->Nominal',
				'type' => '1',
				'currency' => 'RUB'
				),
			'2' => array(
				'xml' => 'http://bank-ua.com/export/currrate.xml',
				'node' => '$xml->item',
				'node_iso' => '$valute->char3',
				'node_rate' => '$valute->rate',
				'node_nominal' => '$valute->size',
				'type' => '1',
				'currency' => 'UAH'
				),
			'3' => array(
				'xml' => 'http://www.nationalbank.kz/rss/rates_all.xml',
				'node' => '$xml->channel->item',
				'node_iso' => '$valute->title',
				'node_rate' => '$valute->description',
				'node_nominal' => '$valute->quant',
				'type' => '1',
				'currency' => 'KZT'
				),
			'4' => array(
				'xml' => 'http://www.nbrb.by/Services/XmlExRates.aspx',
				'node' => '$xml->Currency',
				'node_iso' => '$valute->CharCode',
				'node_rate' => '$valute->Rate',
				'node_nominal' => '$valute->Scale',
				'type' => '1',
				'currency' => 'BYR'
				),
			'5' => array(
				'xml' => 'http://www.bnm.md/md/official_exchange_rates?get_xml=1&date='.date('d.m.Y'),
				'node' => '$xml->Valute',
				'node_iso' => '$valute->CharCode',
				'node_rate' => '$valute->Value',
				'node_nominal' => '$valute->Nominal',
				'type' => '1',
				'currency' => 'MDL'
				),
			'6' => array(
				'xml' => 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
				'node' => '$xml->Cube->Cube->Cube',
				'node_iso' => '$valute["currency"]',
				'node_rate' => '$valute["rate"]',
				'node_nominal' => '1',
				'type' => '2',
				'currency' => 'EUR'
				),
			'7' => array(
				'xml' => $this->params->get('xml', '').($this->params->get('xml_date') ? date($this->params->get('xml_date')) : ''),
				'node' => $this->params->get('node', ''),
				'node_iso' => $this->params->get('node_iso', ''),
				'node_rate' =>  $this->params->get('node_rate', ''),
				'node_nominal' =>  $this->params->get('node_nominal', ''),
				'type' => $this->params->get('type', '1'),
				'currency' =>  $this->params->get('currency', '')
				)
		);
		$this->key = true;//$this->params->get('key');//26.12.2015 18:13
		$this->bank = $this->params->get('bank');
		if (!isset($this->bank_arr[$this->bank])) return;
		// START ///////////////////////////////////////////////////////
		$this->bank=$this->bank_arr[$this->bank];
		//  END  ///////////////////////////////////////////////////////
		$all_currency = $new_currency = array();
		$_list = JSFactory::getAllCurrency();
		if (!count($_list)) return;
		foreach ($_list as $row) {
			$row->main = $row->currency_id == $config->mainCurrency ? 1 : 0;
			$all_currency[strtolower($row->currency_code_iso)] = $row;
		}

		JFactory::getLanguage()->load('plg_jshopping_auto_change_currency_rates', dirname( __FILE__), null, false, 'en-GB');
		if (!$this->key) {
			$this->params->set('status', JText::_('MOD_AUTO_CHANGE_CURRENCY_RATES_STATUS_ERROR_KEY'));
		} else if ($xml = @simplexml_load_file($this->bank['xml'])) {
			$mainCurrencyRate = 0;
			$this->bank['currency'] = strtolower($this->bank['currency']);
			if (isset($all_currency[$this->bank['currency']]) && $all_currency[$this->bank['currency']]->main == 0) {
				$new_currency[$all_currency[$this->bank['currency']]->currency_id] = 1;
			}
			eval('$node = '.$this->bank['node'].';');
			foreach ($node as $valute) {
				eval('$iso_code = '.$this->bank['node_iso'].';');
				$iso_code = strtolower(trim($iso_code));
				if ($iso_code=='rur') $iso_code='rub';
				if (!isset($all_currency[$iso_code])) continue;

				eval('$rate = '.$this->bank["node_rate"].';');
				$rate = (float)str_replace(',', '.', $rate);
				if (is_numeric($this->bank["node_nominal"])) {
					$nominal = $this->bank["node_nominal"];
				} else {
					eval('$nominal = '.$this->bank["node_nominal"].';');
				}
				$nominal = (float)$nominal;
				$cur_rate = $rate / $nominal;
				if ($all_currency[$iso_code]->main) {
					$mainCurrencyRate = $cur_rate;
				} else {
					$new_currency[$all_currency[$iso_code]->currency_id] = $cur_rate;
				}
			}

			if (!$mainCurrencyRate && $all_currency[$this->bank['currency']]->main) {
				$mainCurrencyRate = 1;
			}
			if ($mainCurrencyRate) {
				if (count($new_currency)) {
					ksort($new_currency);
					$this->type = $this->bank['type'];
					$this->percent = $this->params->get('percent', 0);
					foreach ($new_currency as $key=>$rate) {
						$t_currency = JTable::getInstance('Currency', 'jshop');
						$t_currency->load($key);
						if ($t_currency->currency_id) {
							if ($this->type == 1) {
								$rate = $mainCurrencyRate / $rate;
							} else {
								$rate = $rate / $mainCurrencyRate;
							}
							$t_currency->currency_value = $rate + $rate * $this->percent / 100;
							$new_currency[$key] = $t_currency->currency_value;
							$t_currency->store();
						}
					}
					$config->loadCurrencyValue();
					$this->starthour = $this->params->get('starthour', 0);
					$this->stephour = $this->params->get('stephour', 24);
					$this->nowhour = date('G');
					while($this->starthour <= $this->nowhour) {
						$this->starthour += $this->stephour;
					}
					if ($this->starthour > 23) {
						$this->nexthour = $this->params->get('starthour', 0);
						$this->nextdate = strtotime('+1 day');
					} else {
						$this->nexthour = $this->starthour;
						$this->nextdate = strtotime('now');
					}
					$this->params->set('nexthour', $this->nexthour);
					$this->params->set('nextdate', date('Ymd', $this->nextdate));

					$this->params->set('status', JText::sprintf('MOD_AUTO_CHANGE_CURRENCY_RATES_STATUS_OK', implode(',',array_keys ($new_currency)), date('d.m.Y H:i:s'), date('d.m.Y', $this->nextdate), $this->nexthour));
				} else {
					$this->params->set('status', JText::_('MOD_AUTO_CHANGE_CURRENCY_RATES_STATUS_ERROR_EMPTY_CURRENCY'));
				}
			} else {
				$this->params->set('status', JText::_('MOD_AUTO_CHANGE_CURRENCY_RATES_STATUS_ERROR_MAIN_CURRENCY'));
			}
			
		} else {
			$this->params->set('status', JText::_('MOD_AUTO_CHANGE_CURRENCY_RATES_STATUS_ERROR_XML'));
		}

		$this->db = JFactory::getDBO();
		$this->query = $this->db->getQuery(true);
		$this->query->update('#__extensions')
					->set('params='.$this->db->Quote($this->params->toString()))
					->where('element='.$this->db->Quote($this->_name))
					->where('folder='.$this->db->Quote($this->_type));
		$this->db->setQuery($this->query);
		$this->db->query();
		
		if ($this->update_currency_rates) {
			$back = $this->app->input->getString('back');
			if ($back != '') {
				$this->app->redirect(urldecode($back));
			}
		}
	}

}
?>