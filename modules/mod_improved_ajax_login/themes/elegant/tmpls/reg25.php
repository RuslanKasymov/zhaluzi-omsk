<?php
/*-------------------------------------------------------------------------
# mod_improved_ajax_login - Improved AJAX Login and Register
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php defined('_JEXEC') or die('Restricted access'); ?>

<?php if (count($modules = JModuleHelper::getModules($params->get('reg_top', 'reg-top')))): // REG-TOP MODULEPOS ?>
  <?php foreach ($modules as $m): ?>
    <?php echo JModuleHelper::renderModule($m) ?>
  <?php endforeach ?>
  <div class="loginBrd"></div>
<?php endif ?>

<?php if (@$oauth_list && $socialpos=='top') require dirname(__FILE__).'/social.php' // TOP SOCIALPOS ?>

<?php
$registration = 'registration';
if ($regp[0] != 'joomla') $registration.= '/'.$regp[0];

switch ($regp[0]) {
  case 'joomla': $lang->load('plg_user_profile', JPATH_ADMINISTRATOR); break;
  case 'virtuemart': $lang->load('com_virtuemart'); break;
}

$db = JFactory::getDBo();
$db->setQuery(defined('DEMO')?
  "SELECT fields, props FROM #__offlajn_forms WHERE id = {$params->get('regform', 1)}":
  "SELECT fields, props FROM #__offlajn_forms WHERE state=1 AND type='$registration'");
$res = $db->loadObject();
$fields = json_decode($res->fields);
foreach ($fields->page[0]->elem as $elem) {
  foreach ($elem as $name => $prop) {
    if ($name == 'jform[elem_name]') $prop->value = $prop->value? $prop->value : $prop->placeholder;
    if ($name == 'jform[elem_type]') $prop->value = $prop->value? $prop->value : $prop->placeholder;    
    if (!isset($prop->value) || $name == 'jform[elem_pattern]' || $name == 'jform[elem_name]' || $name == 'jform[elem_type]') continue;
    if (!$prop->value) $prop->value = @$prop->defaultValue? @JText::sprintf($prop->defaultValue, '') : @$prop->placeholder;
    else $prop->value = JText::_($prop->value);
    unset($prop->placeholder);
  }
}
?>

<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')) ?>" method="post" name="ialRegister" class="ial-form">
  <input type="hidden" value="<?php echo htmlspecialchars(json_encode($fields), ENT_COMPAT, 'UTF-8') ?>" name="fields" />
  <input type="hidden" value="<?php echo htmlspecialchars($res->props, ENT_COMPAT, 'UTF-8') ?>" name="props" />
<?php if ($regp[0] == 'joomla' || $regp[0] == 'jomsocial'): ?>
  <input type="hidden" value="com_users" name="option" />
  <input type="hidden" value="registration.register" name="task" />
  <?php echo JHTML::_('form.token') ?>
<?php elseif ($regp[0] == 'k2'): ?>
  <input type="hidden" value="com_users" name="option" />
  <input type="hidden" value="registration.register" name="task" />
  <input type="hidden" value="0" name="id" />
  <input type="hidden" value="0" name="gid" />
  <input type="hidden" value="1" name="K2UserForm">
  <?php echo JHTML::_('form.token') ?>
<?php elseif ($regp[0] == 'hikashop'): ?>
  <input type="hidden" value="com_hikashop" name="option" />
  <input type="hidden" value="user" name="ctrl" />
  <input type="hidden" value="register" name="task" />
  <input type="hidden" value="0" name="data[register][id]" />
  <input type="hidden" value="0" name="data[register][gid]" />
  <input type="hidden" value="1" name="acylistsdisplayed_dispall" />
<?php elseif ($regp[0] == 'virtuemart'): ?>
  <input type="hidden" value="com_virtuemart" name="option" />
  <input type="hidden" value="user" name="controller" />
  <input type="hidden" value="saveUser" name="task" />
  <input type="hidden" value="BT" name="address_type" />
  <?php echo JHTML::_('form.token'); ?>
<?php endif; ?>
</form>

<br style="clear:both" />
<?php if (@$oauth_list && $socialpos=='bottom') require dirname(__FILE__).'/social.php' // BOTTOM SOCIALPOS ?>

<?php if (count($modules = JModuleHelper::getModules($params->get('reg_bottom', 'reg-bottom')))): // REG-BOTTOM MODULEPOS ?>
  <div class="loginBrd"></div>
  <?php foreach ($modules as $m): ?>
    <?php echo JModuleHelper::renderModule($m) ?>
  <?php endforeach ?>
<?php endif ?>
