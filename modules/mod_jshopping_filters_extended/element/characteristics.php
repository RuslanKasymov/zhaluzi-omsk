<?php
error_reporting(error_reporting() & ~E_NOTICE);

class JFormFieldCharacteristics extends JFormField {

  public $type = 'characteristics';
  
  protected function getInput(){
        require_once (JPATH_SITE.'/components/com_jshopping/lib/factory.php'); 
         $jshopConfig = JSFactory::getConfig(); 

        
        $db = JFactory::getDBO(); 
        $ordering = "G.ordering, F.ordering";
   
        $query = "SELECT F.id, F.`name_".$jshopConfig->frontend_lang ."` as name, F.allcats, F.type, F.cats, F.ordering, F.`group`, G.`name_".$jshopConfig->frontend_lang ."` as groupname FROM `#__jshopping_products_extra_fields` as F left join `#__jshopping_products_extra_field_groups` as G on G.id=F.group order by ".$ordering;        
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $list = array();        
        foreach($rows as $k=>$v){
            $list[$v->id] = $v;
            if ($v->allcats){
                $list[$v->id]->cats = array();
            }else{
                $list[$v->id]->cats = unserialize($v->cats);
            }            
        }
        unset($rows);
    

        
        $tmp = new stdClass();  
        $tmp->id = "0";
        $tmp->name = JText::_('JALL');
        $char_1  = array($tmp);
        $char_select =array_merge($char_1 , $list);    

        $ctrl  =  $this->name ;   
        //$ctrl  = $this->control_name .'['. $this->name .']';   
        //$ctrl  = 'jform[params][catids]'; 
        $ctrl .= '[]'; 
        
        $value        = empty($this->value) ? '0' : $this->value;    

        return JHTML::_('select.genericlist', $char_select,$ctrl,'class="inputbox" id = "characteristic" multiple="multiple"','id','name', $value );
  }
}
?>