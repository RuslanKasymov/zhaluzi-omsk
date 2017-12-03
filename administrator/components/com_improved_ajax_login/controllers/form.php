<?php
/*-------------------------------------------------------------------------
# com_improved_ajax_login - com_improved_ajax_login
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Form controller class.
 */
class Improved_ajax_loginControllerForm extends JControllerForm
{

  function __construct()
  {
    $this->view_list = 'forms';
    parent::__construct();
  }

	public function save($key = null, $urlVar = null)
  {
    $saved = parent::save($key, $urlVar);
    // generate xml to User - Improved Profile plugin
    if ($saved)
    {
      $data = JRequest::getVar('jform', array(), 'array');
      // fix for magic quotes
      if (get_magic_quotes_gpc())
      {
        $props = json_decode($data['props']);
        if ($props == null) foreach ($data as $key=>$value)
        {
          $data[$key] = stripslashes($value);
        }
      }
      $fields = json_decode($data['fields']);
      if ($data['state'] && isset($fields->page))
      {
        function getAttr($obj, $name)
        {
          $name = 'jform[elem_'.$name.']';
          return isset($obj->{$name})? $obj->{$name} : null;
        }
        $captcha = 0;
        foreach ($fields->page as $page)
        {
        	foreach ($page->elem as $elem)
          {
            $type = getAttr($elem, 'type');
            if ($type->value == 'captcha') $captcha = 1;
          }
        }
        // init recaptcha
        $db = JFactory::getDBO();
        $db->setQuery("UPDATE #__extensions SET custom_data = ".($captcha? "'IALR'" : "''")." WHERE name = 'plg_captcha_recaptcha'");
        $db->query();
      }
    }
    return $saved;
  }

}