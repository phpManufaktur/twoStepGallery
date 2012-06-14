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

// check for dbConnect_LE
if (!class_exists('dbconnectle')) {
    require_once WB_PATH.'/modules/dbconnect_le/include.php';
}


class dbTSGalbum extends dbConnectLE {
    
    const FIELD_ID                = 'album_id';
    const FIELD_GALLERY_ID        = 'gallery_id';
    const FIELD_IMAGE_URL         = 'album_image_url';
    const FIELD_IMAGE_TITLE       = 'album_image_title';
    const FIELD_ALBUM_TITLE       = 'album_title';
    const FIELD_ALBUM_DESCRIPTION = 'album_description';
    const FIELD_TIMESTAMP         = 'album_timestamp';
    
    private $createTable      = false;
    
    public function __construct($createTable = false) {
        $this->setCreateTable($createTable);
        parent::__construct();
        $this->setTableName('mod_ts_gallery_album');
        $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::FIELD_GALLERY_ID, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::FIELD_IMAGE_URL, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_IMAGE_TITLE, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_ALBUM_TITLE, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_ALBUM_DESCRIPTION, "VARCHAR(255) NOT NULL DEFAULT ''");
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
    
} // class dbTSGalbum