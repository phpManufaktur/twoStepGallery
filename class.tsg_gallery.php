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


class dbTSGgallery extends dbConnectLE {

    const FIELD_ID            = 'gallery_id';
    const FIELD_NAME          = 'gallery_name';
    const FIELD_DESCRIPTION   = 'gallery_desc';
    const FIELD_TIMESTAMP     = 'gallery_timestamp';

    private $createTable      = false;

    public function __construct($createTable = false) {
        $this->setCreateTable($createTable);
        parent::__construct();
        $this->setTableName('mod_ts_gallery_gallery');
        $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::FIELD_NAME, "VARCHAR(80) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_DESCRIPTION, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // create table
        if ($this->getCreateTable()) {
            if (!$this->sqlTableExists()) {
                if (!$this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        // set the correct time zone!
        date_default_timezone_set(TSG_CFG_TIME_ZONE);
    } // __construct()

	/**
     * @return the $createTable
     */
    protected function getCreateTable ()
    {
        return $this->createTable;
    }

	/**
     * @param boolean $createTable
     */
    protected function setCreateTable ($createTable)
    {
        $this->createTable = $createTable;
    }

} // class dbTSGgallery