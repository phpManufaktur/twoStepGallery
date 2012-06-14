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

// Module description
$module_description    = 'tsGallery - zweistufige Bildergalerie';
// name of the person(s) who translated and edited this language file
$module_translation_by = 'phpManufaktur by Ralf Hertsch';

define('TSG_BTN_NO',                            'Nein');
define('TSG_BTN_YES',                           'Ja');
define('TSG_BTN_OK',                            'OK');

define('TSG_CFG_CURRENCY',						'%s €');
define('TSG_CFG_DATE_SEPARATOR',				'.');
define('TSG_CFG_DATE_STR',						'd.m.Y');
define('TSG_CFG_DATETIME_STR',					'd.m.Y H:i');
define('TSG_CFG_DAY_NAMES',					    "Sonntag, Montag, Dienstag, Mittwoch, Donnerstag, Freitag, Samstag");
define('TSG_CFG_DECIMAL_SEPARATOR',          	',');
define('TSG_CFG_MONTH_NAMES',					"Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember");
define('TSG_CFG_THOUSAND_SEPARATOR',			'.');
define('TSG_CFG_TIME_LONG_STR',				    'H:i:s');
define('TSG_CFG_TIME_STR',						'H:i');
define('TSG_CFG_TIME_ZONE',					    'Europe/Berlin');
 
define('TSG_CONFIRM_FILE_DELETE',               '<p>Soll die Datei <b>%s</b> wirklich  gelöscht werden?</p>');
define('TSG_CONFIRM_RMDIR',                     '<p>Soll das Verzeichnis <b>%s</b> mit allen enthaltenen Dateien wirklich gelöscht werden?</p>');

define('TSG_ERROR_CFG_ID',						'<p>Der Konfigurationsdatensatz mit der <b>ID %05d</b> konnte nicht ausgelesen werden!</p>');
define('TSG_ERROR_CFG_NAME',					'<p>Zu dem Bezeichner <b>%s</b> wurde kein Konfigurationsdatensatz gefunden!</p>');
define('TSG_ERROR_CHMOD',						'<p>Die Zugriffsrechte für die Datei %s konnten nicht geändert werden!</p>');
define('TSG_ERROR_RMDIR_DIR_EMPTY',             '<p>Es wurde kein gültiges Verzeichnis übergeben!</p>');
define('TSG_ERROR_DIR_NOT_EXISTS',              '<p>Das Verzeichnis <b>%s</b> existiert nicht!</p>');
define('TSG_ERROR_FILE_DELETE',                 '<p>Die Datei <b>%s</b> konnte nicht gelöscht werden.</p>');
define('TSG_ERROR_FILE_NOT_FOUND',              '<p>Die Datei <b>%s</b> wurde nicht gefunden!</p>');
define('TSG_ERROR_MISSING_PARAMS',              '<p>Fehlender Parameter: <b>%s</b>!</p>');
define('TSG_ERROR_MKDIR',                       '<p>Das Verzeichnis<br /><b>%s</b><br />konnte nicht angelegt werden.</p><p>Fehlermeldung: <em>%s</em></p>');
define('TSG_ERROR_PARAM_NAME_INVALID',          '<p>Die im Parameter <b>name</b> angegebene Galerie <b>%s</s> wurde nicht gefunden!</p>');
define('TSG_ERROR_PARAM_NAME_MISSING',          '<p>Der Parameter <b>name</b> fehlt im Droplet, die tsGallery weiß nicht, welche Galerie geladen werden soll.</p>');
define('TSG_ERROR_PRESET_NOT_EXISTS',           '<p>Das Presetverzeichnis <b>%s</b> existiert nicht, die erforderlichen Templates können nicht geladen werden!</p>');
define('TSG_ERROR_RMDIR_DIR_INVALID',           '<p>Auf das Verzeichnis <b>%s</b> kann nicht zugriffen werden.</p>');
define('TSG_ERROR_RMDIR_DIR_NOT_READABLE',      '<p>Das Verzeichnis <b>%s</b> kann nicht eingelesen werden!</p>');
define('TSG_ERROR_RMDIR_RMDIR',                 '<p>Das Verzeichnis <b>%s</b> konnte nicht gelöscht werden.</p>');
define('TSG_ERROR_TEMPLATE_ERROR',				'<p>Fehler bei der Ausführung des Template <b>%s</b>:</p><p>%s</p>');
define('TSG_ERROR_TOUCH',						'<p>Die Modifikationszeit für die Datei %s konnte nicht gesetzt werden!</p>');
define('TSG_ERROR_TWEAK_INVALID_EXTENSION',     '<p>Die Dateiendung <b>%s</b> wird nicht unterstützt!</p>');
define('TSG_ERROR_UNDEFINED_ERROR',             '<p>Es ist ein nicht näher definierter Fehler aufgetreten, bitte informieren Sie den Support!</p>');
define('TSG_ERROR_UPLOAD_FORM_SIZE',			'<p>Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße.</p>');
define('TSG_ERROR_UPLOAD_INI_SIZE',				'<p>Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe von %s</p>');
define('TSG_ERROR_UPLOAD_MOVE_FILE',			'<p>Die Datei <b>%s</b> konnte nicht in das Zielverzeichnis verschoben werden!</p>');
define('TSG_ERROR_UPLOAD_PARTIAL',				'<p>Die Datei <b>%s</b> wurde nur teilweise hochgeladen.</p>');
define('TSG_ERROR_UPLOAD_UNDEFINED_ERROR',  	'<p>Während der Datenübertragung ist ein nicht näher beschriebener Fehler aufgetreteten.</p>');

