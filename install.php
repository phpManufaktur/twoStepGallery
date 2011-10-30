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

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php')) {
    // default language is DE !!!
    require_once(WB_PATH .'/modules/kit_tools/languages/DE.php');
}
else {
    require_once(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php');
}

// include language file for tsGallery
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
    // default language is DE !!!
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php');
    // this constant tells in which language the addon is executed
    if (!defined('TS_GALLERY_LANGUAGE')) define('TS_GALLERY_LANGUAGE', 'DE');
}
else {
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
    if (!defined('TS_GALLERY_LANGUAGE')) define('TS_GALLERY_LANGUAGE', LANGUAGE);
}

require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_config.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_gallery.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_album.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tsg_picture.php';

global $admin;

$tables = array('dbTSGconfig', 'dbTSGgallery', 'dbTSGalbum', 'dbTSGpicture');
$error = '';

foreach ($tables as $table) {
    $create = null;
    $create = new $table();
    if (!$create->sqlTableExists()) {
        if (!$create->sqlCreateTable()) {
            $error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
        }
    }
}

// Prompt Errors
if (!empty($error)) {
    $admin->print_error($error);
}