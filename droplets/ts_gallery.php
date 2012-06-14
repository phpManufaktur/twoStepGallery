//:interface to tsGallery
//:Please visit http://phpManufaktur.de for informations about tsGallery!
/**
 * tsGallery
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */
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
