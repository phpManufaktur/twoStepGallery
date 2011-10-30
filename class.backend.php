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
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", 
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

// load the required libraries
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';


class tsGallery {
    
    const REQUEST_ACTION			= 'act';
    const REQUEST_ITEMS				= 'its';
    const REQUEST_GALLERY_SELECT    = 'gls';
    const REQUEST_NEXT_ACTION       = 'nac';
    const REQUEST_ALBUM_DELETE      = 'albd';
    const REQUEST_GALLERY_DELETE    = 'gld';
    const REQUEST_PICTURE_DELETE    = 'picd';
    
    const ACTION_ABOUT				= 'abt';
    const ACTION_CONFIG				= 'cfg';
    const ACTION_CONFIG_CHECK	    = 'cfgc';
    const ACTION_DEFAULT			= 'def';
    const ACTION_SHOW_MEDIA_DIR     = 'smd';
    const ACTION_GALLERY            = 'gl';
    const ACTION_GALLERY_CHECK      = 'glc';
    const ACTION_ALBUM_CREATE       = 'alb';
    const ACTION_ALBUM_CHECK        = 'albc';
    const ACTION_ALBUM_IMG_CHANGE   = 'albic';
    const ACTION_PICTURE_ADD        = 'pica';
    const ACTION_PICTURE_CHECK      = 'picc';
    
    const SESSION_PARAMS            = 'tsg_params';
    
    private $tab_navigation_array = array(
            self::ACTION_GALLERY    => TSG_TAB_GALLERY,
            self::ACTION_CONFIG		=> TSG_TAB_CONFIG,
            self::ACTION_ABOUT		=> TSG_TAB_ABOUT
            );
    
    private $page_link 				= '';
    private $img_url				= '';
    private $template_path			= '';
    private $error					= '';
    private $message				= '';
    private $media_path				= '';
    private $media_url				= '';
    private $tsg_media_path         = '';
    private $tsg_media_url          = '';
    
    /**
     * Constructor for tsGallery
     */
    public function __construct() {
        global $dbTSGconfig;
        $this->setPageLink(ADMIN_URL.'/admintools/tool.php?tool=ts_gallery');
        $this->setTemplatePath(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/') ;
        $this->setImgURL(WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/');
        date_default_timezone_set(TSG_CFG_TIME_ZONE);
        $this->setTSGmediaPath(WB_PATH.MEDIA_DIRECTORY.'/'.$dbTSGconfig->getValue(dbTSGconfig::CFG_MEDIA_DIR).'/');
        $this->setTSGmediaURL(str_replace(WB_PATH, WB_URL, $this->getMediaPath()));
        $this->setMediaPath(WB_PATH.MEDIA_DIRECTORY);
        $this->setMediaURL(WB_URL.MEDIA_DIRECTORY);
    } // __construct()
    
    /**
     * @return the $tsg_media_path
     */
    protected function getTSGmediaPath ()
    {
        return $this->tsg_media_path;
    }

	/**
     * @return the $tsg_media_url
     */
    protected function getTSGmediaURL ()
    {
        return $this->tsg_media_url;
    }

	/**
     * @param string $tsg_media_path
     */
    protected function setTSGmediaPath ($tsg_media_path)
    {
        $this->tsg_media_path = $tsg_media_path;
    }

	/**
     * @param string $tsg_media_url
     */
    protected function setTSGmediaURL ($tsg_media_url)
    {
        $this->tsg_media_url = $tsg_media_url;
    }

	/**
     * @return the $page_link
     */
    protected function getPageLink ()
    {
        return $this->page_link;
    }

	/**
     * @return the $img_url
     */
    protected function getImgURL ()
    {
        return $this->img_url;
    }

	/**
     * @return the $template_path
     */
    protected function getTemplatePath ()
    {
        return $this->template_path;
    }

	/**
     * @return the $media_path
     */
    protected function getMediaPath ()
    {
        return $this->media_path;
    }

	/**
     * @return the $media_url
     */
    protected function getMediaURL ()
    {
        return $this->media_url;
    }

	/**
     * @param string $page_link
     */
    protected function setPageLink ($page_link)
    {
        $this->page_link = $page_link;
    }

	/**
     * @param string $img_url
     */
    protected function setImgUrl ($img_url)
    {
        $this->img_url = $img_url;
    }

	/**
     * @param string $template_path
     */
    protected function setTemplatePath ($template_path)
    {
        $this->template_path = $template_path;
    }

	/**
     * @param string $media_path
     */
    protected function setMediaPath ($media_path)
    {
        $this->media_path = $media_path;
    }

	/**
     * @param string $media_url
     */
    protected function setMediaUrl ($media_url)
    {
        $this->media_url = $media_url;
    }

	/**
     * Set $this->error to $error
     *
     * @param string $error
     */
    protected function setError($error) {
        $this->error = $error;
    } // setError()
    
    /**
     * Get Error from $this->error;
     *
     * @return STR $this->error
     */
    public function getError() {
        return $this->error;
    } // getError()
    
    /**
     * Check if $this->error is empty
     *
     * @return boolean
     */
    public function isError() {
        return (bool) !empty($this->error);
    } // isError
    
    /**
     * Reset Error to empty String
     */
    protected function clearError() {
        $this->error = '';
    }
    
    /** Set $this->message to $message
     *
     * @param string $message
     */
    public function setMessage($message) {
        $this->message = $message;
    } // setMessage()
    
    /**
     * Get Message from $this->message;
     *
     * @return string $this->message
     */
    public function getMessage() {
        return $this->message;
    } // getMessage()
    
    /**
     * Check if $this->message is empty
     *
     * @return boolean
     */
    public function isMessage() {
        return (bool) !empty($this->message);
    } // isMessage
    
    /**
     * Return Version of Module
     *
     * @return float
     */
    public function getVersion() {
        // read info.php into array
        $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
        if ($info_text == false) {
            return -1;
        }
        // walk through array
        foreach ($info_text as $item) {
            if (strpos($item, '$module_version') !== false) {
                // split string $module_version
                $value = explode('=', $item);
                // return floatval
                return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
            }
        }
        return -1;
    } // getVersion()
    
    /**
     * Parse the desired $template with $template_data and returns the resulting
     * output of the template engine
     * 
     * @param string $template - only template name
     * @param array $template_data
     * @return string template output
     */
    public function getTemplate($template, $template_data) {
        global $parser;
        try {
            $result = $parser->get($this->getTemplatePath().$template, $template_data);
        } catch (Exception $e) {
            $this->setError(sprintf(TSG_ERROR_TEMPLATE_ERROR, $template, $e->getMessage()));
            return false;
        }
        return $result;
    } // getTemplate()
     
    /**
     * Prevent XSS Cross Site Scripting
     *
     * @param mixed reference $request
     * @return mixed $request
     */
    public function xssPrevent(&$request) {
        if (is_string($request)) {
            $request = html_entity_decode($request);
            $request = strip_tags($request);
            $request = trim($request);
            $request = stripslashes($request);
        }
        return $request;
    } // xssPrevent()
    
    /**
     * Action handler of the class
     *
     * @return STR result dialog or message
     */
    public function action() {
        $html_allowed = array();
        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, 'cfg_') == 0) continue; // ignore config values!
            if (!in_array($key, $html_allowed)) {
                $_REQUEST[$key] = $this->xssPrevent($value);
            }
        }
        $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;
    
