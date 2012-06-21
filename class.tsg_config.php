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

// check for dbConnect_LE
if (!class_exists('dbconnectle')) {
    require_once WB_PATH.'/modules/dbconnect_le/include.php';
}

class dbTSGconfig extends dbConnectLE {

    const FIELD_ID				= 'cfg_id';
    const FIELD_NAME			= 'cfg_name';
    const FIELD_TYPE			= 'cfg_type';
    const FIELD_VALUE			= 'cfg_value';
    const FIELD_LABEL			= 'cfg_label';
    const FIELD_DESCRIPTION		= 'cfg_desc';
    const FIELD_STATUS			= 'cfg_status';
    const FIELD_UPDATE_BY		= 'cfg_update_by';
    const FIELD_UPDATE_WHEN		= 'cfg_update_when';

    const STATUS_ACTIVE			= 1;
    const STATUS_DELETED		= 0;

    const TYPE_UNDEFINED		= 0;
    const TYPE_ARRAY			= 7;
    const TYPE_BOOLEAN			= 1;
    const TYPE_EMAIL			= 2;
    const TYPE_FLOAT			= 3;
    const TYPE_INTEGER			= 4;
    const TYPE_LIST				= 9;
    const TYPE_PATH				= 5;
    const TYPE_STRING		    = 6;
    const TYPE_URL				= 8;

    public $type_array = array(
            self::TYPE_UNDEFINED	=> '-UNDEFINED-',
            self::TYPE_ARRAY		=> 'ARRAY',
            self::TYPE_BOOLEAN		=> 'BOOLEAN',
            self::TYPE_EMAIL		=> 'E-MAIL',
            self::TYPE_FLOAT		=> 'FLOAT',
            self::TYPE_INTEGER		=> 'INTEGER',
            self::TYPE_LIST			=> 'LIST',
            self::TYPE_PATH			=> 'PATH',
            self::TYPE_STRING		=> 'STRING',
            self::TYPE_URL			=> 'URL'
    );

    private $createTable 		= false;
    private $message			= '';

    const CFG_MEDIA_DIR			    = 'cfgMediaDir';
    const CFG_IMAGE_EXTENSIONS      = 'cfgImageExtensions';
    const CFG_MB_IMG_ICON_WIDTH     = 'cfgMBimageIconWidth';
    const CFG_MB_IMG_PREVIEW_WIDTH  = 'cfgMBimagePreviewWidth';
    const CFG_GAL_IMG_MAIN_WIDTH    = 'cfgGalImgMainWidth';
    const CFG_GAL_IMG_MAIN_HEIGHT   = 'cfgGalImgMainHeight';
    const CFG_GAL_IMG_PREV_WIDTH    = 'cfgGalImgPrevWidth';
    const CFG_GAL_IMG_PREV_HEIGHT   = 'cfgGalImgPrevHeight';
    const CFG_GAL_DELETE_TEMP_DATA  = 'cfgGalDeleteTempData';
    const CFG_GAL_IMG_MODE          = 'cfgGalImgMode';

    public $config_array = array(
            array('TSG_LABEL_CFG_MEDIA_DIR', self::CFG_MEDIA_DIR, self::TYPE_STRING, '/ts_gallery', 'TSG_HINT_CFG_MEDIA_DIR'),
            array('TSG_LABEL_CFG_IMAGE_EXTENSIONS', self::CFG_IMAGE_EXTENSIONS, self::TYPE_ARRAY, 'gif,jpg,jpeg,png', 'TSG_HINT_CFG_IMAGE_EXTENSIONS'),
            array('TSG_LABEL_CFG_MB_IMG_ICON_WIDTH', self::CFG_MB_IMG_ICON_WIDTH, self::TYPE_INTEGER, '75', 'TSG_HINT_CFG_MB_IMG_ICON_WIDTH'),
            array('TSG_LABEL_CFG_MB_IMG_PREVIEW_WIDTH', self::CFG_MB_IMG_PREVIEW_WIDTH, self::TYPE_INTEGER, '250', 'TSG_HINT_CFG_MB_IMG_PREVIEW_WIDTH'),
            array('TSG_LABEL_CFG_GAL_IMG_MAIN_WIDTH', self::CFG_GAL_IMG_MAIN_WIDTH, self::TYPE_INTEGER, '800', 'TSG_HINT_CFG_GAL_IMG_MAIN_WIDTH'),
            array('TSG_LABEL_CFG_GAL_IMG_MAIN_HEIGHT', self::CFG_GAL_IMG_MAIN_HEIGHT, self::TYPE_INTEGER, '600', 'TSG_HINT_CFG_GAL_IMG_MAIN_HEIGHT'),
            array('TSG_LABEL_CFG_GAL_IMG_PREV_WIDTH', self::CFG_GAL_IMG_PREV_WIDTH, self::TYPE_INTEGER, '95', 'TSG_HINT_CFG_GAL_IMG_PREV_WIDTH'),
            array('TSG_LABEL_CFG_GAL_IMG_PREV_HEIGHT', self::CFG_GAL_IMG_PREV_HEIGHT, self::TYPE_INTEGER, '60', 'TSG_HINT_CFG_GAL_IMG_PREV_HEIGHT'),
            array('TSG_LABEL_CFG_DELETE_TEMP_DATA', self::CFG_GAL_DELETE_TEMP_DATA, self::TYPE_BOOLEAN, '0', 'TSG_HINT_CFG_GAL_DELETE_TEMP_DATA'),
            array('TSG_LABEL_CFG_GAL_IMG_MODE', self::CFG_GAL_IMG_MODE, self::TYPE_ARRAY, 'width', 'TSG_HINT_CFG_GAL_IMG_MODE')
    );