define('TSG_HEADER_MB_DATE',                    'Datum');
define('TSG_HEADER_MB_DELETE',                  '');
define('TSG_HEADER_MB_DIMENSION',               'Maße');
define('TSG_HEADER_MB_ICON',                    '');
define('TSG_HEADER_MB_MKDIR',                   'Verzeichnis erstellen');
define('TSG_HEADER_MB_NAME',                    'Name');
define('TSG_HEADER_MB_SIZE',                    'Größe');
define('TSG_HEADER_MB_SELECT',                  '');
define('TSG_HEADER_MB_UPLOAD',                  'Datei hochladen');

define('TSG_HINT_ALBUM_CREATE',                 '');
define('TSG_HINT_CFG_GAL_DELETE_TEMP_DATA',     'Löscht alle temporären Daten der Galerie und erzwingt ein erneutes Schreiben aller optimierten Bilder (0=default, <b>1</b>=Löschen). Das Programm setzt den Löschbefehl nach Ausführung automatisch wieder auf 0 zurück.');
define('TSG_HINT_CFG_GAL_IMG_MAIN_WIDTH',       '');
define('TSG_HINT_CFG_GAL_IMG_MAIN_HEIGHT',      '');
define('TSG_HINT_CFG_GAL_IMG_MODE',             'Legt fest, welche Werte bei der Optimierung gesetzt werden sollen. Setzen Sie z.B. nur die Breite (<i>width</i>), wird die Höhe (<i>height</i>) dynamisch errechnet. Setzen Sie beide Werte (<i>width,height</i>) erzwingen Sie eine feste Ausgabegröße, die Bilder werden jedoch möglicherweise verzerrt, wenn die Proportionen nicht stimmen. Mögliche Werte: <b>width</b> (Vorgabe) <i>oder</i> <b>height</b> <i>oder</i> <b>width,height</b>.');
define('TSG_HINT_CFG_GAL_IMG_PREV_WIDTH',       '');
define('TSG_HINT_CFG_GAL_IMG_PREV_HEIGHT',      '');
define('TSG_HINT_CFG_IMAGE_EXTENSIONS',         'Dateiendungen der Grafikdateien, die von tsGallery berücksichtigt werden sollen.');
define('TSG_HINT_CFG_MB_IMG_ICON_WIDTH',        'Breite der Miniaturansicht der Bilder im Medienbrowser in Pixel');
define('TSG_HINT_CFG_MB_IMG_PREVIEW_WIDTH',     'Breite des Vorschaubild im Medienbrowser in Pixel');
define('TSG_HINT_CFG_MEDIA_DIR',                'Verzeichnis im /MEDIA Ordner, in dem sich die Bilder für die tsGallery befinden.');
define('TSG_HINT_GALLERY_DELETE',               '');
define('TSG_HINT_GALLERY_DESC',                 '');
define('TSG_HINT_GALLERY_ID',                   '');
define('TSG_HINT_GALLERY_NAME',                 'Bezeichner für die Galerie. Dieser wird im Droplet [[ts_gallery]] mit dem Parameter <i>name</i> verwendet um die Galerie auszuwählen.');
define('TSG_HINT_GALLERY_SELECT',               'Bitte wählen sie eine existierende Galerie zum Bearbeiten aus oder erstellen Sie eine neue Galerie.');

