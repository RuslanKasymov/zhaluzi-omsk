<?php
/*------------------------------------------------------------------------
# plg_improved_ajax_login - Improved AJAX Login
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.module.helper' );

$lang = JFactory::getLanguage();
$lang->load('com_users');
$lang->load('mod_login');

class OUserHelper {
  private static function query($select, $from, $where = 1) {
    $db = JFactory::getDBO();
    $query = $db->getQuery(true)->select($select)->from($from)->where($where);
    $db->setQuery($query);
    return $db->loadObject();
  }

  public static function getUser($username) {
    return self::query(
      array('id', 'username', 'password'), '#__users',
      "username = '$username' OR email = '$username'");
  }

  public static function getUserByEmail($email) {
    return self::query(array('id', 'username', 'password'), '#__users', "email = '$email'");
  }

  public static function getId($username) {
    $res = self::query('id', '#__users', "username LIKE '$username'");
    return $res? $res->id : 0;
  }

  public static function getIdByEmail($email) {
    $res = self::query('id', '#__users', "email LIKE '$email'");
    return $res? $res->id : 0;
  }

  public static function getNewId() {
    $res = self::query('MAX(id) AS id', '#__users');
    return $res? $res->id++ : 0;
  }
}

if (@$_GET['task'] == 'registration.activate')
{
  $userParams = JComponentHelper::getParams('com_users');
  if ($userParams->get('useractivation') == '2'
  &&  $userParams->get('mail_to_admin') == 'extended')
  {
    $app = JFactory::getApplication();
    $token = JRequest::getString('token');
    $db = JFactory::getDBO();
    $db->setQuery("SELECT id FROM #__users WHERE block = 1 AND activation = '$token'");
    $userId = (int) $db->loadResult();
    $user = JFactory::getUser($userId);
    // Admin activation is on and user is verifying their email
    if ($userId && !$user->getParam('activate', 0))
    {
      $config	= JFactory::getConfig();
      $lang->load('plg_user_profile', JPATH_ADMINISTRATOR);
      $redirect = JRoute::_('index.php?option=com_users&view=registration&layout=complete', false);
  		// Compile the admin notification mail values.
  		$data = $user->getProperties();
  		$data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
  		$user->set('activation', $data['activation']);
      $user->setParam('activate', 1);
  		$data['sitename'] = $config->get('sitename');
      $data['profile'] = "\n\t".$data['username'];
      // get user profile details
      $db->setQuery("SELECT profile_key, profile_value FROM #__user_profiles WHERE user_id = $userId");
      $rows = $db->loadObjectList();
      if ($rows) foreach ($rows as $row)
      {
        $key = explode('.', $row->profile_key);
        $key[0] = isset($key[1])? $key[1] : $key[0];
        $key[1] = JText::_("PLG_USER_PROFILE_FIELD_{$key[0]}_LABEL");
        $key = ($key[1] == "PLG_USER_PROFILE_FIELD_{$key[0]}_LABEL")? $key[0].':' : $key[1];
        $data['profile'].= "\n $key\n\t".json_decode($row->profile_value);
      }
  		$emailSubject	= JText::sprintf(
  			'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT',
  			$data['name'], $data['sitename']);
  		$emailBody = JText::sprintf(
  			'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY',
  			$data['sitename'], "\n\t".$data['name'], "\n\t".$data['email'], $data['profile'],
  			JUri::base().'index.php?option=com_users&task=registration.activate&token='.$data['activation']);
  		// get all admin users
  		$db->setQuery("SELECT id, email FROM #__users WHERE sendEmail = 1 AND block = 0");
  		$rows = $db->loadObjectList();
  		// Send mail to all users with users creating permissions and receiving system emails
  		foreach( $rows as $row )
  		{
  			$usercreator = JFactory::getUser($row->id);
  			if ($usercreator->authorise('core.create', 'com_users'))
  			{
  				$return = JFactory::getMailer()->sendMail(
            $config->get('mailfrom'), $config->get('fromname'), $row->email,
            $emailSubject, $emailBody);
  				// Check for an error.
  				if ($return !== true)
          {
            $app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'), 'error');
  					$app->redirect($redirect);
            $app->close();
          }
  			}
  		}
  		if ($user->save()) $app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'), 'message');
      else $app->enqueueMessage(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()), 'error');
      $app->redirect($redirect);
      $app->close();
    }
  }
}

jimport('joomla.plugin.plugin');

class plgSystemImproved_Ajax_Login extends JPlugin
{

  function plgSystemImproved_Ajax_Login(&$subject, $config)
  {
    parent::__construct($subject, $config);
    $GLOBALS['username=email'] = $this->params->get('generate', 1) < 1;

    if (isset($_REQUEST['ialCheck'])) {
      $check = JRequest::getString('ialCheck');
      $json = array('error' => '', 'msg' => '');
      switch ($check) {
      case 'ialLogin':
        $json['field'] = 'password';
        if (JSession::checkToken()) {
          $user = JRequest::getVar(isset($_REQUEST['username'])? 'username' : 'email', '');
          $password = JRequest::getString('password', '', 'method', JREQUEST_ALLOWRAW);
        	if (!empty($password)) {
            $result = isset($_REQUEST['username'])?
              OUserHelper::getUser($user) : OUserHelper::getUserByEmail($user);
          	if ($result) {
              $match = 0;
              if (method_exists('JUserHelper', 'verifyPassword')) {
                $match = JUserHelper::verifyPassword($password, $result->password, $result->id);
              } elseif (substr($result->password, 0, 4) == '$2y$') {
                $password60 = substr($result->password, 0, 60);
                if (JCrypt::hasStrongPasswordSupport())
                  $match = password_verify($password, $password60);
              } else {
                $parts = explode(':', $result->password);
                $crypt = $parts[0];
                $salt = @$parts[1];
                $cryptmode = substr($result->password, 0, 8) == '{SHA256}'? 'sha256' : 'md5-hex';
                $testcrypt = JUserHelper::getCryptedPassword($password, $salt, $cryptmode, false);
                $match = $crypt == $testcrypt || $result->password == $testcrypt;
              }
              if ($match) $json['username'] = $result->username;
              else $json['error'] = 'JGLOBAL_AUTH_INVALID_PASS';
          	} else $json['error'] = 'JGLOBAL_AUTH_NO_USER';
          } else $json['error'] = 'JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED';
        } else $json['error'] = 'JINVALID_TOKEN';
        $json['msg'] = JText::_($json['error']);
        die(json_encode($json));
      case 'data[register][username]':
      case 'jform[username]':
      case 'username':
        $username = JRequest::getString('value');
        if (OUserHelper::getId($username)) $json['error'] = 'COM_USERS_REGISTER_USERNAME_MESSAGE';
        $json['msg'] = JText::_($json['error']);
        die(json_encode($json));
      case 'data[register][email]':
      case 'jform[email1]':
      case 'email':
        $email = JRequest::getString('value');
        if (OUserHelper::getIdByEmail($email)) $json['error'] = 'COM_USERS_REGISTER_EMAIL1_MESSAGE';
        $json['msg'] = JText::_($json['error']);
        die(json_encode($json));
      case 'ialRegister':
        // com_users
        if ($jf = JRequest::getVar('jform', null, 'array'))
        {
          if (!JSession::checkToken())
          {
            $json['error'] = 'JINVALID_TOKEN';
            $json['msg'] = JText::_($json['error']);
            die(json_encode($json));
          }
          if (!isset($jf['email1']))
          {
            $json['error'] = 'JGLOBAL_EMAIL';
            $json['msg'] = JText::_('JGLOBAL_EMAIL').' '.JText::_('JREQUIRED');
            die(json_encode($json));
          }
          if (!isset($jf['password1']))
          {
            $json['error'] = 'JGLOBAL_PASSWORD';
            $json['msg'] = JText::_('JGLOBAL_PASSWORD').' '.JText::_('JREQUIRED');
            die(json_encode($json));
          }
          if (!isset($jf['username']))
          {
            if ($this->params->get('generate', 1) > 0)
            {
              list($jf['username']) = explode('@', $jf['email1']);
              if (OUserHelper::getId($jf['username']))
                $jf['username'] .= OUserHelper::getNewId();
            }
            else $jf['username'] = $jf['email1'];
          }
          if (!isset($jf['name'])) $jf['name'] = $jf['username'];
          if (!isset($jf['email2'])) $jf['email2'] = $jf['email1'];
          if (!isset($jf['password2'])) $jf['password2'] = $jf['password1'];
          JRequest::setVar('jform', $jf);
          JFactory::getApplication()->input->post->set('jform', $jf);
        }
        $_SESSION['ialRegister'] = $jf['username'];
        break;
      }
    }
  }

  function onAfterDispatch()
  {
    // Registration checker
    if (isset($_SESSION['ialRegister'])) {
      $msg = JFactory::getApplication()->getMessageQueue();
      $error = isset($msg[0]) && @$msg[0]['type'] != "message";
      $json = array(
        'field' => preg_match('/captcha/i', @$msg[0]['message'])? 'recaptcha_response_field' : 'submit',
        'error' => $error,
        'msg' => @$msg[0]['message']
      );
      if (!$error && $this->params->get('autologin', 1)
      &&  !JComponentHelper::getParams('com_users')->get('useractivation', 1))
      {
        $json['autologin'] = JHTML::_('form.token');
        $json['username'] = $_SESSION['ialRegister'];
      }
      unset($_SESSION['ialRegister']);
      die(json_encode($json));
    }

    if (!$this->params->get('override', 1)) return;

    $app = JFactory::getApplication();
    if ($app->isAdmin()) return;

    $option = JRequest::getCmd('option');
    $view = JRequest::getCmd('view');
    $layout = JRequest::getCmd('layout');

    if ($option == 'com_users' && $view == 'login')
    {
      $module = $this->getModule();
      if (!$module) return;

      $user = JFactory::getUser();
      if (!$user->guest)
      {
          $app->redirect('index.php?option=com_users&view=profile');
          $app->close();
      }

      $module->view = 'log';
      $this->render($module);
    }
    elseif ($option == 'com_users' && $view == 'registration' && $layout != 'complete')
    {
      $module = $this->getModule();
      if (!$module) return;

      $params = json_decode($module->params);
      $regpage = $params->moduleparametersTab->regpage;
      $regpage = explode('|*|', $regpage);
      if (@$regpage[0] != 'joomla' && @$regpage[0] != 'k2') return;

      $module->view = 'reg';
      $this->render($module);
    }
  }

  function getModule()
  {
    $db = JFactory::getDBO();
    $db->setQuery("SELECT * FROM #__modules WHERE module = 'mod_improved_ajax_login' AND published = 1 ORDER BY id DESC");
    $modules = $db->loadObjectList('language');
    $langTag = JFactory::getLanguage()->getTag();
    return isset($modules[$langTag])? $modules[$langTag] : @$modules['*'];
  }

  function render($module)
  {
    $contents = '<div id="loginComp">';
    $contents.= JModuleHelper::renderModule($module);
    $contents.= '</div>';
    $document = JFactory::getDocument();
    $document->setBuffer($contents, 'component');
  }

}