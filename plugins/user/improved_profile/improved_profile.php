<?php

defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');

/**
 * An example custom improved_profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.improved_profile
 * @version		1.6
 */
class plgUserImproved_Profile extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
*/
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->improved) and $userId > 0)
			{
				// Load the improved data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $userId." AND profile_key LIKE 'improved.%'" .
					' ORDER BY ordering'
				);
				$results = $db->loadRowList();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}

				// Merge the improved data.
				$data->improved = array();

				foreach ($results as $v)
				{
					$k = str_replace('improved.', '', $v[0]);
					$data->improved[$k] = json_decode($v[1], true);
					if ($data->improved[$k] === null)
					{
						$data->improved[$k] = $v[1];
					}
				}
			}

			if (!JHtml::isRegistered('users.url'))
			{
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
			if (!JHtml::isRegistered('users.calendar'))
			{
				JHtml::register('users.calendar', array(__CLASS__, 'calendar'));
			}
			if (!JHtml::isRegistered('users.tos'))
			{
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if (substr ($value, 0, 4) == "http")
			{
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else
			{
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public static function calendar($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			return JHtml::_('date', $value, null, null);
		}
	}

	public static function tos($value)
	{
		if ($value)
		{
			return JText::_('JYES');
		}
		else
		{
			return JText::_('JNO');
		}
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
*/
	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

    $lang = JFactory::getLanguage();
    $lang->load('plg_user_profile', JPATH_ADMINISTRATOR);
    $lang->load('mod_improved_ajax_login');    

		// Add the registration fields to the form.
    $db = JFactory::getDBO();
    $db->setQuery('SELECT fields FROM #__offlajn_forms WHERE id = 1');
    $res = $db->loadObject();
    $fields = json_decode($res->fields);
    function getAttr($obj, $name)
    {
      $name = 'jform[elem_'.$name.']';
      return isset($obj->{$name})? $obj->{$name} : null;
    }
    $xml = '<form><fields name="improved"><fieldset name="improved" label="PLG_USER_PROFILE_SLIDER_LABEL"></fieldset></fields></form>';
    $profile = JFactory::getXML($xml, false);
    foreach ($fields->page as $page)
    {
    	foreach ($page->elem as $elem)
      {
        $type = getAttr($elem, 'type');
        $name = getAttr($elem, 'name');
        if (!isset($type->profile) || $name->value == 'newsletter') continue;
        $field = $profile->fields->fieldset->addChild('field');
        $field->addAttribute('name', $name->value? $name->value : $name->placeholder);
        $field->addAttribute('id', 'ial-'.getAttr($elem, 'name')->value);
        $field->addAttribute('type', isset($type->defaultValue)? $type->defaultValue : $type->value);
        $field->addAttribute('required', getAttr($elem, 'required')->checked? 'true' : 'false');
        $label = getAttr($elem, 'label');
        if ($label) $field->addAttribute('label', JText::_($label->value? $label->value : (@$label->defaultValue? @$label->defaultValue : @$label->placeholder)));
        $title = getAttr($elem, 'title');
        if ($label) $field->addAttribute('description', JText::_($title->value? $title->value : @$title->defaultValue));
        $error = getAttr($elem, 'error');
        if ($error) $field->addAttribute('message', JText::_($error->value? $error->value : $error->defaultValue));
        if ($type->value == 'checkbox') $field->addAttribute('value', 'on');
        if ($type->value == 'select')
        {
          $field['type'] = 'list';
          $xml = str_replace(array('[', ']'), array('<', '>'), $options = getAttr($elem, 'select')->value);
          $opts = JFactory::getXML("<select>$xml</select>", false);
          foreach ($opts as $opt)
          {
            $option = $field->addChild('option');
            $option->addAttribute('value', $opt['value']);
            $option[0] = (string) $opt;
          }
        }
        $article = getAttr($elem, 'article');
        if ($article)
        {
          $field->addAttribute('article', $article->value);
          $option = $field->addChild('option');
          $option->addAttribute('value', 'on');
          $option[0] = 'JYES';
        }
      }
    }
    $form->load($profile, false);

		if ($name != 'com_users.registration')
		{
			// We only want the TOS in the registration form
			$form->removeField('tos', 'improved');
		}

		return true;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
    $db = JFactory::getDbo();
    $userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
    $users = JComponentHelper::getParams('com_users');
    $emailBodyAdmin = '';

    $jomsocial = JRequest::getVar('jomsocial', null, 'post', 'array');

    if ($userId && $isNew && $result && isset($_SESSION['oauth']))
    {
      if (JRequest::getString('social_id') == $_SESSION['oauth']['id'])
      {
        $db->setQuery("INSERT INTO #__offlajn_users (user_id, {$_SESSION['oauth']['type']}_id) VALUES ($userId, '{$_SESSION['oauth']['id']}')");
        $db->query();
      }
      unset($_SESSION['oauth']);
    }

    if ($userId && $isNew && $result && $users->get('useractivation') < 2 && $users->get('mail_to_admin') == 'extended')
    {
      $lang = JFactory::getLanguage();
      $lang->load('com_users');
      if ($jomsocial)
      {
        $lang->load('com_community');
        $lang->load('com_community.cointries');
      }
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'], JURI::root() );
			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'], $data['username'], JURI::root() );
    }

    // JomSocial
    if ($userId && $isNew && $result && $jomsocial)
    {
      $values = array();
      foreach ($jomsocial as $k => $v)
      {
        $field_id = (int) substr($k, 5);
        $value = addcslashes($v, "'");
      	$values[] = "($userId, $field_id, '$value', 0)";
      }
      $db->setQuery("INSERT INTO #__community_fields_values (user_id, field_id, value, access) VALUES ".implode(', ', $values));
      $db->query();
      if ($emailBodyAdmin)
      {
        $emailBodyAdmin.= "\n";
        $db->setQuery("SELECT id, type, name FROM #__community_fields WHERE registration = 1 ORDER BY ordering");
        $fields = $db->loadObjectList();
        if ($fields) foreach ($fields as $f)
        {
          $emailBodyAdmin.= $f->type == 'group' ?
            "\n{$f->name}\n\n" : " {$f->name}\n\t".JText::_(@$jomsocial['field'.$f->id])."\n";
        }
      }
    }

		if ($userId && $result && isset($data['improved']) && (count($data['improved'])))
		{
			try
			{
				//Sanitize the date
				if (!empty($data['improved']['dob']))
				{
					$date = new JDate($data['improved']['dob']);
					$data['improved']['dob'] = $date->format('Y-m-d');
				}

				$db->setQuery("DELETE FROM #__user_profiles WHERE user_id = $userId AND profile_key LIKE 'improved.%'");
				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

        if ($emailBodyAdmin) $emailBodyAdmin.= "\n\n".JText::_('COM_USERS_PROFILE')."\n\n";

				foreach ($data['improved'] as $k => $v)
				{
          if ($emailBodyAdmin)
          {
            $key = array($k, JText::_("PLG_USER_PROFILE_FIELD_{$k}_LABEL"));
            $key = ($key[1] == "PLG_USER_PROFILE_FIELD_{$key[0]}_LABEL")? $key[0].':' : $key[1];
            $emailBodyAdmin.= " $key\n\t$v\n";
          }
					$tuples[] = '('.$userId.', '.$db->quote('improved.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

    if ($emailBodyAdmin)
    {
      $config = JFactory::getConfig();
			// get all admin users
			$db->setQuery('SELECT email FROM #__users WHERE block = 0 AND sendEmail = 1');
			$rows = $db->loadObjectList();

			// Send mail to all superadministrators id
			foreach( $rows as $row )
			{
				$return = JFactory::getMailer()->sendMail(
          $config->get('mailfrom'), $config->get('fromname'),
          $row->email, $emailSubject, $emailBodyAdmin );

				// Check for an error.
				if ($return !== true) return false;
			}
    }

		return true;
	}

	/**
	 * Remove all user improved_profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) return false;
		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');
		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();

				$db->setQuery("DELETE FROM #__user_profiles WHERE user_id = $userId AND profile_key LIKE 'improved.%'");
				if (!$db->query()) throw new Exception($db->getErrorMsg());

        $db->setQuery("DELETE FROM #__offlajn_users WHERE user_id = $userId");
        if (!$db->query()) throw new Exception($db->getErrorMsg());
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