        if (isset($_FILES[mediaBrowser::REQUEST_UPLOAD])) {
            // special: file upload!
            $action = mediaBrowser::ACTION_MEDIA_UPLOAD;
        }
        if (isset($_REQUEST[mediaBrowser::REQUEST_MKDIR]) && !empty($_REQUEST[mediaBrowser::REQUEST_MKDIR])) {
            // special: make directory
            $action = mediaBrowser::ACTION_MEDIA_MKDIR;
        }
            
        switch ($action):  
        case self::ACTION_PICTURE_ADD:
        case self::ACTION_ALBUM_IMG_CHANGE:
        case self::ACTION_ALBUM_CREATE:
            $this->show(self::ACTION_GALLERY, $this->selectAlbumPicture());
            break;
        case self::ACTION_GALLERY:
            $this->show(self::ACTION_GALLERY, $this->dlgGallery());
            break;
        case self::ACTION_GALLERY_CHECK:
            $this->show(self::ACTION_GALLERY, $this->checkGallery());
            break;
        case self::ACTION_CONFIG:
            $this->show(self::ACTION_CONFIG, $this->dlgConfig());
            break;
        case self::ACTION_CONFIG_CHECK:
            $this->show(self::ACTION_CONFIG, $this->checkConfig());
            break;
        case self::ACTION_ABOUT:
            $this->show(self::ACTION_ABOUT, $this->dlgAbout());
            break;
        case mediaBrowser::ACTION_MEDIA_FILE_SELECT:
            // select one or more files
            $this->show(self::ACTION_GALLERY, $this->selectFiles());
            break;
        case mediaBrowser::ACTION_MEDIA_MKDIR:
            // create a directory
            $this->show(self::ACTION_GALLERY, $this->createDirectory());
            break;
        case mediaBrowser::ACTION_MEDIA_UPLOAD:
            // file upload to process
            $this->show(self::ACTION_GALLERY, $this->processFileUpload());
            break;
        case mediaBrowser::ACTION_MEDIA_FILE_DELETE:
            // a MEDIA file should be deleted, user must confirm action
            $this->show(self::ACTION_GALLERY, $this->confirmDeleteFile());
            break;
        case mediaBrowser::ACTION_MEDIA_FILE_DELETE_EXEC:
            // delete MEDIA File
            $this->show(self::ACTION_GALLERY, $this->execDeleteFile());
            break;
        case mediaBrowser::ACTION_MEDIA_RMDIR:
            // MEDIA directory should be deleted, user must first confirm action
            $this->show(self::ACTION_GALLERY, $this->confirmRemoveDirectory());
            break;
        case mediaBrowser::ACTION_MEDIA_RMDIR_EXEC:
            // delete MEDIA directory ...
            $this->show(self::ACTION_GALLERY, $this->execRemoveDirectory());
            break;
        case mediaBrowser::ACTION_MEDIA_CHDIR:
        case mediaBrowser::ACTION_MEDIA_BROWSER:
        case self::ACTION_SHOW_MEDIA_DIR:
            $this->show(self::ACTION_GALLERY, $this->dlgMediaDir());
            break;
        default:
            $this->show(self::ACTION_GALLERY, $this->dlgGallery());
            break;
        endswitch;
    } // action    
    
    /**
     * Preformat the output, adds a navigation bar, prompts error a.s.o
     *
     * @param string $action - active navigation tab
     * @param string $content - the content
     *
     * @return prompt parsed content
     */
    public function show($action, $content) {
        $navigation = array();
        foreach ($this->tab_navigation_array as $key => $value) {
            $navigation[] = array(
                    'active' 	=> ($key == $action) ? 1 : 0,
                    'url'		=> sprintf('%s&%s', $this->getPageLink(), http_build_query(array(self::REQUEST_ACTION => $key))),
                    'text'		=> $value
            );
        }
        $data = array(
                'WB_URL'		=> WB_URL,
                'navigation'	=> $navigation,
                'error'			=> ($this->isError()) ? 1 : 0,
                'content'		=> ($this->isError()) ? $this->getError() : $content
        );
        echo $this->getTemplate('backend.body.lte', $data);
    } // show()
    
    /**
     * Information about tsGallery
     *
     * @return string parsed about dialog
     */
    public function dlgAbout() {
        $data = array(
                'version'		=> sprintf('%01.2f', $this->getVersion()),
                'img_url'		=> $this->img_url,
                'release_notes'	=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
        );
        return $this->getTemplate('backend.about.lte', $data);
    } // dlgAbout()
    
    /**
     * General configuration dialog for tsGallery
     * 
     * @return string parsed config dialog
     */
    public function dlgConfig() {
        global $dbTSGconfig;
    
        $SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
                $dbTSGconfig->getTableName(),
                dbTSGconfig::FIELD_STATUS,
                dbTSGconfig::STATUS_DELETED,
                dbTSGconfig::FIELD_NAME);
        $config = array();
        if (!$dbTSGconfig->sqlExec($SQL, $config)) {
            $this->setError($dbTSGconfig->getError());
            return false;
        }
        $count = array();
        $header = array(
                'identifier'	=> tool_header_cfg_identifier,
                'value'			=> tool_header_cfg_value,
                'description'	=> tool_header_cfg_description
        );
    
        $items = array();
        // list existing entries
        foreach ($config as $entry) {
            $id = $entry[dbTSGconfig::FIELD_ID];
            $count[] = $id;
            $value = ($entry[dbTSGconfig::FIELD_TYPE] == dbTSGconfig::TYPE_LIST) ? $dbTSGconfig->getValue($entry[dbTSGconfig::FIELD_NAME]) : $entry[dbTSGconfig::FIELD_VALUE];
            if (isset($_REQUEST[dbTSGconfig::FIELD_VALUE.'_'.$id])) $value = $_REQUEST[dbTSGconfig::FIELD_VALUE.'_'.$id];
            $value = str_replace('"', '&quot;', stripslashes($value));
            $items[] = array(
                    'id'			=> $id,
                    'identifier'	=> constant($entry[dbTSGconfig::FIELD_LABEL]),
                    'value'			=> $value,
                    'name'			=> sprintf('%s_%s', dbTSGconfig::FIELD_VALUE, $id),
                    'description'	=> constant($entry[dbTSGconfig::FIELD_DESCRIPTION]),
                    'type'			=> $dbTSGconfig->type_array[$entry[dbTSGconfig::FIELD_TYPE]],
                    'field'			=> $entry[dbTSGconfig::FIELD_NAME]
            );
        }
        $data = array(
                'form_name'			=> 'flex_table_cfg',
                'form_action'		=> $this->getPageLink(),
                'action_name'	    => self::REQUEST_ACTION,
                'action_value'		=> self::ACTION_CONFIG_CHECK,
                'items_name'		=> self::REQUEST_ITEMS,
                'items_value'		=> implode(",", $count),
                'head'				=> tool_header_cfg,
                'intro'				=> $this->isMessage() ? $this->getMessage() : sprintf(tool_intro_cfg, 'tsGallery'),
                'is_message'		=> $this->isMessage() ? 1 : 0,
                'items'				=> $items,
                'btn_ok'			=> tool_btn_ok,
                'btn_abort'			=> tool_btn_abort,
                'abort_location'	=> $this->getPageLink(),
                'header'		    => $header
        );
        return $this->getTemplate('backend.config.lte', $data);
    } // dlgConfig()
    
    /**
     * checks all changes in the configuration and update the desired records
     * if necessary, sets messages and return the config dialog
     *
     * @return string parsed config dialog
     */
    public function checkConfig() {
        global $dbTSGconfig;
        $message = '';
        // check if a setting is changed
        if ((isset($_REQUEST[self::REQUEST_ITEMS])) && (!empty($_REQUEST[self::REQUEST_ITEMS]))) {
            $ids = explode(",", $_REQUEST[self::REQUEST_ITEMS]);
            foreach ($ids as $id) {
                if (isset($_REQUEST[dbTSGconfig::FIELD_VALUE.'_'.$id])) {
                    $value = $_REQUEST[dbTSGconfig::FIELD_VALUE.'_'.$id];
                    $where = array();
                    $where[dbTSGconfig::FIELD_ID] = $id;
                    $config = array();
                    if (!$dbTSGconfig->sqlSelectRecord($where, $config)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGconfig->getError()));
                        return false;
                    }
                    if (sizeof($config) < 1) {
                        $this->setError(sprintf(tool_error_cfg_id, $id));
                        return false;
                    }
                    $config = $config[0];
                    if ($config[dbTSGconfig::FIELD_VALUE] != $value) {
                        // Wert wurde geaendert
                        if (!$dbTSGconfig->setValue($value, $id) && $dbTSGconfig->isError()) {
                            $this->setError($dbTSGconfig->getError());
                            return false;
                        }
                        elseif ($dbTSGconfig->isMessage()) {
                            $message .= $dbTSGconfig->getMessage();
                        }
                        else {
                            // Datensatz wurde aktualisiert
                            $message .= sprintf(tool_msg_cfg_id_updated, $config[dbTSGconfig::FIELD_NAME]);
                        }
                    }
                    unset($_REQUEST[dbTSGconfig::FIELD_VALUE.'_'.$id]);
                }
            }
        }
        $this->setMessage($message);
        return $this->dlgConfig();
    } // checkConfig()
    
    /**
     * Call a confirmation dialog before removing a complete directory
     * 
     * @return string|boolean - confirm dialog or false on error
     */
    protected function confirmRemoveDirectory() {
        // set the directory to remove
        $remove_directory = isset($_REQUEST[mediaBrowser::REQUEST_DIRECTORY]) ? $_REQUEST[mediaBrowser::REQUEST_DIRECTORY] : '';
        $mediaBrowser = new mediaBrowser();
        if (false === ($result = $mediaBrowser->confirmRemoveDirectory($remove_directory))) $this->setError($mediaBrowser->getError());
        return $result;
    } // confirmRemoveDirectory()
    
    /**
     * Removes a directory
     * 
     * @return string|boolen - mediaBrowser on success or false on error
     */
    protected function execRemoveDirectory() {
        global $dbTSGconfig;
        // first get the allowed file extensions for the media browser
        $exts = $dbTSGconfig->getValue(dbTSGconfig::CFG_IMAGE_EXTENSIONS);
        $remove_directory = isset($_REQUEST[mediaBrowser::REQUEST_DIRECTORY]) ? $_REQUEST[mediaBrowser::REQUEST_DIRECTORY] : '';
        $mediaBrowser = new mediaBrowser();
        if (!$mediaBrowser->execRemoveDirectory($remove_directory)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $mediaBrowser->getError()));
            return false;
        }
        $this->setMessage(sprintf(TSG_MSG_RMDIR_SUCCESS, $remove_directory));
        $directory = substr($remove_directory, 0, strrpos($remove_directory, DIRECTORY_SEPARATOR));
        if (false === ($result = $mediaBrowser->dlgMediaDirectory($directory))) $this->setError($mediaBrowser->getError()); 
        return $result;
    }
    
    /**
     * Call a confirmation dialog before deleting a file
     * 
     * @return string|boolean - confirm dialog or false on error
     */
    protected function confirmDeleteFile() {
        // set the file to delete
        $delete_file = isset($_REQUEST[mediaBrowser::REQUEST_FILE]) ? $_REQUEST[mediaBrowser::REQUEST_FILE] : '';
        $mediaBrowser = new mediaBrowser();
        if (false === ($result = $mediaBrowser->confirmDeleteFile($delete_file))) $this->setError($mediaBrowser->getError());
        return $result;
    } // confirmDeleteFile()
    
    /**
     * Delete a file and then show the media browser again
     * 
     * @return string|boolean - media browser on success or false on error
     */
    protected function execDeleteFile() {
        $delete_file = isset($_REQUEST[mediaBrowser::REQUEST_FILE]) ? $_REQUEST[mediaBrowser::REQUEST_FILE] : '';
        $mediaBrowser = new mediaBrowser();
        if (!$mediaBrowser->execDeleteFile($this->getMediaPath().$delete_file)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $mediaBrowser->getError()));
            return false;
        }
        $this->setMessage(sprintf(TSG_MSG_FILE_DELETE_SUCCESS, $delete_file));
        $directory = substr($delete_file, 0, strrpos($delete_file, DIRECTORY_SEPARATOR));
        if (false === ($result = $mediaBrowser->dlgMediaDirectory($directory))) $this->setError($mediaBrowser->getError());
        return $result;
    } // execDeleteFile()
    
    /**
     * Process a file upload to the server
     * 
     * @return boolean|Ambigous <boolean, Ambigous, string, mixed>
     */
    protected function processFileUpload() {
        $mediaBrowser = new mediaBrowser();
        if (!$mediaBrowser->processFileUpload() && $mediaBrowser->isError()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $mediaBrowser->getError()));
            return false;
        }
        $this->setMessage($mediaBrowser->getMessage());
        // check if MEDIA subdirectory is set, otherwise set MEDIA root...
        $directory = isset($_REQUEST[mediaBrowser::REQUEST_DIRECTORY]) ? $_REQUEST[mediaBrowser::REQUEST_DIRECTORY] : '';
        if (false === ($result = $mediaBrowser->dlgMediaDirectory($directory))) $this->setError($mediaBrowser->getError());
        return $result;
    } // processFileUpload()
    
    /**
     * Create a directory in /MEDIA folder
     * 
     * @return string|boolean - mediaBrowser on success, false on error
     */
    protected function createDirectory() {
        $mediaBrowser = new mediaBrowser();
        if (!$mediaBrowser->createDirectory() && $mediaBrowser->isError()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $mediaBrowser->getError()));
            return false;
        }
        $this->setMessage($mediaBrowser->getMessage());
        // check if MEDIA subdirectory is set, otherwise set MEDIA root...
        $directory = isset($_REQUEST[mediaBrowser::REQUEST_DIRECTORY]) ? $_REQUEST[mediaBrowser::REQUEST_DIRECTORY] : '';
        if (false === ($result = $mediaBrowser->dlgMediaDirectory($directory))) $this->setError($mediaBrowser->getError());
        return $result;
    } // createDirectory()
    
    /**
     * Shows a Media Filemanager
     * 
     * @return string mediaFilemanager
     */
    protected function dlgMediaDir() {
        // check if MEDIA subdirectory is set, otherwise set MEDIA root...
        $directory = isset($_REQUEST[mediaBrowser::REQUEST_DIRECTORY]) ? $_REQUEST[mediaBrowser::REQUEST_DIRECTORY] : '';
        // init mediaBrowser
        $mediaBrowser = new mediaBrowser();
        if (false === ($result = $mediaBrowser->dlgMediaDirectory($directory))) $this->setError($mediaBrowser->getError()); 
        return $result;
    } // dlgMediaDir()
    
    /**
     * Select media files
     * 
     * @return string|boolean - mediaFilemanager or false on error
     */
    protected function selectFiles() {
        if (isset($_SESSION[self::SESSION_PARAMS])) {
            $params = unserialize($_SESSION[self::SESSION_PARAMS]);
            foreach ($params as $key => $value) $_REQUEST[$key] = $value;
        }
        if (!isset($_REQUEST[self::REQUEST_NEXT_ACTION])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MISSING_PARAMS, 'REQUEST_NEXT_ACTION')));
            return false;
        }
        switch ($_REQUEST[self::REQUEST_NEXT_ACTION]):
        case self::ACTION_PICTURE_CHECK:
        case self::ACTION_ALBUM_CHECK:
            return $this->checkAlbumPicture();
        default:
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, TSG_ERROR_UNDEFINED_ERROR));
            return false;
        endswitch;
    } // selectFiles()
    
    /**
     * Dialog for creating and editing the galleries
     * 
     * @return string dlgGallery
     */
    protected function dlgGallery() {
        global $dbTSGgallery;
        global $dbTSGalbum;
        global $dbTSGpicture;
        global $dbTSGconfig;
        
        $gallery_id = isset($_REQUEST[dbTSGgallery::FIELD_ID]) ? $_REQUEST[dbTSGgallery::FIELD_ID] : -1;
        
        if ($gallery_id > 0) {
            // load gallery
            $where = array(dbTSGgallery::FIELD_ID => $gallery_id);
            $gallery = array();
            if (!$dbTSGgallery->sqlSelectRecord($where, $gallery)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
                return false;
            }
            if (count($gallery) < 1) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $gallery_id)));
                return false;
            }
            $gallery = $gallery[0];
            // load album
            $where = array(dbTSGalbum::FIELD_GALLERY_ID => $gallery_id);
            $albums = array();
            if (!$dbTSGalbum->sqlSelectRecord($where, $albums)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                return false;
            }
        }
        else {
            // set defaults for a gallery
            $gallery = $dbTSGgallery->getFields();
            $gallery[dbTSGgallery::FIELD_ID] = -1;
            // set defaults for the album and the pictures
            $albums = array();
        }
        
        $galleries = array();
        $where = array();
        if (!$dbTSGgallery->sqlSelectRecord($where, $galleries)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
            return false;
        }
        $galleries_array = array();
        $galleries_array[] = array(
                'value' => -1,
                'text' => TSG_STR_PLEASE_SELECT_GALLERY,
                'selected' => ($gallery_id == -1) ? 1 : 0
                );
        foreach ($galleries as $gal) {
            $galleries_array[] = array(
                    'value' => $gal[dbTSGgallery::FIELD_ID],
                    'text' => $gal[dbTSGgallery::FIELD_NAME],
                    'selected' => ($gal[dbTSGgallery::FIELD_ID] == $gallery_id) ? 1 : 0
                    );
        }
        
        $icon_width = $dbTSGconfig->getValue(dbTSGconfig::CFG_MB_IMG_ICON_WIDTH);
        $preview_width = $dbTSGconfig->getValue(dbTSGconfig::CFG_MB_IMG_PREVIEW_WIDTH);
        // init mediaBrowser - needed for image optimizing
        $mediaBrowser = new mediaBrowser();
        
        $gallery_array = array(
                'id' => array(
                        'name' => dbTSGgallery::FIELD_ID,
                        'value' => $gallery[dbTSGgallery::FIELD_ID],
                        'label' => constant(sprintf('TSG_LABEL_%s', strtoupper(dbTSGgallery::FIELD_ID))),
                        'hint' => constant(sprintf('TSG_HINT_%s', strtoupper(dbTSGgallery::FIELD_ID)))                                
                        ),
                'name' => array(
                        'name' => dbTSGgallery::FIELD_NAME,
                        'value' => $gallery[dbTSGgallery::FIELD_NAME],
                        'label' => constant(sprintf('TSG_LABEL_%s', strtoupper(dbTSGgallery::FIELD_NAME))),
                        'hint' => constant(sprintf('TSG_HINT_%s', strtoupper(dbTSGgallery::FIELD_NAME)))
                        ),
                'description' => array(
                        'name' => dbTSGgallery::FIELD_DESCRIPTION,
                        'value' => $gallery[dbTSGgallery::FIELD_DESCRIPTION],
                        'label' => constant(sprintf('TSG_LABEL_%s', strtoupper(dbTSGgallery::FIELD_DESCRIPTION))),
                        'hint' => constant(sprintf('TSG_HINT_%s', strtoupper(dbTSGgallery::FIELD_DESCRIPTION)))
                        ),
                'select' => array(
                        'name' => self::REQUEST_GALLERY_SELECT,
                        'options' => $galleries_array,
                        'label' => TSG_LABEL_GALLERY_SELECT,
                        'hint' => TSG_HINT_GALLERY_SELECT,
                        'onchange' => sprintf(
                                'javascript:execOnChange(\'%s\',\'%s\');',
								sprintf(
								        '%s&amp;%s=%s%s&amp;%s=',
									    $this->getPageLink(),
										self::REQUEST_ACTION,
										self::ACTION_GALLERY,
										(defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '',
										dbTSGgallery::FIELD_ID),
                                self::REQUEST_GALLERY_SELECT
								)
                        ),
                'delete' => array(
                        'name' => self::REQUEST_GALLERY_DELETE,
                        'value' => $gallery_id,
                        'label' => TSG_LABEL_GALLERY_DELETE,
                        'hint' => TSG_HINT_GALLERY_DELETE
                        )                
                );
        
        $albums_array = array();
        // step throught the albums and load the pictures
        foreach ($albums as $album) {
            // load associated pictures
            $where = array(dbTSGpicture::FIELD_ALBUM_ID => $album[dbTSGalbum::FIELD_ID]);
            $pictures = array();
            if (!$dbTSGpicture->sqlSelectRecord($where, $pictures)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                return false;
            }
            $pictures_array = array();
            foreach ($pictures as $picture) {
                if (file_exists(WB_PATH . mediaBrowser::MB_TEMP_DIR . '/icon' . substr($picture[dbTSGpicture::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY)))) {
                    $icon_url = WB_URL . mediaBrowser::MB_TEMP_DIR . '/icon' . substr($picture[dbTSGpicture::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY));
                }
                else {
                    // file does not exist!
                }
                if (file_exists(WB_PATH . mediaBrowser::MB_TEMP_DIR . '/preview' . substr($picture[dbTSGpicture::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY)))) {
                    $preview_url = WB_URL . mediaBrowser::MB_TEMP_DIR . '/preview' . substr($picture[dbTSGpicture::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY));
                }
                else {
                    // file does not exist!
                }
                
                $pictures_array[$picture[dbTSGpicture::FIELD_ID]] = array(
                        'id' => $picture[dbTSGpicture::FIELD_ID],
                        'album_id' => $picture[dbTSGpicture::FIELD_ALBUM_ID],
                        'gallery_id' => $picture[dbTSGpicture::FIELD_GALLERY_ID], 
                        'name' => sprintf('%s_%s', dbTSGpicture::FIELD_IMAGE_TITLE, $picture[dbTSGpicture::FIELD_ID]),
                        'title' => $picture[dbTSGpicture::FIELD_IMAGE_TITLE],
                        'image' => array(
                                'url' => $picture[dbTSGpicture::FIELD_IMAGE_URL],
                                'title' => $picture[dbTSGpicture::FIELD_IMAGE_TITLE],
                                'icon' => array(
                                        'url' => $icon_url,
                                        'width' => $icon_width
                                        ),
                                'preview' => array(
                                        'url' => $preview_url,
                                        'width' => $preview_width
                                        )
                                ),
                        'delete' => array(
                                'label' => TSG_LABEL_PICTURE_DELETE,
                                'name' => self::REQUEST_PICTURE_DELETE,
                                'value' => $picture[dbTSGpicture::FIELD_ID]
                                )
                        );
            }
            
            if (file_exists(WB_PATH . mediaBrowser::MB_TEMP_DIR . '/icon' . substr($album[dbTSGalbum::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY)))) {
                $icon_url = WB_URL . mediaBrowser::MB_TEMP_DIR . '/icon' . substr($album[dbTSGalbum::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY));
            }
            else {
               // file does not exist! 
            }            
            if (file_exists(WB_PATH . mediaBrowser::MB_TEMP_DIR . '/preview' . substr($album[dbTSGalbum::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY)))) {
                $preview_url = WB_URL . mediaBrowser::MB_TEMP_DIR . '/preview' . substr($album[dbTSGalbum::FIELD_IMAGE_URL], strlen(WB_URL.MEDIA_DIRECTORY));
            }
            else {
                // file does not exist!
            }
            
            $albums_array[$album[dbTSGalbum::FIELD_ID]] = array(
                    'id' => $album[dbTSGalbum::FIELD_ID],
                    'gallery_id' => $album[dbTSGalbum::FIELD_GALLERY_ID],
                    'name' => dbTSGalbum::FIELD_ID,
                    'image' => array(
                            'url' => $album[dbTSGalbum::FIELD_IMAGE_URL],
                            'title' => $album[dbTSGalbum::FIELD_IMAGE_TITLE],
                            'icon' => array(
                                    'url' => $icon_url,
                                    'width' => $icon_width
                                    ),
                            'preview' => array(
                                    'url' => $preview_url,
                                    'width' => $preview_width
                                    ),
                            ),
                    'album' => array(
                            'title' => array(
                                    'label' => TSG_LABEL_ALBUM_TITLE,
                                    'value' => $album[dbTSGalbum::FIELD_ALBUM_TITLE],
                                    'name' => sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_TITLE, $album[dbTSGalbum::FIELD_ID])
                                    ),
                            'description' => array(
                                    'label' => TSG_LABEL_ALBUM_DESC,
                                    'value' => $album[dbTSGalbum::FIELD_ALBUM_DESCRIPTION],
                                    'name' => sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_DESCRIPTION, $album[dbTSGalbum::FIELD_ID])
                                    ),
                            'image' => array(
                                    'label' => TSG_LABEL_ALBUM_IMAGE_CHANGE,
                                    'link' => array(
                                            'url' => sprintf('%s%s%s',
                                                    $this->getPageLink(),
                                                    (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                    http_build_query(array(
                                                            self::REQUEST_ACTION => self::ACTION_ALBUM_IMG_CHANGE,
                                                            dbTSGgallery::FIELD_ID => $gallery_id,
                                                            dbTSGalbum::FIELD_ID => $album[dbTSGalbum::FIELD_ID]))
                                                    ),
                                            'text' => TSG_STR_ALBUM_IMG_CHANGE
                                            ),
                                    'title' => array(
                                            'label' => TSG_LABEL_ALBUM_IMG_TITLE,
                                            'value' => $album[dbTSGalbum::FIELD_IMAGE_TITLE],
                                            'name' => sprintf('%s_%s', dbTSGalbum::FIELD_IMAGE_TITLE, $album[dbTSGalbum::FIELD_ID])
                                            )
                                    ),
                            'delete' => array(
                                    'label' => TSG_LABEL_ALBUM_DELETE,
                                    'name' => self::REQUEST_ALBUM_DELETE,
                                    'value' => $album[dbTSGalbum::FIELD_ID]
                                    )
                            ),
                    'pictures' => array(
                            'items' => $pictures_array,
                            'add' => array(
                                    'label' => TSG_LABEL_PICTURE_ADD,
                                    'url' => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION => self::ACTION_PICTURE_ADD,
                                                        dbTSGgallery::FIELD_ID => $gallery_id,
                                                        dbTSGalbum::FIELD_ID => $album[dbTSGalbum::FIELD_ID]))
                                            ),
                                    )
                            )
                    );
        } // foreach $albums
        
        
        $data = array(
                'form' => array(
                        'name' => 'tsg_gallery',
                        'link' => $this->getPageLink(),
                        'action' => array(
                                'name' => self::REQUEST_ACTION,
                                'value' => self::ACTION_GALLERY_CHECK),
                        'title' => TSG_TITLE_GALLERY,
                        'is_message' => $this->isMessage() ? 1 : 0,
                        'intro' => $this->isMessage() ? $this->getMessage() : TSG_INTRO_GALLERY,
                        'btn' => array(
                                'ok' => tool_btn_ok,
                                'abort' => tool_btn_abort)),
                'gallery' => $gallery_array,
                'albums' => array(
                        'items' => $albums_array,
                        'create' => array(
                                'label' => TSG_LABEL_ALBUM_CREATE,
                                'link' => array(
                                        'text' => TSG_STR_ALBUM_CREATE,
                                        'url' => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION => self::ACTION_ALBUM_CREATE,
                                                        dbTSGgallery::FIELD_ID => $gallery_id
                                                    ))
                                                )
                                        ),
                                'hint' => TSG_HINT_ALBUM_CREATE
                                ),
                        )
                );
        return $this->getTemplate('backend.gallery.lte', $data);
    } // dlgGallery()
    
    /**
     * Check changes from dlgGallery
     * 
     * @return string|boolean - dlgGallery on success or false on error
     */
    protected function checkGallery() {
        global $dbTSGgallery;
        global $dbTSGalbum;
        global $dbTSGpicture;
        
        $gallery_id = isset($_REQUEST[dbTSGgallery::FIELD_ID]) ? $_REQUEST[dbTSGgallery::FIELD_ID] : -1;
        
        $message = '';
        $data = array();
        $data[dbTSGgallery::FIELD_NAME] = isset($_REQUEST[dbTSGgallery::FIELD_NAME]) ? trim($_REQUEST[dbTSGgallery::FIELD_NAME]) : '';
        $data[dbTSGgallery::FIELD_DESCRIPTION] = isset($_REQUEST[dbTSGgallery::FIELD_DESCRIPTION]) ? trim($_REQUEST[dbTSGgallery::FIELD_DESCRIPTION]) : '';
        
        $gallery = $dbTSGgallery->getFields();
        if ($gallery_id > 0) {
            // gallery is already existing
            if (isset($_REQUEST[self::REQUEST_GALLERY_DELETE]) && ($_REQUEST[self::REQUEST_GALLERY_DELETE] == $gallery_id)) {
                // delete the gallery and all depending records
                $where = array(dbTSGgallery::FIELD_ID => $gallery_id);
                if (!$dbTSGgallery->sqlDeleteRecord($where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
                    return false;
                }
                $where = array(dbTSGalbum::FIELD_GALLERY_ID => $gallery_id);
                if (!$dbTSGalbum->sqlDeleteRecord($where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                    return false;
                }
                $where = array(dbTSGpicture::FIELD_GALLERY_ID => $gallery_id);
                if (!$dbTSGpicture->sqlDeleteRecord($where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                    return false;
                }    
                // unset $_REQUEST, set message and return to the gallery dialog            
                unset($_REQUEST[dbTSGgallery::FIELD_ID]);
                $this->setMessage(sprintf(TSG_MSG_GALLERY_DELETED, $gallery_id));
                return $this->dlgGallery();
            }
            $where = array(dbTSGgallery::FIELD_ID => $gallery_id);
            if (!$dbTSGgallery->sqlSelectRecord($where, $gallery)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
                return false;
            }
            if (count($gallery) < 1) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $gallery_id)));
                return false;
            }
            $gallery = $gallery[0];
        }
        // check minimum settings
        if (empty($data[dbTSGgallery::FIELD_NAME]) || strlen($data[dbTSGgallery::FIELD_NAME]) < 3) {
            $this->setMessage(TSG_MSG_GALLERY_NAME_INVALID);
            return $this->dlgGallery();
        }
        
        if ($data[dbTSGgallery::FIELD_NAME] != $gallery[dbTSGgallery::FIELD_NAME]) {
            // new record or record changed ...
            if ($gallery_id > 0) {
                // update record
                $where = array(dbTSGgallery::FIELD_ID => $gallery_id);
                if (!$dbTSGgallery->sqlUpdateRecord($data, $where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
                    return false;
                }
                $gallery = $data;
                $message .= TSG_MSG_GALLERY_UPDATED;
            }
            else {
                // new record
                if (!$dbTSGgallery->sqlInsertRecord($data, $gallery_id)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
                    return false;
                }
                $gallery = $data;
                $_REQUEST[dbTSGgallery::FIELD_ID] = $gallery_id;
                $this->setMessage(TSG_MSG_GALLERY_INSERTED);
                return $this->dlgGallery();
            }
        }
                
        // ok gallery is saved, now look for the albums and pictures
        if (isset($_REQUEST[self::REQUEST_ALBUM_DELETE]) && !empty($_REQUEST[self::REQUEST_ALBUM_DELETE])) {
            // delete one or more albums
            $ids = array();
            if (is_array($_REQUEST[self::REQUEST_ALBUM_DELETE])) {
                $ids = $_REQUEST[self::REQUEST_ALBUM_DELETE];
            }
            else {
                $ids[] = $_REQUEST[self::REQUEST_ALBUM_DELETE];
            }
            foreach ($ids as $id) {
                $where = array(dbTSGalbum::FIELD_ID => $id);
                if (!$dbTSGalbum->sqlDeleteRecord($where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                    return false;
                }
            }
            $message .= sprintf(TSG_MSG_ALBUM_DELETED, implode(', ',$ids));
        }
                
        $where = array(dbTSGalbum::FIELD_GALLERY_ID => $gallery_id);
        $albums = array();
        if (!$dbTSGalbum->sqlSelectRecord($where, $albums)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
            return false;
        }
        foreach ($albums as $album) {
            $album_id = $album[dbTSGalbum::FIELD_ID];
            $img_title = (isset($_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_IMAGE_TITLE, $album_id)]) ) ? $_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_IMAGE_TITLE, $album_id)] : '';
            $title = (isset($_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_TITLE, $album_id)])) ? $_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_TITLE, $album_id)] : '';
            $desc = (isset($_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_DESCRIPTION, $album_id)])) ? $_REQUEST[sprintf('%s_%s', dbTSGalbum::FIELD_ALBUM_DESCRIPTION, $album_id)] : '';
            
            if (($img_title != $album[dbTSGalbum::FIELD_IMAGE_TITLE]) ||
                    ($title != $album[dbTSGalbum::FIELD_ALBUM_TITLE]) ||
                    ($desc != $album[dbTSGalbum::FIELD_ALBUM_DESCRIPTION])) {
                $data = array(
                        dbTSGalbum::FIELD_ALBUM_DESCRIPTION => $desc,
                        dbTSGalbum::FIELD_ALBUM_TITLE => $title,
                        dbTSGalbum::FIELD_IMAGE_TITLE => $img_title);
                $where = array(dbTSGalbum::FIELD_ID => $album[dbTSGalbum::FIELD_ID]);
                if (!$dbTSGalbum->sqlUpdateRecord($data, $where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                    return false;
                }
                $message .= sprintf(TSG_MSG_ALBUM_UPDATED, $album[dbTSGalbum::FIELD_ID]);
            }
            // pictures to delete?
            if (isset($_REQUEST[self::REQUEST_PICTURE_DELETE]) && !empty($_REQUEST[self::REQUEST_PICTURE_DELETE])) {
                // delete one or more prictures
                $ids = array();
                (is_array($_REQUEST[self::REQUEST_PICTURE_DELETE])) ? $ids = $_REQUEST[self::REQUEST_PICTURE_DELETE] : $ids[] = $_REQUEST[self::REQUEST_ALBUM_DELETE];
                foreach ($ids as $id) {
                    $where = array(dbTSGpicture::FIELD_ID => $id);
                    if (!$dbTSGpicture->sqlDeleteRecord($where)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                        return false;
                    }
                }
                $message .= sprintf(TSG_MSG_PICTURE_DELETED, implode(', ',$ids));
            }
            // load Pictures
            $where = array(dbTSGpicture::FIELD_ALBUM_ID => $album_id);
            $pictures = array();
            if (!$dbTSGpicture->sqlSelectRecord($where, $pictures)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                return false;
            }
            foreach ($pictures as $picture) {
                $picture_id = $picture[dbTSGpicture::FIELD_ID];
                $title = isset($_REQUEST[sprintf('%s_%s', dbTSGpicture::FIELD_IMAGE_TITLE, $picture_id)]) ? $_REQUEST[sprintf('%s_%s', dbTSGpicture::FIELD_IMAGE_TITLE, $picture_id)] : '';
                if ($picture[dbTSGpicture::FIELD_IMAGE_TITLE] != $title) {
                    $data = array(dbTSGpicture::FIELD_IMAGE_TITLE => $title);
                    $where = array(dbTSGpicture::FIELD_ID => $picture_id);
                    if (!$dbTSGpicture->sqlUpdateRecord($data, $where)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                        return false;
                    }
                    $message .= sprintf(TSG_MSG_PICTURE_UPDATED, basename($picture[dbTSGpicture::FIELD_IMAGE_URL]));
                }
            }
        }
        
        $this->setMessage($message);
        return $this->dlgGallery();
    } // checkGallery()
    
    
    protected function selectAlbumPicture() {
        $action = isset($_REQUEST[self::REQUEST_ACTION]) ? $_REQUEST[self::REQUEST_ACTION] : self::ACTION_DEFAULT;
        switch ($action) :
        case self::ACTION_ALBUM_CREATE:
            $params = array(
                    dbTSGgallery::FIELD_ID => $_REQUEST[dbTSGgallery::FIELD_ID],
                    self::REQUEST_NEXT_ACTION => self::ACTION_ALBUM_CHECK
            );
            $_SESSION[self::SESSION_PARAMS] = serialize($params);
            break;
        case self::ACTION_PICTURE_ADD:
            $params = array(
                    dbTSGgallery::FIELD_ID => $_REQUEST[dbTSGgallery::FIELD_ID],
                    dbTSGalbum::FIELD_ID => $_REQUEST[dbTSGalbum::FIELD_ID],
                    self::REQUEST_NEXT_ACTION => self::ACTION_PICTURE_CHECK
            );
            $_SESSION[self::SESSION_PARAMS] = serialize($params);
            break;
        case self::ACTION_ALBUM_IMG_CHANGE:
            $params = array(
                    dbTSGgallery::FIELD_ID => $_REQUEST[dbTSGgallery::FIELD_ID],
                    dbTSGalbum::FIELD_ID => $_REQUEST[dbTSGalbum::FIELD_ID],
                    self::REQUEST_NEXT_ACTION => self::ACTION_ALBUM_CHECK
            );
            $_SESSION[self::SESSION_PARAMS] = serialize($params);
            break;
        default:
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, TSG_ERROR_UNDEFINED_ERROR));
            return false;
        endswitch;
        
        $result = $this->dlgMediaDir();
        return $result;
    } // selectAlbumPicture()
    
    protected function checkAlbumPicture() {
        global $dbTSGalbum;
        global $dbTSGpicture;
        
        if (!isset($_REQUEST[mediaBrowser::REQUEST_FILE])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MISSING_PARAMS, 'REQUEST_FILE')));
            return false;
        }
        if (!isset($_REQUEST[dbTSGgallery::FIELD_ID])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MISSING_PARAMS, 'FIELD_ID')));
            return false;
        }
        $file = $this->getMediaURL() . $_REQUEST[mediaBrowser::REQUEST_FILE];
        $album_id = isset($_REQUEST[dbTSGalbum::FIELD_ID]) ? $_REQUEST[dbTSGalbum::FIELD_ID] : -1;
        if ($album_id < 1) {
            // add new record to the gallery album
            $data = array(
                    dbTSGalbum::FIELD_GALLERY_ID => $_REQUEST[dbTSGgallery::FIELD_ID],
                    dbTSGalbum::FIELD_IMAGE_URL => $file
                    );
            if (!$dbTSGalbum->sqlInsertRecord($data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                return false;
            } 
            $this->setMessage(sprintf(TSG_MSG_ALBUM_CREATED, basename($file)));
        }
        elseif ($_REQUEST[self::REQUEST_NEXT_ACTION] == self::ACTION_ALBUM_CHECK) {
            // update album record
            $where = array(dbTSGalbum::FIELD_ID => $album_id);
            $data = array(
                    dbTSGalbum::FIELD_IMAGE_URL => $file
            );
            if (!$dbTSGalbum->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
                return false;
            }
            $this->setMessage(sprintf(TSG_MSG_ALBUM_IMG_CHANGED, basename($file)));
        }
        elseif ($_REQUEST[self::REQUEST_NEXT_ACTION] == self::ACTION_PICTURE_CHECK) {
            // update album picture
            $data = array(
                    dbTSGpicture::FIELD_ALBUM_ID => $album_id,
                    dbTSGpicture::FIELD_GALLERY_ID => $_REQUEST[dbTSGgallery::FIELD_ID],
                    dbTSGpicture::FIELD_IMAGE_URL => $file
                    );
            if (!$dbTSGpicture->sqlInsertRecord($data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                return false;
            }
            $this->setMessage(sprintf(TSG_MSG_PICTURE_ADDED, basename($file)));
        }
        return $this->dlgGallery();
    } // checkAlbumPicture()
    
} // class tsGallery