define('TSG_INTRO_GALLERY',                     'Galerie verwalten');
define('TSG_INTRO_MEDIA_BROWSER',               'Bitte wählen Sie die gewünschte(n) Bilder aus.');

define('TSG_LABEL_ALBUM_CREATE',                'Neues Album anlegen');
define('TSG_LABEL_ALBUM_DESC',                  'Album Beschreibung');
define('TSG_LABEL_ALBUM_IMAGE_CHANGE',          'Albumbild wechseln');
define('TSG_LABEL_ALBUM_IMG_TITLE',             'Albumbild Titel');
define('TSG_LABEL_ALBUM_TITLE',                 'Album Titel');
define('TSG_LABEL_CFG_DELETE_TEMP_DATA',        'Galerie, TEMP Daten löschen');
define('TSG_LABEL_CFG_GAL_IMG_MAIN_WIDTH',      'Galerie, Hauptbild, Breite');
define('TSG_LABEL_CFG_GAL_IMG_MAIN_HEIGHT',     'Galerie, Hauptbild, Höhe');
define('TSG_LABEL_CFG_GAL_IMG_MODE',            'Galerie, Optimierung');
define('TSG_LABEL_CFG_GAL_IMG_PREV_WIDTH',      'Galerie, Vorschau, Breite');
define('TSG_LABEL_CFG_GAL_IMG_PREV_HEIGHT',     'Galerie, Vorschau, Höhe');
define('TSG_LABEL_CFG_IMAGE_EXTENSIONS',        'Unterstützte Grafiktypen');
define('TSG_LABEL_CFG_MB_IMG_ICON_WIDTH',       'Media Browser, Icon Breite');
define('TSG_LABEL_CFG_MB_IMG_PREVIEW_WIDTH',    'Media Browser, Vorschau Breite');
define('TSG_LABEL_CFG_MEDIA_DIR',               'Medienverzeichnis');
define('TSG_LABEL_GALLERY_DELETE',              'Galerie löschen');
define('TSG_LABEL_GALLERY_DESC',                'Beschreibung');
define('TSG_LABEL_GALLERY_ID',                  'Galerie ID');
define('TSG_LABEL_GALLERY_NAME',                'Galerie Bezeichner');
define('TSG_LABEL_GALLERY_SELECT',              'Galerie auswählen');
define('TSG_LABEL_MB_BREADCRUMB',               'Pfad');
define('TSG_LABEL_PICTURE_ADD',                 'Bild hinzufügen');
define('TSG_LABEL_PICTURE_DELETE',              'Bild löschen');

