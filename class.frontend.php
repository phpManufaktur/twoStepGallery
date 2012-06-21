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
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// load the required libraries
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';


class tsgFrontend {

    const PARAM_CSS					= 'css';
    const PARAM_PRESET				= 'preset';
    const PARAM_NAME                = 'name';

    private $params = array(
            self::PARAM_CSS				=> true,
            self::PARAM_PRESET			=> 1,
            self::PARAM_NAME            => ''
    );

    const GALLERY_TYPE_MAIN = 'main';
    const GALLERY_TYPE_PREVIEW = 'prev';

    const OPTIMIZE_MODE_WIDTH = 1;
    const OPTIMIZE_MODE_HEIGHT = 2;
    const OPTIMIZE_MODE_BOTH = 4;

    private $templatePath = '';
    private $tempPathMainImg = '';
    private $tempUrlMainImg = '';
    private $tempPathPrevImg = '';
    private $tempUrlPrevImg = '';
    private $previewWidth = 0;
    private $previewHeight = 0;
    private $mainWidth = 0;
    private $mainHeight = 0;
    private $optimizeMode = self::OPTIMIZE_MODE_WIDTH;

    public function __construct() {
        global $dbTSGconfig;

        $this->setTemplatePath(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/' . $this->params[self::PARAM_PRESET] . '/' . TS_GALLERY_LANGUAGE . '/');
        date_default_timezone_set(TSG_CFG_TIME_ZONE);

        $this->setTempPathMainImg(WB_PATH . '/temp/ts_gallery/main');
        $this->setTempUrlMainImg(WB_URL . '/temp/ts_gallery/main');
        $this->setTempPathPrevImg(WB_PATH.'/temp/ts_gallery/prev');
        $this->setTempUrlPrevImg(WB_URL.'/temp/ts_gallery/prev');

        // check if the TEMP data should be resetted
        if ($dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_DELETE_TEMP_DATA)) {
            $mediaBrowser = new mediaBrowser();
            if (file_exists($this->getTempPathMainImg())) {
                $mediaBrowser->rrmdir($this->getTempPathMainImg());
            }
            if (file_exists($this->getTempPathPrevImg())) {
                $mediaBrowser->rrmdir($this->getTempPathPrevImg());
            }
            // reset the cfg command
            $dbTSGconfig->setValueByName('0', dbTSGconfig::CFG_GAL_DELETE_TEMP_DATA);
        }

        if (!file_exists($this->getTempPathMainImg())) {
            try {
                mkdir($this->getTempPathMainImg(), 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $this->getTempPathMainImg(), $ex->getMessage())));
                return false;
            }
        }
        if (!file_exists($this->getTempPathPrevImg())) {
            try {
                mkdir($this->getTempPathPrevImg(), 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $this->getTempPathPrevImg(), $ex->getMessage())));
                return false;
            }
        }

        $this->setPreviewWidth($dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_IMG_PREV_WIDTH));
        $this->setPreviewHeight($dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_IMG_PREV_HEIGHT));
        $this->setMainHeight($dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_IMG_MAIN_HEIGHT));
        $this->setMainWidth($dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_IMG_MAIN_WIDTH));

        $mode = $dbTSGconfig->getValue(dbTSGconfig::CFG_GAL_IMG_MODE);
        if (in_array('width', $mode) && in_array('height', $mode)) {
            $this->setOptimizeMode(self::OPTIMIZE_MODE_BOTH);
        }
        elseif (in_array('height', $mode)) {
            $this->setOptimizeMode(self::OPTIMIZE_MODE_HEIGHT);
        }
        else {
            $this->setOptimizeMode(self::OPTIMIZE_MODE_WIDTH);
        }

    } // __construct()

    /**
     * @return the $previewWidth
     */
    protected function getPreviewWidth ()
    {
        return $this->previewWidth;
    }

	/**
     * @return the $previewHeight
     */
    protected function getPreviewHeight ()
    {
        return $this->previewHeight;
    }

	/**
     * @return the $mainWidth
     */
    protected function getMainWidth ()
    {
        return $this->mainWidth;
    }

	/**
     * @return the $mainHeight
     */
    protected function getMainHeight ()
    {
        return $this->mainHeight;
    }

	/**
     * @return the $optimizeMode
     */
    protected function getOptimizeMode ()
    {
        return $this->optimizeMode;
    }

	/**
     * @param number $previewWidth
     */
    protected function setPreviewWidth ($previewWidth)
    {
        $this->previewWidth = $previewWidth;
    }

	/**
     * @param number $previewHeight
     */
    protected function setPreviewHeight ($previewHeight)
    {
        $this->previewHeight = $previewHeight;
    }

	/**
     * @param number $mainWidth
     */
    protected function setMainWidth ($mainWidth)
    {
        $this->mainWidth = $mainWidth;
    }

	/**
     * @param number $mainHeight
     */
    protected function setMainHeight ($mainHeight)
    {
        $this->mainHeight = $mainHeight;
    }

	/**
     * @param string $optimizeMode
     */
    protected function setOptimizeMode ($optimizeMode)
    {
        $this->optimizeMode = $optimizeMode;
    }

	/**
     * @return the $tempUrlMainImg
     */
    protected function getTempUrlMainImg ()
    {
        return $this->tempUrlMainImg;
    }

	/**
     * @return the $tempUrlPrevImg
     */
    protected function getTempUrlPrevImg ()
    {
        return $this->tempUrlPrevImg;
    }

	/**
     * @param string $tempUrlMainImg
     */
    protected function setTempUrlMainImg ($tempUrlMainImg)
    {
        $this->tempUrlMainImg = $tempUrlMainImg;
    }

	/**
     * @param string $tempUrlPrevImg
     */
    protected function setTempUrlPrevImg ($tempUrlPrevImg)
    {
        $this->tempUrlPrevImg = $tempUrlPrevImg;
    }

	/**
     * @return the $tempPathMainImg
     */
    protected function getTempPathMainImg ()
    {
        return $this->tempPathMainImg;
    }

	/**
     * @return the $tempPathPrevImg
     */
    protected function getTempPathPrevImg ()
    {
        return $this->tempPathPrevImg;
    }

	/**
     * @param string $tempPathMainImg
     */
    protected function setTempPathMainImg ($tempPathMainImg)
    {
        $this->tempPathMainImg = $tempPathMainImg;
    }

	/**
     * @param string $tempPathPrevImg
     */
    protected function setTempPathPrevImg ($tempPathPrevImg)
    {
        $this->tempPathPrevImg = $tempPathPrevImg;
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
     * Return the params available for the droplet [[kit_idea]] as array
     *
     * @return ARRAY $params
     */
    public function getParams ()
    {
        return $this->params;
    } // getParams()

    /**
     * Set the params for the droplet {{kit_idea]]
     *
     * @param ARRAY $params
     * @return BOOL
     */
    public function setParams($params = array()) {
        $this->params = $params;
        $this->setTemplatePath(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/'.$this->params[self::PARAM_PRESET].'/'.TS_GALLERY_LANGUAGE.'/');
        if (!file_exists($this->getTemplatePath())) {
            $this->setError(sprintf(TSG_ERROR_PRESET_NOT_EXISTS, '/modules/'.basename(dirname(__FILE__)).'/templates/'.$this->params[self::PARAM_PRESET].'/'.TS_GALLERY_LANGUAGE.'/'));
            return false;
        }
        return true;
    } // setParams()

    /**
     * Set $this->error to $error
     *
     * @param STR $error
     */
    public function setError($error) {
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
     * @return BOOL
     */
    public function isError() {
        return (bool) !empty($this->error);
    } // isError

    /**
     * Process the desired template and returns the result as string
     *
     * @param STR $template
     * @param ARRAY $template_data
     * @return STR $result
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

    public function checkPicture($origin_url, $gallery_type, &$picture_url, &$picture_width, &$picture_height) {
        global $dbTSGconfig;

        $origin_relative_path = substr($origin_url, strlen(WB_URL. MEDIA_DIRECTORY));
        $origin_path = WB_PATH.MEDIA_DIRECTORY. $origin_relative_path;

        if ($gallery_type == self::GALLERY_TYPE_MAIN) {
            $picture_path = $this->getTempPathMainImg() . $origin_relative_path;
            $picture_url = $this->getTempUrlMainImg() . $origin_relative_path;
            $cfg_width = $this->getMainWidth();
            $cfg_height = $this->getMainHeight();
        }
        else {
            // GALLERY_TYPE_PREVIEW
            $picture_path = $this->getTempPathPrevImg() . $origin_relative_path;
            $picture_url = $this->getTempUrlPrevImg() . $origin_relative_path;
            $cfg_width = $this->getPreviewWidth();
            $cfg_height = $this->getPreviewHeight();
        }

        if (!file_exists($origin_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_FILE_NOT_FOUND, $origin_path)));
            return false;
        }

        // get the origin filesize and filetime
        $origin_filemtime = filemtime($origin_path);

        if (file_exists($picture_path) && (filemtime($picture_path) == $origin_filemtime)) {
            // file already exists, get imagesize and return ...
            list($picture_width, $picture_height) = getimagesize($picture_path);
            return true;
        }

        list($origin_width, $origin_height) = getimagesize($origin_path);

        // get the new picture dimensions
        if ($this->getOptimizeMode() == self::OPTIMIZE_MODE_BOTH) {
            // dimensions are fixed
            $picture_width = $cfg_width;
            $picture_height = $cfg_height;
        }
        elseif ($this->getOptimizeMode() == self::OPTIMIZE_MODE_HEIGHT) {
            $picture_height = $cfg_height;
            if ($origin_height == $picture_height) {
                $picture_width = $origin_width;
            }
            else {
                $percent = (int) ($picture_height/($origin_height/100));
                $picture_width = (int) (($origin_width/100)*$percent);
            }
        }
        else {
            // OPTIMIZE_MODE_WIDTH
            $picture_width = $cfg_width;
            if ($origin_width == $picture_width) {
                $picture_height = $origin_height;
            }
            else {
                $percent = (int) ($picture_width/($origin_width/100));
                $picture_height = (int) (($origin_height/100)*$percent);
            }
        }
        $mediaBrowser = new mediaBrowser();
        if (false === ($path = $mediaBrowser->createTweakedFile(
                basename($origin_path),
                substr(basename($origin_path), strrpos(basename($origin_path), '.')+1),
                $origin_path,
                $picture_width,
                $picture_height,
                $origin_width,
                $origin_height,
                $origin_filemtime,
                dirname($picture_path).'/'))) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $mediaBrowser->getError()));
            return false;
        }
        return true;
    } // checkPicture()

    public function action() {
        global $dbTSGgallery;
        global $dbTSGalbum;
        global $dbTSGpicture;
        global $dbTSGconfig;
        if (empty($this->params[self::PARAM_NAME])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, TSG_ERROR_PARAM_NAME_MISSING));
            return false;
        }

        $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'", $dbTSGgallery->getTableName(), dbTSGgallery::FIELD_NAME, $this->params[self::PARAM_NAME]);
        $result = array();
        if (!$dbTSGgallery->sqlExec($SQL, $result)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGgallery->getError()));
            return false;
        }
        if (count($result) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_PARAM_NAME_INVALID, $this->params[self::PARAM_NAME])));
            return false;
        }
        $gallery = $result[0];
        $gallery_id = $gallery[dbTSGgallery::FIELD_ID];
        $albums = array();
        $where = array(dbTSGalbum::FIELD_GALLERY_ID => $gallery_id);
        if (!$dbTSGalbum->sqlSelectRecord($where, $albums)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGalbum->getError()));
            return false;
        }
        if (count($albums) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $gallery_id)));
            return false;
        }

        $images = array();
        foreach ($albums as $album) {
            // get the album picture
            $album_main_url = '';
            $album_main_width = 0;
            $album_main_height = 0;
            if (!$this->checkPicture($album[dbTSGalbum::FIELD_IMAGE_URL], self::GALLERY_TYPE_MAIN, $album_main_url, $album_main_width, $album_main_height)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                return false;
            }
            $album_prev_url = '';
            $album_prev_height = 0;
            $album_prev_width = 0;
            if (!$this->checkPicture($album[dbTSGalbum::FIELD_IMAGE_URL], self::GALLERY_TYPE_PREVIEW, $album_prev_url, $album_prev_width, $album_prev_height)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                return false;
            }

            // get the pictures
            $where = array(dbTSGpicture::FIELD_ALBUM_ID => $album[dbTSGalbum::FIELD_ID]);
            $pictures = array();
            if (!$dbTSGpicture->sqlSelectRecord($where, $pictures)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbTSGpicture->getError()));
                return false;
            }
            $picture_array = array();
            $picture_array[] = array(
                    'album_id' => $album[dbTSGalbum::FIELD_ID],
                    'main' => array(
                            'url' => $album_main_url,
                            'width' => $album_main_width,
                            'height' => $album_main_height,
                            'title' => $album[dbTSGalbum::FIELD_IMAGE_TITLE]
                    ),
                    'preview' => array(
                            'url' => $album_prev_url,
                            'width' => $album_prev_width,
                            'height' => $album_prev_height,
                            'title' => $album[dbTSGalbum::FIELD_IMAGE_TITLE]
                    ),
            );
            foreach ($pictures as $picture) {
                $main_url = '';
                $main_width = 0;
                $main_height = 0;
                if (false ===($result = $this->checkPicture($picture[dbTSGpicture::FIELD_IMAGE_URL], self::GALLERY_TYPE_MAIN, $main_url, $main_width, $main_height))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }

                $prev_url = '';
                $prev_height = 0;
                $prev_width = 0;
                if (!$this->checkPicture($picture[dbTSGpicture::FIELD_IMAGE_URL], self::GALLERY_TYPE_PREVIEW, $prev_url, $prev_width, $prev_height)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }
                $picture_array[] = array(
                        'album_id' => $album[dbTSGalbum::FIELD_ID],
                        'main' => array(
                                    'url' => $main_url,
                                    'width' => $main_width,
                                    'height' => $main_height,
                                    'title' => $picture[dbTSGpicture::FIELD_IMAGE_TITLE]
                                    ),
                        'preview' => array(
                                    'url' => $prev_url,
                                    'width' => $prev_width,
                                    'height' => $prev_height,
                                    'title' => $picture[dbTSGpicture::FIELD_IMAGE_TITLE]
                                    ),
                        );
            }


            $images[] = array(
                    'album' => array(
                            'id' => $album[dbTSGalbum::FIELD_ID],
                            'main' => array(
                                    'url' => $album_main_url,
                                    'width' => $album_main_width,
                                    'height' => $album_main_height,
                                    'title' => $album[dbTSGalbum::FIELD_IMAGE_TITLE]
                                    ),
                            'preview' => array(
                                    'url' => $album_prev_url,
                                    'width' => $album_prev_width,
                                    'height' => $album_prev_height,
                                    'title' => $album[dbTSGalbum::FIELD_IMAGE_TITLE]
                                    ),
                            'title' => $album[dbTSGalbum::FIELD_ALBUM_TITLE],
                            'description' => $album[dbTSGalbum::FIELD_ALBUM_DESCRIPTION]
                            ),
                    'pictures' => $picture_array
                    );
        }
        $gallery = array('gallery' => $images);
        $result = $this->getTemplate('gallery.lte', $gallery);
        return $this->show($result);
    } // show()

    /**
     * prompt the formatted result
     *
     * @param STR $content - content to show
     *
     * @return STR dialog
     */
    public function show($content) {
        $data = array(
                'error' => ($this->isError()) ? 1 : 0,
                'content' => ($this->isError()) ? $this->getError() : $content
        );
        return $this->getTemplate('body.lte', $data);
    } // show_main()

} // class tsgFrontend