    /**
     * Constructor
     *
     * @param boolean $createTables - true, if the table should be created
     */
    public function __construct($createTable = false) {
        $this->setCreateTable($createTable);
        parent::__construct();
        $this->setTableName('mod_ts_gallery_config');
        $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::FIELD_NAME, "VARCHAR(32) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_TYPE, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::TYPE_UNDEFINED."'");
        $this->addFieldDefinition(self::FIELD_VALUE, "TEXT NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::FIELD_LABEL, "VARCHAR(64) NOT NULL DEFAULT 'TSG_STR_UNDEFINED'");
        $this->addFieldDefinition(self::FIELD_DESCRIPTION, "VARCHAR(255) NOT NULL DEFAULT 'TSG_STR_UNDEFINED'");
        $this->addFieldDefinition(self::FIELD_STATUS, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::STATUS_ACTIVE."'");
        $this->addFieldDefinition(self::FIELD_UPDATE_BY, "VARCHAR(32) NOT NULL DEFAULT 'SYSTEM'");
        $this->addFieldDefinition(self::FIELD_UPDATE_WHEN, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
        $this->setIndexFields(array(self::FIELD_NAME));
        $this->setAllowedHTMLtags('<a><abbr><acronym><span>');
        $this->checkFieldDefinitions();
        // create table
        if ($this->getCreateTable()) {
            if (!$this->sqlTableExists()) {
                if (!$this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        // grant default values
        if ($this->sqlTableExists()) {
            $this->checkConfig();
        }
        // set the correct time zone!
        date_default_timezone_set(TSG_CFG_TIME_ZONE);
    } // __construct()

    /**
     * @return the $createTables
     */
    protected function getCreateTable ()
    {
        return $this->createTables;
    }

	/**
     * @param boolean $createTables
     */
    protected function setCreateTable ($createTable)
    {
        $this->createTables = $createTable;
    }

    /**
     * Set the message
     *
     * @param string $message
     */
	public function setMessage($message) {
        $this->message = $message;
    } // setMessage()

    /**
     * Get Message from $this->message;
     *
     * @return STR $this->message
     */
    public function getMessage() {
        return $this->message;
    } // getMessage()

    /**
     * Check if $this->message is empty
     *
     * @return BOOL
     */
    public function isMessage() {
        return (bool) !empty($this->message);
    } // isMessage

    /**
     * set the value $new_value to the record with the name $name
     *
     * @param string $new_value - the new value
     * @param integer $name - name of the record, self::CFG_ ...
     *
     * @return boolean
     *
     */
    public function setValueByName($new_value, $name) {
        $where = array();
        $where[self::FIELD_NAME] = $name;
        $config = array();
        if (!$this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_CFG_NAME, $name)));
            return false;
        }
        return $this->setValue($new_value, $config[0][self::FIELD_ID]);
    } // setValueByName()

    /**
     * Adds a slash to the end of the desired string if no slash is at the end
     *
     * @param string $path
     * @return string $path
     */
    public function addSlash($path) {
        $path = substr($path, strlen($path)-1, 1) == "/" ? $path : $path."/";
        return $path;
    } // addSlash()

    /**
     * Change a string to a float value, uses the country definitions from the
     * language file
     *
     * @param string $string
     * @return float
     */
    public function str2float($string) {
        $string = str_replace(TSG_CFG_THOUSAND_SEPARATOR, '', $string);
        $string = str_replace(TSG_CFG_DECIMAL_SEPARATOR, '.', $string);
        $float = floatval($string);
        return $float;
    } // str2float()

    /**
     * Change a string to a integer value, uses the country definitions from the
     * language file
     *
     * @param string $string
     * @return integer
     */
    public function str2int($string) {
        $string = str_replace(TSG_CFG_THOUSAND_SEPARATOR, '', $string);
        $string = str_replace(TSG_CFG_DECIMAL_SEPARATOR, '.', $string);
        $int = intval($string);
        return $int;
    } // str2int()

    /**
     * Checks the desired email address for logical errors
     *
     * @param string $email
     * @return boolean
     */
    public function validateEMail($email) {
        if(preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
            return true;
        }
        else {
            return false;
        }
    } // validateEMail()

    /**
     * set the value $new_value to the record with the ID $id
     *
     * @param string $new_value - new value
     * @param integer $id - ID of the record
     *
     * @return boolean
     */
    public function setValue($new_value, $id) {
        $value = '';
        $where = array();
        $where[self::FIELD_ID] = $id;
        $config = array();
        if (!$this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_CFG_ID, $id)));
            return false;
        }
        $config = $config[0];
        switch ($config[self::FIELD_TYPE]):
        case self::TYPE_ARRAY:
            // the function expects the value $value as string!
            $worker = explode(",", $new_value);
            $data = array();
            foreach ($worker as $item) {
                $data[] = trim($item);
            };
            $value = implode(",", $data);
            break;
        case self::TYPE_BOOLEAN:
            $value = (bool) $new_value;
            $value = (int) $value;
            break;
        case self::TYPE_EMAIL:
            if ($this->validateEMail($new_value)) {
                $value = trim($new_value);
            }
            else {
                $this->setMessage(sprintf(TSG_MSG_INVALID_EMAIL, $new_value));
                return false;
            }
            break;
        case self::TYPE_FLOAT:
            $value = $this->str2float($new_value);
            break;
        case self::TYPE_INTEGER:
            $value = $this->str2int($new_value);
            break;
        case self::TYPE_URL:
        case self::TYPE_PATH:
            $value = $this->addSlash(trim($new_value));
            break;
        case self::TYPE_STRING:
            $value = (string) trim($new_value);
            // demask quots
            $value = str_replace('&quot;', '"', $value);
            break;
        case self::TYPE_LIST:
            $lines = nl2br($new_value);
            $lines = explode('<br />', $lines);
            $val = array();
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) $val[] = $line;
            }
            $value = implode(",", $val);
            break;
        endswitch;

        unset($config[self::FIELD_ID]);
        $config[self::FIELD_VALUE] = (string) $value;
        $config[self::FIELD_UPDATE_BY] = 'SYSTEM';
        $config[self::FIELD_UPDATE_WHEN] = date('Y-m-d H:i:s');
        if (!$this->sqlUpdateRecord($config, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        return true;
    } // setValue()

    /**
     * Returns the value of the record with the name $name.
     * This function format the returned value in the expected type, i.e. as
     * array or float or string ...
     *
     * @param string $name
     * @return mixed $value as the desired type
     */
    public function getValue($name) {
        $result = '';
        $where = array();
        $where[self::FIELD_NAME] = $name;
        $config = array();
        if (!$this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_CFG_NAME, $name)));
            return false;
        }
        $config = $config[0];
        switch ($config[self::FIELD_TYPE]):
        case self::TYPE_ARRAY:
            $result = explode(",", $config[self::FIELD_VALUE]);
            break;
        case self::TYPE_BOOLEAN:
            $result = (bool) $config[self::FIELD_VALUE];
            break;
        case self::TYPE_EMAIL:
        case self::TYPE_PATH:
        case self::TYPE_STRING:
        case self::TYPE_URL:
            $result = (string) utf8_decode($config[self::FIELD_VALUE]);
            break;
        case self::TYPE_FLOAT:
            $result = (float) $config[self::FIELD_VALUE];
            break;
        case self::TYPE_INTEGER:
            $result = (integer) $config[self::FIELD_VALUE];
            break;
        case self::TYPE_LIST:
            $result = str_replace(",", "\n", $config[self::FIELD_VALUE]);
            break;
        default:
            $result = utf8_decode($config[self::FIELD_VALUE]);
        break;
        endswitch;
        return $result;
    } // getValue()

    /**
     * Checks the configuration and set the default values if they not exists
     *
     * @return boolean
     */
    public function checkConfig() {
        foreach ($this->config_array as $item) {
            $where = array();
            $where[self::FIELD_NAME] = $item[1];
            $check = array();
            if (!$this->sqlSelectRecord($where, $check)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                return false;
            }
            if (sizeof($check) < 1) {
                // record does not exist, create default
                $data = array();
                $data[self::FIELD_LABEL] = $item[0];
                $data[self::FIELD_NAME] = $item[1];
                $data[self::FIELD_TYPE] = $item[2];
                $data[self::FIELD_VALUE] = $item[3];
                $data[self::FIELD_DESCRIPTION] = $item[4];
                $data[self::FIELD_UPDATE_WHEN] = date('Y-m-d H:i:s');
                $data[self::FIELD_UPDATE_BY] = 'SYSTEM';
                if (!$this->sqlInsertRecord($data)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }
            }
        }
        return true;
    } // checkConfig()

} // class dbTSGconfig