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

// include .secure.php to protect this file and the whole CMS!
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
// end include .secure.php

// load the required libraries
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';
// load LEPTON functions
require_once WB_PATH . '/framework/functions.php';

/**
 * FilterIterator for parsing images in the /MEDIA directory
 *
 * @author phpManufaktur, ralf.hertsch@phpmanufaktur.de
 */
 class imageExtensionFilter extends FilterIterator {

    private $extensions;

    /**
     * Constructor imageExtensionFilter
     * Specify the allowed file extensions in the array $allowed_extensions
     *
     * @param iterator $iterator
     * @param array $allowed_extensions
     */
    public function __construct($iterator, $allowed_extensions = array()) {
        $this->setExtensions(implode('|', $allowed_extensions));
        parent::__construct($iterator);
    } // __construct()

    /**
     * @return the $extensions
     */
    protected function getExtensions ()
    {
        return $this->extensions;
    }

    /**
     * @param field_type $extensions
     */
    protected function setExtensions ($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Set the accepted filter to the desired extension array and to directories
     * to enable recursing through the /MEDIA directory.
     *
     * @see FilterIterator::accept()
     */
    public function accept() {
        $filter = sprintf('%%.(%s)%%si', $this->getExtensions());
        return (($this->current()->isFile() &&
                preg_match($filter, $this->current()->getBasename()) ||
                $this->current()->isDir()));
    } // accept()

} //  imageExtensionFilter

class mediaBrowser {
    
    const REQUEST_ACTION			= 'act';
    const REQUEST_DIRECTORY         = 'dir';
    const REQUEST_FILE              = 'file';
    const REQUEST_UPLOAD            = 'upl';
    const REQUEST_MKDIR             = 'mkdir';
    
    const ACTION_MEDIA_BROWSER = 'mbamb';
    const ACTION_MEDIA_CHECK            = 'mbac';
    const ACTION_MEDIA_CHDIR            = 'mbacd';
    const ACTION_MEDIA_MKDIR = 'mbamkdir';
    const ACTION_MEDIA_RMDIR            = 'mbard';
    const ACTION_MEDIA_RMDIR_EXEC       = 'mbarde';
    const ACTION_MEDIA_FILE_SELECT      = 'mbafs';
    const ACTION_MEDIA_FILE_DELETE      = 'mbafd';
    const ACTION_MEDIA_FILE_DELETE_EXEC = 'mbafde';
    const ACTION_MEDIA_UPLOAD = 'mbaupl';   
    
    private $allowedExtensions;
    private $mediaPath;
    private $mediaURL;
    private $imagePath;
    private $imageURL;
    private $tempPath = '';
    private $tempURL = '';
    private $error = '';
    private $message = '';
    private $templatePath = '';
    private $pageLink = '';
    
    /**
     * es added for compatibility to imageTweak
     * @see http://phpmanufaktur.de/image_tweak
     */ 
    const CLASS_CROP		= 'crop';
    const CLASS_TOP			= 'top';
    const CLASS_BOTTOM		= 'bottom';
    const CLASS_LEFT		= 'left';
    const CLASS_RIGHT		= 'right';
    const CLASS_ZOOM	    = 'zoom';
    const CLASS_NO_CACHE	= 'no-cache';
    
    const SELECT_MODE_SINGLE    = 1;
    const SELECT_MODE_MULTIPLE  = 2;
    const SELECT_MODE_NONE      = 0;
    
    const MB_TEMP_DIR       = '/temp/media_browser'; 
    
    public function __construct() {
        global $dbTSGconfig;
        // first get the allowed file extensions for the media browser
        $exts = $dbTSGconfig->getValue(dbTSGconfig::CFG_IMAGE_EXTENSIONS);
        $this->setAllowedExtensions($exts);
        $this->setPageLink(ADMIN_URL.'/admintools/tool.php?tool=ts_gallery');
        $this->setMediaPath(WB_PATH.MEDIA_DIRECTORY);
        $this->setMediaURL(WB_URL.MEDIA_DIRECTORY);
        $this->setImagePath(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/images/');
        $this->setImageURL(WB_URL.'/modules/'.basename(dirname(__FILE__)).'/images/');        
        $this->setTempPath(WB_PATH.self::MB_TEMP_DIR);
        $this->setTempURL(WB_URL.self::MB_TEMP_DIR);
        $this->setTemplatePath(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/') ;
        if (!file_exists($this->getTempPath())) {
            try {
                mkdir($this->getTempPath(), 0755, true);
            } 
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $this->getTempPath(), $ex->getMessage())));
                return false;
            }            
        }
    } // __construct()

	/**
     * @return the $imagePath
     */
    protected function getImagePath ()
    {
        return $this->imagePath;
    }

	/**
     * @return the $imageURL
     */
    protected function getImageURL ()
    {
        return $this->imageURL;
    }

	/**
     * @param field_type $imagePath
     */
    protected function setImagePath ($imagePath)
    {
        $this->imagePath = $imagePath;
    }

	/**
     * @param field_type $imageURL
     */
    protected function setImageURL ($imageURL)
    {
        $this->imageURL = $imageURL;
    }

	/**
     * @return the $pageLink
     */
    protected function getPageLink ()
    {
        return $this->pageLink;
    }

	/**
     * @param string $pageLink
     */
    protected function setPageLink ($pageLink)
    {
        $this->pageLink = $pageLink;
    }

	/**
     * @return the $templatePath
     */
    protected function getTemplatePath ()
    {
        return $this->templatePath;
    }

	/**
     * @param string $templatePath
     */
    protected function setTemplatePath ($templatePath)
    {
        $this->templatePath = $templatePath;
    }

	/**
     * @return the $error
     */
    public function getError ()
    {
        return $this->error;
    }

	/**
     * @param field_type $error
     */
    protected function setError ($error)
    {
        $this->error = $error;
    }
    
    public function isError() {
        return (bool) !empty($this->error);
    }

	/**
     * @return the $message
     */
    public function getMessage ()
    {
        return $this->message;
    }

	/**
     * @param field_type $message
     */
    protected function setMessage ($message)
    {
        $this->message = $message;
    }
    
    public function isMessage() {
        return (bool) !empty($this->message);
    }

	/**
     * @return the $mediaPath
     */
    protected function getMediaPath ()
    {
        return $this->mediaPath;
    }

	/**
     * @param field_type $mediaPath
     */
    protected function setMediaPath ($mediaPath)
    {
        $this->mediaPath = $mediaPath;
    }

	/**
     * @return the $mediaURL
     */
    protected function getMediaURL ()
    {
        return $this->mediaURL;
    }

	/**
     * @param field_type $mediaURL
     */
    protected function setMediaURL ($mediaURL)
    {
        $this->mediaURL = $mediaURL;
    }

	/**
     * @return the $tempPath
     */
    protected function getTempPath ()
    {
        return $this->tempPath;
    }

	/**
     * @param field_type $tempPath
     */
    protected function setTempPath ($tempPath)
    {
        $this->tempPath = $tempPath;
    }

	/**
     * @return the $tempURL
     */
    protected function getTempURL ()
    {
        return $this->tempURL;
    }

	/**
     * @param field_type $tempURL
     */
    protected function setTempURL ($tempURL)
    {
        $this->tempURL = $tempURL;
    }

	/**
     * @return the $allowed_extensions
     */
    protected function getAllowedExtensions ()
    {
        return $this->allowedExtensions;
    }

	/**
     * @param field_type $allowed_extensions
     */
    protected function setAllowedExtensions ($allowed_extensions)
    {
        $this->allowedExtensions = $allowed_extensions;
    }
    
    /**
     * Parse the desired $template with $template_data and returns the resulting
     * output of the template engine
     *
     * @param string $template - only template name
     * @param array $template_data
     * @return string template output
     */
    protected function getTemplate($template, $template_data) {
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
     * Delete a directory recursive with all files
     * 
     * @param string $directory
     * @param boolean $empty - if true, delete only empty directory
     * @return boolean
     */
    public function rrmdir($directory, $empty = false) {
        if(substr($directory, -1) == DIRECTORY_SEPARATOR) {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_RMDIR_DIR_INVALID, $directory)));
            return false;
        } 
        elseif (!is_readable($directory)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_RMDIR_DIR_NOT_READABLE, $directory)));
            return false;
        } 
        else {
            $directoryHandle = opendir($directory);
            while (false !== ($contents = readdir($directoryHandle))) {
                if ($contents != '.' && $contents != '..') {
                    $path = $directory . DIRECTORY_SEPARATOR . $contents;
                    if (is_dir($path)) {
                        $this->rrmdir($path);
                    } 
                    else {
                        unlink($path);
                    }
                }
            }
            closedir($directoryHandle);

            if ($empty == false) {
                if (!rmdir($directory)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_RMDIR_RMDIR, $directory)));
                    return false;
                }
            }
            return true;
        }
    } // rrmdir()
    
    /**
     * Execute the command the directory $remove_directory
     * 
     * @param string $remove_directory
     * @return boolean
     */
    public function execRemoveDirectory($remove_directory = '') {
        if (empty($remove_directory)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, TSG_ERROR_RMDIR_DIR_EMPTY));
            return false;
        }
        if (file_exists($this->getMediaPath().$remove_directory)) {
            if (!$this->rrmdir($this->getMediaPath().$remove_directory)) {
                return false;
            }
        }
        else {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_DIR_NOT_EXISTS, $remove_directory)));
            return false;
        }
        return true;
    } // execRemoveDirectory()
    
    /**
     * Show a Dialog to confirm the remove command for a directory
     * 
     * @param string $remove_directory
     * @return string confirm dialog
     */
    public function confirmRemoveDirectory($remove_directory = '') {
        $data = array(
                'form' => array(
                        'name' => 'media_browser',
                        'link' => $this->getPageLink(),
                        'action' => array(
                                'name' => self::REQUEST_ACTION,
                                'value' => self::ACTION_MEDIA_RMDIR_EXEC ),
                        'title' => TSG_TITLE_CONFIRM_RMDIR,
                        'is_message' => ($this->isMessage()) ? 1 : 0,
                        'intro' => ($this->isMessage()) ? $this->getMessage() : sprintf(TSG_CONFIRM_RMDIR, $remove_directory),
                        'btn' => array(
                                'yes' => TSG_BTN_YES,
                                'no' => TSG_BTN_NO),
                        'abort' => array(
                                'link' => sprintf('%s%s%s',
                                        $this->getPageLink(),
                                        (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                        http_build_query(array(
                                                self::REQUEST_ACTION  => self::ACTION_MEDIA_BROWSER,
                                                self::REQUEST_DIRECTORY => substr($remove_directory, 0, strrpos($remove_directory, DIRECTORY_SEPARATOR)))))),
                        ),
                'item' => array(
                        'name' => self::REQUEST_DIRECTORY,
                        'value' => $remove_directory)
                );
        return $this->getTemplate('backend.media.browser.confirm.lte', $data);
    } // confirmRemoveDirectory()
    
    /**
     * Show a dialog to confirm the deletion of a file
     * 
     * @param string $delete_file
     * @return string confirm dialog
     */
    public function confirmDeleteFile($delete_file = '') {
        $data = array(
                'form' => array(
                        'name' => 'media_browser',
                        'link' => $this->getPageLink(),
                        'action' => array(
                                'name' => self::REQUEST_ACTION,
                                'value' => self::ACTION_MEDIA_FILE_DELETE_EXEC),
                        'title' => TSG_TITLE_CONFIRM_FILE_DELETE,
                        'is_message' => ($this->isMessage()) ? 1 : 0,
                        'intro' => ($this->isMessage()) ? $this->getMessage() : sprintf(TSG_CONFIRM_FILE_DELETE, $delete_file),
                        'btn' => array(
                                'yes' => TSG_BTN_YES,
                                'no' => TSG_BTN_NO),
                        'abort' => array(
                                'link' => sprintf('%s%s%s',
                                        $this->getPageLink(),
                                        (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                        http_build_query(array(
                                                self::REQUEST_ACTION  => self::ACTION_MEDIA_BROWSER,
                                                self::REQUEST_DIRECTORY => substr($delete_file, 0, strrpos($delete_file, DIRECTORY_SEPARATOR)))))),
                ),
                'item' => array(
                        'name' => self::REQUEST_FILE,
                        'value' => $delete_file)
        );
        return $this->getTemplate('backend.media.browser.confirm.lte', $data);
    } // confirmDeleteFile()
    
    /**
     * Execute the command to delete a file
     * 
     * @param string $delete_file
     */
    public function execDeleteFile($delete_file = '') {
        if (file_exists($delete_file)) {
            if (!unlink($delete_file)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_DELETE, $delete_file)));
                return false;
            }
            return true;
        }
        else {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_NOT_FOUND, $delete_file)));
            return false;
        }
    } // execDeleteFile()
    
    /**
     * Add a slash to the end of a path if the slash not exists
     * 
     * @param string reference $path
     * @return string $path
     */
    protected function addSlash(&$path) {
        $path = substr($path, strlen($path)-1, 1) == DIRECTORY_SEPARATOR ? $path : $path . DIRECTORY_SEPARATOR;
        return $path;
    } // addSlash()
    
    /**
     * Remove a leading slash from path string
     * @param string reference $path
     * @return string $path
     */
    protected function removeLeadingSlash(&$path) {
        $path = substr($path, 0, 1) == DIRECTORY_SEPARATOR ? substr($path, 1, strlen($path)) : $path;
        return $path;
    } // removeLeadingSlash()
    
    /**
     * Processing a file upload
     * 
     * @return boolean 
     */
    public function processFileUpload() {      
        $dir = isset($_REQUEST[self::REQUEST_DIRECTORY]) ? $_REQUEST[self::REQUEST_DIRECTORY] : '';
        $dir = $this->getMediaPath(). DIRECTORY_SEPARATOR. $dir;
        $this->addSlash($dir);
        if (isset($_FILES[self::REQUEST_UPLOAD]) && (is_uploaded_file($_FILES[self::REQUEST_UPLOAD]['tmp_name']))) {
            if ($_FILES[self::REQUEST_UPLOAD]['error'] == UPLOAD_ERR_OK) {
                // check if uploaded file is allowed
                $explode = explode('.', $_FILES[self::REQUEST_UPLOAD]['name']);
                $ext = end($explode);
                $ext = strtolower($ext);
                if (!in_array($ext, $this->getAllowedExtensions())) { 
                    // disallowed file or filetype - delete uploaded file
                    @unlink($_FILES[self::REQUEST_UPLOAD]['tmp_name']);
                    $this->setMessage(sprintf(TSG_MSG_UPLOAD_INVALID_EXTENSION, implode(', ', $this->getAllowedExtensions())));
                    return false;
                }
                $tmp_file = $_FILES[self::REQUEST_UPLOAD]['tmp_name'];
                $upl_file = $dir. media_filename($_FILES[self::REQUEST_UPLOAD]['name']);
                if (!move_uploaded_file($tmp_file, $upl_file)) {
                    // error moving file
                    $this->setError(sprintf(TSG_ERROR_UPLOAD_MOVE_FILE, $upl_file));
                    return false;
                }
                else {
                    $this->setMessage(sprintf(TSG_MSG_UPLOAD_SUCCESS, basename($upl_file)));
                    return true;
                }
            }
            else {
                switch ($_FILES[self::REQUEST_UPLOAD]['error']):
                case UPLOAD_ERR_INI_SIZE:
                    $this->setError(sprintf(TSG_ERROR_UPLOAD_INI_SIZE, ini_get('upload_max_filesize')));
                break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->setError(TSG_ERROR_UPLOAD_FORM_SIZE);
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->setError(sprintf(TSG_ERROR_UPLOAD_PARTIAL, $_FILES[self::REQUEST_UPLOAD]['name']));
                    break;
                default:
                    $this->setError(TSG_ERROR_UPLOAD_UNDEFINED_ERROR);
                endswitch;
                return false;
            }
        }
        else {
            $this->setMessage(TSG_MSG_UPLOAD_NO_FILE);
            return false;
        }
    } // processUploadedFile()
    
    /**
     * Create a directory
     * 
     * @return boolean
     */
    public function createDirectory() {
        if (isset($_REQUEST[self::REQUEST_MKDIR]) && !empty($_REQUEST[self::REQUEST_MKDIR])) {
            $dir = isset($_REQUEST[self::REQUEST_DIRECTORY]) ? $_REQUEST[self::REQUEST_DIRECTORY] : '';
            $dir = $this->getMediaPath(). DIRECTORY_SEPARATOR. $dir;
            $this->addSlash($dir);
            $dir = $dir . strtolower($_REQUEST[self::REQUEST_MKDIR]);
            try {
                mkdir($dir, 0755, true);
            } 
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $dir, $ex->getMessage())));
                return false;
            }
            $this->setMessage(sprintf(TSG_MSG_MKDIR_SUCCESS, substr($dir, strlen(WB_PATH))));
            return true;
        }
        else {
            // no valid directory name ...
            $this->setMessage(TSG_MSG_MKDIR_INVALID_DIR);
            return false;
        }
    } // createDirectory()
    
    public function selectFiles() {
        print_r($_REQUEST[self::REQUEST_FILE]);
        return true;
    }
    
    /**
     * Shows a Media Browser for the selected $media_directory
     * 
     * @param string $media_directory
     * @return boolean|Ambigous <string, boolean, mixed>
     */
    public function dlgMediaDirectory($media_directory = '', $select_mode = self::SELECT_MODE_NONE) {
        global $dbTSGconfig;
        global $kitTools;
        
        $maxIconWidth = $dbTSGconfig->getValue(dbTSGconfig::CFG_MB_IMG_ICON_WIDTH);
        $maxPreviewWidth = $dbTSGconfig->getValue(dbTSGconfig::CFG_MB_IMG_PREVIEW_WIDTH);
        
        $media_directory = substr($media_directory, 0, 1) == DIRECTORY_SEPARATOR ? substr($media_directory, 1, strlen($media_directory)) : $media_directory;
        $directory = $this->getMediaPath().DIRECTORY_SEPARATOR.$media_directory;
        $images = array();
        $directories = array();
        $parent_directory = $this->getMediaURL();
        
        // use LEPtoken if neccessary
        //$leptoken = (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '';
        
        $iterator = new imageExtensionFilter(new RecursiveDirectoryIterator($directory), $this->getAllowedExtensions());
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                list($width, $height, $type) = getimagesize($fileinfo->__toString());
                $media_dir = dirname(substr($fileinfo->__toString(), strlen(WB_PATH.MEDIA_DIRECTORY)));
                // create Icon
                $icon_path = $this->getTempPath().DIRECTORY_SEPARATOR.'icon'.$media_dir;
                $icon_path = substr($icon_path, strlen($icon_path)-1, 1) == DIRECTORY_SEPARATOR ? $icon_path : $icon_path.DIRECTORY_SEPARATOR;
                // create icon image
                if ($width > $maxIconWidth) {
                    // calculate size for icon
                    $percent = (int) ($maxIconWidth/($width/100));
                    $iconWidth = $maxIconWidth;
                    $iconHeight = (int) ($height/(100)*$percent);
                }
                else {
                    // use orginal image dimensions
                    $iconWidth = $width;
                    $iconHeight = $height;
                }
                if (!file_exists($icon_path.$fileinfo->getBasename()) ||
                    ($fileinfo->getMTime() != ($mtime = filemtime($icon_path.$fileinfo->getBasename())))) {
                  // create a new icon 
                  if (!file_exists($icon_path)) {
                      try {
                          mkdir($icon_path, 0755, true);
                      }
                      catch(ErrorException $ex) {
                          sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $icon_path.$fileinfo->getBasename(), $ex->getMessage()));
                          return false;
                      }
                  }
                  if (false == ($tweaked_file = $this->createTweakedFile(
                          $fileinfo->getBasename(), 
                          strtolower(substr($fileinfo->getBasename(), strrpos($fileinfo->getBasename(), '.')+1)),
                          $fileinfo->__toString(),
                          $iconWidth,
                          $iconHeight,
                          $width,
                          $height,
                          $fileinfo->getMTime(),
                          $icon_path))) {
                      // error creating the tweaked file
                      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                      return false;
                  }  
                }
                // create Preview
                $preview_path = $this->getTempPath().DIRECTORY_SEPARATOR.'preview'.$media_dir;
                $preview_path = substr($preview_path, strlen($preview_path)-1, 1) == DIRECTORY_SEPARATOR ? $preview_path : $preview_path.DIRECTORY_SEPARATOR;
                // create preview image
                if ($width > $maxPreviewWidth) {
                    // calculate size for icon
                    $percent = (int) ($maxPreviewWidth/($width/100));
                    $previewWidth = $maxPreviewWidth;
                    $previewHeight = (int) ($height/(100)*$percent);
                }
                else {
                    // use orginal image dimensions
                    $previewWidth = $width;
                    $previewHeight = $height;
                }
                if (!file_exists($preview_path.$fileinfo->getBasename()) ||
                        ($fileinfo->getMTime() != ($mtime = filemtime($preview_path.$fileinfo->getBasename())))) {
                    // create a new icon
                    if (!file_exists($preview_path)) {
                        try {
                            mkdir($preview_path, 0755, true);
                        }
                        catch(ErrorException $ex) {
                            sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $preview_path.$fileinfo->getBasename(), $ex->getMessage()));
                            return false;
                        }
                    }
                    if (false == ($tweaked_file = $this->createTweakedFile(
                            $fileinfo->getBasename(),
                            strtolower(substr($fileinfo->getBasename(), strrpos($fileinfo->getBasename(), '.')+1)),
                            $fileinfo->__toString(),
                            $previewWidth,
                            $previewHeight,
                            $width,
                            $height,
                            $fileinfo->getMTime(),
                            $preview_path))) {
                        // error creating the tweaked file
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                        return false;
                    }
                }
                
                
                $images[$fileinfo->getFilename()] = array(
                        'is_file'         => 1,
                        'basename'        => $fileinfo->getBasename(),
                        'modified'        => array(
                                'timestamp'  => $fileinfo->getMTime(),
                                'formatted'  => date(TSG_CFG_DATETIME_STR, $fileinfo->getMTime())),
                        'path'            => $fileinfo->__toString(),
                        'link'            => array(
                                'select'    => array(
                                        'url'    => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION  => self::ACTION_MEDIA_FILE_SELECT,
                                                        self::REQUEST_FILE    => substr($fileinfo->__toString(), strlen(WB_PATH.MEDIA_DIRECTORY))))),
                                        'text'   => TSG_STR_SELECT,
                                        'name' => self::REQUEST_FILE), 
                                'delete'    => array(
                                        'url'    => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION  => self::ACTION_MEDIA_FILE_DELETE,
                                                        self::REQUEST_FILE    => substr($fileinfo->__toString(), strlen(WB_PATH.MEDIA_DIRECTORY))))),
                                        'text'   => TSG_STR_DELETE,
                                        'name' => self::REQUEST_FILE)), 
                        'media_dir'       => $media_dir,
                        'icon'            => array(
                                'url'    => WB_URL. substr($icon_path, strlen(WB_PATH)) . $fileinfo->getBasename(),
                                'height' => $iconHeight,
                                'width'  => $iconWidth ),
                        'preview'         => array(
                                'url'    => WB_URL. substr($preview_path, strlen(WB_PATH)). $fileinfo->getBasename(),
                                'width'  => $previewWidth,
                                'height' => $previewHeight ),
                        'url'             => WB_URL. substr($fileinfo->__toString(), strlen(WB_PATH)),
                        'size'            => array(
                                'bytes'    => $fileinfo->getSize(),
                                'formatted'=> $kitTools->bytes2Str($fileinfo->getSize())),
                        'width'           => $width,
                        'height'          => $height,
                        'mime_type'       => image_type_to_mime_type($type));
            }
            elseif ($fileinfo->isDir()) {
                $directories[$fileinfo->getFilename()] = array(
                        'is_file'     => 0,
                        'is_parent'   => 0,
                        'link'        => array(
                                'select'    => array(
                                        'url'    => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION    => self::ACTION_MEDIA_CHDIR,
                                                        self::REQUEST_DIRECTORY => substr($fileinfo->__toString(), strlen(WB_PATH.MEDIA_DIRECTORY))))),
                                        'text'   => TSG_STR_CHDIR,
                                        'name' => self::REQUEST_DIRECTORY),
                                'delete'    => array(
                                        'url'    => sprintf('%s%s%s',
                                                $this->getPageLink(),
                                                (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                                http_build_query(array(
                                                        self::REQUEST_ACTION    => self::ACTION_MEDIA_RMDIR,
                                                        self::REQUEST_DIRECTORY => substr($fileinfo->__toString(), strlen(WB_PATH.MEDIA_DIRECTORY))))),
                                        'text'   => TSG_STR_RMDIR,
                                        'name' => self::REQUEST_DIRECTORY)), 
                        'basename'    => $fileinfo->getBasename(),
                        'path'        => $fileinfo->__toString(),
                        'url'         => WB_URL. substr($fileinfo->__toString(), strlen(WB_PATH)));
            }
        }
        
        // sort the directories alphabetical
        asort($directories);
        // if it is not the root directory insert a "move up" link at the first position ...
        if (!empty($media_directory)) { // && (substr_count($media_directory, DIRECTORY_SEPARATOR) > 0)) {
            $parent_directory = substr($media_directory, 0, strrpos($media_directory, DIRECTORY_SEPARATOR));
            $pd = array(
                    'is_file'     => 0,
                    'is_parent'   => 1,
                    'basename'    => '..',
                    'link'        => array(
                            'select'    => array(
                                    'url'    => sprintf('%s%s%s',
                                            $this->getPageLink(),
                                            (strpos($this->getPageLink(), '?') === false) ? '?' : '&',
                                            http_build_query(array(
                                                    self::REQUEST_ACTION    => self::ACTION_MEDIA_CHDIR,
                                                    self::REQUEST_DIRECTORY => $parent_directory))),
                                    'text'   => TSG_STR_CHDIR),
                                    'name' => self::REQUEST_DIRECTORY), 
                    'path'        => $this->getMediaPath().DIRECTORY_SEPARATOR.$parent_directory,
                    'url'         => $this->getMediaURL().DIRECTORY_SEPARATOR.$parent_directory);
            array_unshift($directories, $pd);
        }
        
        // sort the images alphabetical
        asort($images);
        $files = $directories + $images;
        
        $folder_path = $this->getImagePath().'mb_folder.png';
        $folder_url = $this->getImageURL().'mb_folder.png';
        if (!file_exists($folder_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_NOT_FOUND, $folder_path))); 
            return false;
        }
        list($folder_width, $folder_height) = getimagesize($folder_path);
        
        $parent_path = $this->getImagePath().'mb_parent.png';
        $parent_url = $this->getImageURL().'mb_parent.png';
        if (!file_exists($parent_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_NOT_FOUND, $parent_path)));
            return false;
        }
        list($parent_width, $parent_height) = getimagesize($parent_path);
        
        $delete_path = $this->getImagePath().'mb_delete.png';
        $delete_url = $this->getImageURL().'mb_delete.png';
        if (!file_exists($delete_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_NOT_FOUND, $delete_path)));
            return false;
        }
        list($delete_width, $delete_height) = getimagesize($delete_path);
        
        // pepare template
        $data = array(
                'form' => array(
                        'name' => 'media_browser',
                        'link' => $this->getPageLink(),
                        'action' => array(
                                'name' => self::REQUEST_ACTION,
                                'value' => self::ACTION_MEDIA_CHECK ),
                        'title' => TSG_TITLE_MEDIA_BROWSER,
                        'is_message'=> ($this->isMessage()) ? 1 : 0,
                        'intro' => ($this->isMessage()) ? $this->getMessage() : TSG_INTRO_MEDIA_BROWSER,
                        'header' => array(
                                'select' => TSG_HEADER_MB_SELECT,
                                'icon' => TSG_HEADER_MB_ICON,
                                'name' => TSG_HEADER_MB_NAME,
                                'dimension' => TSG_HEADER_MB_DIMENSION,
                                'size' => TSG_HEADER_MB_SIZE,
                                'date' => TSG_HEADER_MB_DATE,
                                'delete' => TSG_HEADER_MB_DELETE,
                                'upload' => TSG_HEADER_MB_UPLOAD,
                                'mkdir' => TSG_HEADER_MB_MKDIR),
                        'btn' => array(
                                'ok' => TSG_BTN_OK
                                )        
                        ),
                'directory' => array(
                        'name' => mediaBrowser::REQUEST_DIRECTORY,
                        'value' => $media_directory
                        ),
                'media' => array(
                        'settings' => array(
                                'icon' => array(
                                        'width' => $maxIconWidth),
                                'preview' => array(
                                        'width' => $maxPreviewWidth),
                                'select' => $select_mode),
                        'list' => $files,
                        'folder' => array(
                                'url' => $folder_url,
                                'width' => $folder_width,
                                'height' => $folder_height),
                        'parent' => array(
                                'url' => $parent_url,
                                'width' => $parent_width,
                                'height' => $parent_height),
                        'delete' => array(
                                'url' => $delete_url,
                                'width' => $delete_width,
                                'height' => $delete_height),
                        'path' => array(
                                'label' => TSG_LABEL_MB_BREADCRUMB,
                                'value' => DIRECTORY_SEPARATOR. $media_directory)),
                'upload' => array(
                        'name' => self::REQUEST_UPLOAD),
                'mkdir' => array(
                        'name' => self::REQUEST_MKDIR)
                );
        return $this->getTemplate('backend.media.browser.lte', $data);
    } // dlgMediaDirectory
    
    /**
     * Master routine from imageTweak to create optimized images.
     * @see http://phpmanufaktur.de/image_tweak
     * 
     * @param string $filename - basename of the image
     * @param string $extension - extension of the image
     * @param string $file_path - complete path to the image
     * @param integer $new_width - the new width in pixel
     * @param integer $new_height - the new height in pixel
     * @param integer $origin_width - the original width in pixel
     * @param integer $origin_height - the original height in pixel
     * @param integer $origin_filemtime - the FileMTime of the image
     * @param string $new_path - the path to the tweaked image
     * @param array $classes - optional es to force image operations
     * @return mixed - path to the new file on succes, boolean false on error
     */
    public function createTweakedFile($filename, $extension, $file_path, 
            $new_width, $new_height, $origin_width, $origin_height, 
            $origin_filemtime, $new_path, $classes=array()) {
        $extension = strtolower($extension);
		switch ($extension):
	  	case 'gif':
	  		$origin_image = imagecreatefromgif($file_path);
	        break;
	    case 'jpeg':
	    case 'jpg':
	        $origin_image = imagecreatefromjpeg($file_path);
	        break;
	    case 'png':
	        $origin_image = imagecreatefrompng($file_path);
	        break;
	    default: 
	        // unsupported image type
	    echo $extension;
	        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_TWEAK_INVALID_EXTENSION, $extension)));
	        return false;
	  	endswitch;

	  	// create new image of $new_width and $new_height
	  	$new_image = imagecreatetruecolor($new_width, $new_height);
	  	// Check if this image is PNG or GIF, then set if Transparent  
	  	if (($extension == 'gif') OR ($extension == 'png')) {
	  	    imagealphablending($new_image, false);
	  	    imagesavealpha($new_image,true);
	  	    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
	  	    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
	  	}
	  	if (in_array(self::CLASS_CROP, $classes)) {
	  	    // don't change image size...
	  	    $zoom = 100;
	  	    foreach ($classes as $class) { 
	  	        if (stripos($class, self::CLASS_ZOOM.'[') !== false) {
	  	            $x = substr($class, strpos($class, '[')+1, (strpos($class, ']') - (strpos($class, '[')+1))); 
	  	            $zoom = (int) $x; 
	  	            if ($zoom < 1) $zoom = 1;
	  	            if ($zoom > 100) $zoom = 100;
	  	        }
	  	    }
	  	    // crop image
	  	    if (in_array(self::CLASS_LEFT, $classes)) {
	  	        $x_pos = 0;
	  	    }
	  	    elseif (in_array(self::CLASS_RIGHT, $classes)) {
	  	        $x_pos = $origin_width-$new_width;
	  	    }
	  	    else {
	  	        $x_pos = ((int) $origin_width/2)-((int) $new_width/2);	
	  	    }
	  	    if (in_array(self::CLASS_TOP, $classes)) {
	  	        $y_pos = 0;
	  	    }
	  	    elseif (in_array(self::CLASS_BOTTOM, $classes)) {
	  	        $y_pos = $origin_height-$new_height;
	  	    }
	  	    else {
	  	        $y_pos = ((int) $origin_height/2) - ((int)$new_height/2);
	  	    }
	  	    if ($zoom !== 100) {
	  	        // change image size and crop image
	  	        $faktor = $zoom/100;
	  	        $zoom_width = (int) ($origin_width*$faktor);
	  	        $zoom_height = (int) ($origin_height*$faktor); 
	  	        imagecopyresampled($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height, $zoom_width, $zoom_height);
	  	    }
	  	    else {
	  	        // only crop image
	  	        imagecopy($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height);
	  	    }
	  	}
	  	else {
	  	    // resample image
	  	    imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);
	  	}
	  	
	  	if (!file_exists($new_path)) {
	  	    try {
	  	        mkdir($new_path, 0755, true);
	  	    }
	  	    catch(ErrorException $ex) {
	  	        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $new_path, $ex->getMessage())));
	  	        return false;
	  	    }
	  	}
	  	$new_file = $new_path.$filename;
	  	//Generate the file, and rename it to $newfilename
	  	switch ($extension): 
	  	case 'gif': 
	  	    imagegif($new_image, $new_file); 
       	    break;
       	case 'jpg':
       	case 'jpeg': 
       	    imagejpeg($new_image, $new_file, 90); // static setting for the JPEG Quality 
       	    break;
       	case 'png': 
       	    imagepng($new_image, $new_file); 
       	    break;
       	default:  
       	    // unsupported image type
       	    return false;
       	endswitch;
    
       	if (!chmod($new_file, 0644)) {
       	    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_CHMOD, basename($new_file))));
       	    return false;
       	}
       	if (($origin_filemtime !== false) && (touch($new_file, $origin_filemtime) === false)) {
       	    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_TOUCH, basename($new_file))));
       	    return false;
       	}
       	return $new_file;	  
	} // createTweakedFile()
    
} //  mediaBrowser
