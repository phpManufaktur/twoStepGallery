//:interface to tsGallery
//:Please visit http://phpManufaktur.de for informations about tsGallery!
/**
 * twoStepGallery
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
// load the ts_gallery.preset with LibraryAdmin
include_once WB_PATH.'/modules/libraryadmin/include.php';
$new_page = includePreset( $wb_page_data, 'libraryadmin', 'ts_gallery', 'ts_gallery', NULL, false, NULL, NULL );
if ( !empty($new_page) ) {
    $wb_page_data = $new_page;
}

if (file_exists(WB_PATH.'/modules/ts_gallery/class.frontend.php')) {
	require_once(WB_PATH.'/modules/ts_gallery/class.frontend.php');
	$gallery = new tsgFrontend();
	$params = $gallery->getParams();
	$params[tsgFrontend::PARAM_PRESET] = (isset($preset)) ? (int) $preset : 1;
	$params[tsgFrontend::PARAM_CSS] = (isset($css) && (strtolower($css) == 'false')) ? false : true;
	$params[tsgFrontend::PARAM_NAME] = (isset($name)) ? $name : '';
	if (!$gallery->setParams($params)) return $gallery->getError();
	return $gallery->action();
}
else {
	return "tsGallery is not installed!";
}
