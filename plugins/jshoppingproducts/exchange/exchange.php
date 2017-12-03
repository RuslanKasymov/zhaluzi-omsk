<?php
defined('_JEXEC') or die('Restricted access');

class plgJshoppingProductsExchange extends JPlugin{
    
    function onBeforeLoadProductList(){
        $handle = fopen(JPATH_PLUGINS.'/jshoppingproducts/exchange/update.ini','r');
        $last_exchange = fread($handle, 4096);
        fclose($handle);
        $period = $this->params->get('period', 24);
        $center_update = $this->params->get('source', 'ecb');
        $margin = (float)$this->params->get('margin', 0);
        $now = time();
        $developer_mode = $this->params->get('developer_mode',0);
        if(!$developer_mode){
            $text = '';
            if (($last_exchange+($period*60*60)) > $now) return 0;
        }else{
            $text = '<br>New values:<br><hr>';
        }
        
        $jshopConfig = JSFactory::getConfig();
        $db = jfactory::getDbo();
        //----- Curency ------- 
        $currency = JTable::getInstance('currency', 'jshop');
        //----- All Currencies -----
        $AllCurrencies = $currency->getAllCurrencies();
        //----- Default Currency -----
        $defaultCurrency = null;
		
        foreach($AllCurrencies as $data){
            if ($jshopConfig->mainCurrency == $data->currency_id){
                $defaultCurrency = $data;
                break;
            }
        }
        if (!$defaultCurrency) return 0;
        
        switch ($center_update){
            case 'ecb':
                $source_path = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
                $xmlObject = simplexml_load_file($source_path);
                $json = json_encode($xmlObject);
                $array_xml = json_decode($json, TRUE);

                $tmp=$array_xml['Cube']['Cube']['Cube'];
                $xml_array=array();
                foreach($tmp as $data){
                        $xml_array[$data['@attributes']['currency']]=$data['@attributes']['rate'];
                }
                $xml_array['EUR']=1.000000;

                foreach($AllCurrencies as $key=>$data){
                    if($defaultCurrency->currency_id == $data->currency_id)continue;

                    if ($xml_array[$data->currency_code_iso] and isset($xml_array[$defaultCurrency->currency_code_iso])){
                        $t = $xml_array[$data->currency_code_iso]/($xml_array[$defaultCurrency->currency_code_iso]*$defaultCurrency->currency_value);
                        if($margin != 0){
                            $x = ($t*$margin)/100;
                        }
                        $data->currency_value=$t + $x;
                        if($developer_mode and $margin != 0) echo $t.'+'.$x.'='.$data->currency_value.'<br>';
                        $AllCurrencies[$key]=$data;
                    }
                }
            break;
            case 'forex':
                $source_path = 'http://rss.timegenie.com/forex.xml';
                $xmlObject = simplexml_load_file($source_path);
                $xml_array = array();
                foreach($xmlObject as $field=>$data){
                    if($field == 'data'){
                        $json = json_encode($data);
                        $array_xml = json_decode($json, TRUE);
                        $xml_array[$array_xml['code']] = $array_xml['rate'];
                    }
                }
                $xml_array['EUR']=1.000000;

                foreach($AllCurrencies as $key=>$data){
                    if($defaultCurrency->currency_id == $data->currency_id)continue;

                    if ($xml_array[$data->currency_code_iso] and isset($xml_array[$defaultCurrency->currency_code_iso])){
                        $t = $xml_array[$data->currency_code_iso]/($xml_array[$defaultCurrency->currency_code_iso]*$defaultCurrency->currency_value);
                        if($margin != 0){
                            $x = ($t*$margin)/100;
                        }
                        $data->currency_value=$t + $x;
                        if($developer_mode and $margin != 0) echo $t.'+'.$x.'='.$data->currency_value.'<br>';
                        $AllCurrencies[$key]=$data;
                    }
                }
            break;
            case 'cbr':
                $source_path = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d/m/Y');

                $xmlObject = simplexml_load_file($source_path);

                $xml_array=array();
                $array_xml = json_encode($xmlObject);
                $array_xml = json_decode($array_xml);
                $array_xml = $array_xml->Valute;
                
                foreach($array_xml as $data){
                    $prom = str_replace(',','.',$data->Value);
                    $xml_array[$data->CharCode] = $prom/$data->Nominal;
                }
                $xml_array['RUB']=1.000000;

                foreach($AllCurrencies as $key=>$data){
                    if($defaultCurrency->currency_id == $data->currency_id)continue;
                    //$prom = 1/$xml_array[$defaultCurrency->currency_code_iso];
                    if ($xml_array[$data->currency_code_iso] and isset($xml_array[$defaultCurrency->currency_code_iso])){
                        $t = $xml_array[$defaultCurrency->currency_code_iso]/($xml_array[$data->currency_code_iso]/$defaultCurrency->currency_value);
                        if($margin != 0){
                            $x = ($t*$margin)/100;
                        }
                        $data->currency_value=$t + $x;
                        if($developer_mode and $margin != 0) echo $t.'+'.$x.'='.$data->currency_value.'<br>';
                        //echo '<br>'.$data->currency_code_iso.'= '.$xml_array[$data->currency_code_iso].'*('.$xml_array[$defaultCurrency->currency_code_iso].'/'.$defaultCurrency->currency_value.')';
                        $AllCurrencies[$key]=$data;
                    }
                }
            break;
            case 'nbu':
                $source_path = 'http://pfsoft.com.ua/service/currency/';

                $xmlObject = simplexml_load_file($source_path);
                $xml_array=array();
                $array_xml = json_encode($xmlObject);
                $array_xml = json_decode($array_xml);
                $array_xml = $array_xml->Valute;
                
                foreach($array_xml as $data){
                    $prom = str_replace(',','.',$data->Value);
                    $xml_array[$data->CharCode] = $prom/$data->Nominal;
                }
                $xml_array['UAH']=1.000000;

                foreach($AllCurrencies as $key=>$data){
                    if($defaultCurrency->currency_id == $data->currency_id)continue;
                    //$prom = 1/$xml_array[$defaultCurrency->currency_code_iso];
                    if ($xml_array[$data->currency_code_iso] and isset($xml_array[$defaultCurrency->currency_code_iso])){
                        $t = $xml_array[$defaultCurrency->currency_code_iso]/($xml_array[$data->currency_code_iso]/$defaultCurrency->currency_value);
                        if($margin != 0){
                            $x = ($t*$margin)/100;
                        }
                        $data->currency_value=$t + $x;
                        if($developer_mode and $margin != 0) echo $t.'+'.$x.'='.$data->currency_value.'<br>';                        
                        //echo '<br>'.$data->currency_code_iso.'= '.$xml_array[$data->currency_code_iso].'*('.$xml_array[$defaultCurrency->currency_code_iso].'/'.$defaultCurrency->currency_value.')';
                        $AllCurrencies[$key]=$data;
                    }
                }
            break;
            case 'privat':
                $source_path = 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $source_path);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: xml"));
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $xml_array = array();
                $xmlObject = new SimpleXMLElement($response);
                
                $xml_array=array();
                foreach($xmlObject as $i=>$v){
                    $g = (array)$v->exchangerate['ccy'];
                    $val = (array)$v->exchangerate['buy'];
                    $xml_array[$g[0]] = $val[0];
                }

                $xml_array['UAH']=1.000000;
                $xml_array['RUB']=$xml_array['RUR'];
                foreach($AllCurrencies as $key=>$data){
                    if($defaultCurrency->currency_id == $data->currency_id)continue;
                    //$prom = 1/$xml_array[$defaultCurrency->currency_code_iso];
                    if ($xml_array[$data->currency_code_iso] and isset($xml_array[$defaultCurrency->currency_code_iso])){
                        $t = $xml_array[$defaultCurrency->currency_code_iso]/($xml_array[$data->currency_code_iso]/$defaultCurrency->currency_value);
                        if($margin != 0){
                            $x = ($t*$margin)/100;
                        }
                        $data->currency_value=$t + $x;
                        if($developer_mode and $margin != 0) echo $t.'+'.$x.'='.$data->currency_value.'<br>';                        
                        //echo '<br>'.$data->currency_code_iso.'= '.$xml_array[$data->currency_code_iso].'*('.$xml_array[$defaultCurrency->currency_code_iso].'/'.$defaultCurrency->currency_value.')';
                        $AllCurrencies[$key]=$data;
                    }
                }
            break;
            
        }
		
		if(count($array_xml) > 0){
            $handle = fopen(JPATH_PLUGINS.'/jshoppingproducts/exchange/update.ini','w');
            fwrite($handle, $now);
            fclose($handle);
        }
        
        echo $text;
        foreach($AllCurrencies as $data){
            if($defaultCurrency->currency_id == $data->currency_id){
                if($developer_mode){
                    echo $data->currency_code_iso.' = '.$data->currency_value.'<br>';
                }
                continue;
            }
            $query = "update #__jshopping_currencies set currency_value=".$data->currency_value." where currency_code_iso='".$data->currency_code_iso."'";
            $db->setQuery($query);
            if(!$db->query()) echo $data->currency_code_iso.' error<br>';
            if($developer_mode){
                echo $data->currency_code_iso.' = '.$data->currency_value.'<br>';
            }
        }
    }
}
?>