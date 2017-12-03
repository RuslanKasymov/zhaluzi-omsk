<?php
error_reporting(error_reporting() & ~E_NOTICE);

class JFormFieldAttributes extends JFormField {

  public $type = 'attributes';
  
  protected function getInput(){
        require_once (JPATH_SITE.'/components/com_jshopping/lib/factory.php'); 
      /*  require_once (JPATH_SITE.'/components/com_jshopping/lib/jtableauto.php');  
        require_once (JPATH_SITE.'/components/com_jshopping/tables/attribut.php'); 
        $attribut = JTable::getInstance('attribut', 'jshop');
        $listAttribut = $attribut->getAllAttributes(); */
         $jshopConfig = JSFactory::getConfig(); 
         
        $db = JFactory::getDBO(); 
        $query = "SELECT attr_id, `name_".$jshopConfig->frontend_lang ."` as name FROM `#__jshopping_attr` ORDER BY attr_ordering";
        $db->setQuery($query);
        $listAttribut = $db->loadObjectList();
        
        $tmp = new stdClass();  
        $tmp->attr_id = "0";
        $tmp->name = JText::_('JALL');
        $attr_1  = array($tmp);
        $attribut_select =array_merge($attr_1 , $listAttribut);    

        $ctrl  =  $this->name ;   
        //$ctrl  = $this->control_name .'['. $this->name .']';   
        //$ctrl  = 'jform[params][catids]'; 
        $ctrl .= '[]'; 
        
        $value        = empty($this->value) ? '0' : $this->value;    

        return JHTML::_('select.genericlist', $attribut_select,$ctrl,'class="inputbox" id = "attribut_ordering" multiple="multiple"','attr_id','name', $value );
  }
}
?>