define('TSG_MSG_ALBUM_CREATED',                 '<p>Mit dem Startbild <b>%s</b> wurde innerhalb der Galerie ein neues Album angelegt.</p>');
define('TSG_MSG_ALBUM_IMG_CHANGED',             '<p>Das Startbild <b>%s</b> wurde geändert.</p>');
define('TSG_MSG_ALBUM_DELETED',                 '<p>Die Alben mit den <b>ID\'s %s</b> wurden gelöscht.</p>');
define('TSG_MSG_ALBUM_UPDATED',                 '<p>Das Album mit der <b>ID %s</b> wurde aktualisiert.</p>');
define('TSG_MSG_FILE_DELETE_SUCCESS',           '<p>Die Datei <b>%s</b> wurde gelöscht.</p>');
define('TSG_MSG_GALLERY_DELETED',               '<p>Die Galerie mit der <b>ID %03d</b> wurde gelöscht.</p>');
define('TSG_MSG_GALLERY_INSERTED',              '<p>Es wurde eine neue Galerie erstellt.</p><p>Bitte legen Sie jetzt das erste Album in der Galerie an!</p>');
define('TSG_MSG_GALLERY_NAME_INVALID',          '<p>Der Bezeichner für die Galerie darf nicht leer sein und muss mindestens 3 Zeichen lang sein.</p>');
define('TSG_MSG_GALLERY_UPDATED',               '<p>Die Galerie wurde aktualisiert.</p>');
define('TSG_MSG_MKDIR_INVALID_DIR',             '<p>Es wurde kein verwendbarer Verzeichnisname übergeben!</p>');
define('TSG_MSG_MKDIR_SUCCESS',                 '<p>Das Verzeichnis <b>%s</b> wurde angelegt.</p>');
define('TSG_MSG_INVALID_EMAIL',					'<p>Die E-Mail Adresse <b>%s</b> ist nicht gültig, bitte prüfen Sie Ihre Eingabe.</p>');
define('TSG_MSG_PICTURE_ADDED',                 '<p>Das Bild <b>%s</b> wurde hinzugefügt.</p>');
define('TSG_MSG_PICTURE_DELETED',               '<p>Die Bilder mit den <b>ID\'s %s</b> wurden gelöscht.</p>');
define('TSG_MSG_PICTURE_UPDATED',               '<p>Das Bild <b>%s</b> wurde aktualisiert.</p>');
define('TSG_MSG_RMDIR_SUCCESS',                 '<p>Das Verzeichnis <b>%s</b> wurde gelöscht.</p>');
define('TSG_MSG_UPLOAD_INVALID_EXTENSION',      '<p>Die übertragene Datei wurde zurückgewiesen. Erlaubt sind nur Grafikdateien mit den Endungen <b>%s</b>.</p>');
define('TSG_MSG_UPLOAD_NO_FILE',                '<p>Es wurde keine Datei übertragen ...</p>'); 
define('TSG_MSG_UPLOAD_SUCCESS',				'<p>Die Datei <b>%s</b> wurde erfolgreich übertragen.</p>');

define('TSG_TITLE_CONFIRM_FILE_DELETE',         'Datei löschen');
define('TSG_TITLE_CONFIRM_RMDIR',               'Verzeichnis löschen');
define('TSG_TITLE_GALLERY',                     'Galerie');
define('TSG_TITLE_MEDIA_BROWSER',               'Medien Browser');

define('TSG_STR_ALBUM_CREATE',                  'Startbild für das Album auswählen');
define('TSG_LABEL_ALBUM_DELETE',                'Album löschen');
define('TSG_STR_ALBUM_IMG_CHANGE',              'Albumbild ändern ...');
define('TSG_STR_CHDIR',                         'Verzeichnis wechseln');
define('TSG_STR_DELETE',                        'Löschen');
define('TSG_STR_PLEASE_SELECT_GALLERY',         '- Galerie auswählen oder neue Galerie erstellen -');
define('TSG_STR_RMDIR',                         'Verzeichnis löschen');
define('TSG_STR_SELECT',                        'Auswählen');
define('TSG_STR_UNDEFINED',                     '- nicht definiert -');

define('TSG_TAB_ABOUT',                         '?');
define('TSG_TAB_CONFIG',                        'Einstellungen');
define('TSG_TAB_GALLERY',                       'Galerie');