<?php

/**
 * twoStepGallery
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

$PRECHECK['PHP_VERSION'] = array(
  'VERSION' => '5.2.0',
  'OPERATOR' => '>='
);
$PRECHECK['WB_ADDONS'] = array(
  'dbconnect_le' => array(
    'VERSION' => '0.65',
    'OPERATOR' => '>='
  ),
  'dwoo' => array(
    'VERSION' => '0.11',
    'OPERATOR' => '>='
  ),
  'kit_tools' => array(
    'VERSION' => '0.15',
    'OPRATOR' => '>='
  ),
  'wblib' => array(
    'VERSION' => '0.76',
    'OPERATOR' => '>='
  ),
  'libraryadmin' => array(
    'VERSION' => '1.9',
    'OPERATOR' => '>='
  )
);

global $database;
$sql = "SELECT `value` FROM `" . TABLE_PREFIX . "settings` WHERE `name`='default_charset'";
if (false === ($query = $database->query($sql)))
  trigger_error($database->get_error(), E_USER_ERROR);
$data = $query->fetchRow(MYSQL_ASSOC);

// jQueryAdmin should be uninstalled
$jqa = (file_exists(WB_PATH.'/modules/jqueryadmin/tool.php')) ? 'INSTALLED' : 'UNINSTALLED';

$PRECHECK['CUSTOM_CHECKS'] = array(
  'Default Charset' => array(
    'REQUIRED' => 'utf-8',
    'ACTUAL' => $data['value'],
    'STATUS' => ($data['value'] === 'utf-8')
    ),
  'jQueryAdmin (replaced by LibraryAdmin)' => array(
      'REQUIRED' => 'UNINSTALLED',
      'ACTUAL' => $jqa,
      'STATUS' => ($jqa === 'UNINSTALLED')
      )
);
