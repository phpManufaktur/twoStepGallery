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

// for extended error reporting set to true!
if (!defined('KIT_DEBUG')) define('KIT_DEBUG', true);
require_once(WB_PATH.'/modules/kit_tools/debug.php');

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

global $parser;
if (!class_exists('Dwoo')) {
    // search lib_dwoo (LEPTON only)
    if ( is_dir( WB_PATH.'/modules/lib_dwoo' ) ) {
        // as of version 1.2, LEPTON will autocreate the $parser object; this is
        // for backward compatibility
        if (!is_object($parser)) {
            require_once(WB_PATH.'/modules/lib_dwoo/dwoo/dwooAutoload.php');
            $cache_path = WB_PATH.'/temp/cache';
            if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
            $compiled_path = WB_PATH.'/temp/compiled';
            if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
            $parser = new Dwoo($compiled_path,$cache_path);
            set_include_path (
                    implode(
                            PATH_SEPARATOR,
                            array(
                                    realpath(WB_PATH.'/modules/lib_dwoo/dwoo'),
                                    get_include_path(),
                            )
                    )
            );
        }
    }
    // search dwoo module (WB only)
    elseif( is_dir( WB_PATH.'/modules/dwoo' ) ) {
        require_once(WB_PATH.'/modules/dwoo/include.php');
        if (!is_object($parser)) {
            $cache_path = WB_PATH.'/temp/cache';
            if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
            $compiled_path = WB_PATH.'/temp/compiled';
            if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
            $parser = new Dwoo($compiled_path,$cache_path);
            set_include_path (
                    implode(
                            PATH_SEPARATOR,
                            array(
                                    realpath(WB_PATH.'/modules/dwoo'),
                                    get_include_path(),
                            )
                    )
            );
        } 
    }
    else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include Dwoo Template Engine!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
if (!is_object($parser)) {
    $cache_path = WB_PATH.'/temp/cache';
    if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
    $compiled_path = WB_PATH.'/temp/compiled';
    if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
    $parser = new Dwoo($compiled_path,$cache_path);
    set_include_path (
            implode(PATH_SEPARATOR,
                    array(
                            realpath(WB_PATH.'/modules/dwoo'),
                            get_include_path(),
                    ))
            );
} 

// check for dbConnect_LE
if (!class_exists('dbconnectle')) {
    require_once WB_PATH.'/modules/dbconnect_le/include.php';
}

// check for kitTools
if (!class_exists('kitToolsLibrary')) {
    require_once WB_PATH.'/modules/kit_tools/class.tools.php';
}
global $kitTools;
if (!is_object($kitTools)) $kitTools = new kitToolsLibrary();


// load database classes for tsGallery
require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.tsg_config.php');
require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.tsg_gallery.php');
require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.tsg_album.php');
require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.tsg_picture.php');

global $dbTSGconfig;
if (!is_object($dbTSGconfig)) $dbTSGconfig = new dbTSGconfig();

global $dbTSGgallery;
if (!is_object($dbTSGgallery)) $dbTSGgallery = new dbTSGgallery();

global $dbTSGalbum;
if (!is_object($dbTSGalbum)) $dbTSGalbum = new dbTSGalbum();

global $dbTSGpicture;
if (!is_object($dbTSGpicture)) $dbTSGpicture = new dbTSGpicture();

require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.media_browser.php');
