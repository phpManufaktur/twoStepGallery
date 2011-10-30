<?php

/**
 * tsGallery
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 * 
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {    
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php'); 
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) { 
        include($root.'/framework/class.secure.php'); 
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_config.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_gallery.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_album.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_picture.php';
require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');

global $admin;

$tables = array('dbTSGconfig', 'dbTSGgallery', 'dbTSGalbum', 'dbTSGpicture');
$error = '';

foreach ($tables as $table) {
    $delete = null;
    $delete = new $table();
    if ($delete->sqlTableExists()) {
        if (!$delete->sqlDeleteTable()) {
            $error .= sprintf('<p>[UNINSTALL] %s</p>', $delete->getError());
        }
    }
}

// remove Droplets
$dbDroplets = new dbDroplets();
$droplets = array('ts_gallery');
foreach ($droplets as $droplet) {
    $where = array(dbDroplets::field_name => $droplet);
    if (!$dbDroplets->sqlDeleteRecord($where)) {
        $message = sprintf('[UPGRADE] Error uninstalling Droplet: %s', $dbDroplets->getError());
    }
}

// Prompt Errors
if (!empty($error)) {
    $admin->print_error($error);
}
