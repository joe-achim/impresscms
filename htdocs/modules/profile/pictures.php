<?php
/**
* Pictures page
*
* @copyright	GNU General Public License (GPL)
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.3
* @author		Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
* @package		profile
* @version		$Id$
*/

/**
 * Edit a Picture
 *
 * @param object $picturesObj ProfilePicture object to be edited
*/
function editpictures($picturesObj, $hideForm=false)
{
	global $profile_pictures_handler, $xoTheme, $icmsTpl, $icmsUser;

	$icmsTpl->assign('hideForm', $hideForm);
	if (!$picturesObj->isNew()){
		if (!$picturesObj->userCanEditAndDelete()) {
			redirect_header($picturesObj->getItemLink(true), 3, _NOPERM);
		}
		$picturesObj->hideFieldFromForm(array('creation_time', 'uid_owner', 'meta_keywords', 'meta_description', 'short_url'));
		$sform = $picturesObj->getSecureForm(_MD_PROFILE_PICTURES_EDIT, 'addpictures');
		$sform->assign($icmsTpl, 'profile_picturesform');
		$icmsTpl->assign('profile_category_path', $picturesObj->getVar('title') . ' > ' . _EDIT);
	} else {
		if (!$profile_pictures_handler->userCanSubmit()) {
			redirect_header(PROFILE_URL, 3, _NOPERM);
		}
		$picturesObj->setVar('uid_owner', $icmsUser->uid());
		$picturesObj->setVar('creation_time', time());
		$picturesObj->hideFieldFromForm(array('creation_time', 'uid_owner', 'meta_keywords', 'meta_description', 'short_url'));
		$sform = $picturesObj->getSecureForm(_MD_PROFILE_PICTURES_SUBMIT, 'addpictures');
		$sform->assign($icmsTpl, 'profile_picturesform');
		$icmsTpl->assign('profile_category_path', _SUBMIT);
	}

	$xoTheme->addStylesheet(ICMS_URL . '/modules/profile/module'.(( defined("_ADM_USE_RTL") && _ADM_USE_RTL )?'_rtl':'').'.css');
}


$profile_template = 'profile_pictures.html';
include_once 'header.php';

$xoTheme->addStylesheet(ICMS_LIBRARIES_URL.'/jquery/colorbox/colorbox.css');
$xoTheme->addStylesheet(ICMS_LIBRARIES_URL.'/jquery/colorbox/colorbox-custom.css');
if(ereg('msie', strtolower($_SERVER['HTTP_USER_AGENT']))) {$xoTheme->addStylesheet(ICMS_LIBRARIES_URL.'/jquery/colorbox/colorbox-custom-ie.css');}
$xoTheme->addScript(ICMS_LIBRARIES_URL.'/jquery/colorbox/colorbox.js');
$xoTheme->addScript(ICMS_LIBRARIES_URL.'/jquery/colorbox/lightbox.js');

$profile_pictures_handler = icms_getModuleHandler('pictures');

/** Use a naming convention that indicates the source of the content of the variable */
$clean_op = '';

if (isset($_GET['op'])) $clean_op = $_GET['op'];
if (isset($_POST['op'])) $clean_op = $_POST['op'];

/** Again, use a naming convention that indicates the source of the content of the variable */
global $icmsUser;
$clean_pictures_id = isset($_GET['pictures_id']) ? intval($_GET['pictures_id']) : 0 ;
$real_uid = is_object($icmsUser)?intval($icmsUser->uid()):0;
$clean_uid = isset($_GET['uid']) ? intval($_GET['uid']) : $real_uid ;
$picturesObj = $profile_pictures_handler->get($clean_pictures_id);

/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ('mod','addpictures','del','');

$isAllowed = getAllowedItems('pictures', $clean_uid);
print_r($isAllowed);
print_r($isAllowed['pictures']);
if (!$isAllowed['pictures']) {
	redirect_header(icms_getPreviousPage('index.php'), 3, _NOPERM);
}

/**
 * Only proceed if the supplied operation is a valid operation
 */
if (in_array($clean_op,$valid_op,true)){
  switch ($clean_op) {
	case "mod":
		$picturesObj = $profile_pictures_handler->get($clean_pictures_id);
		if ($clean_pictures_id > 0 && $picturesObj->isNew()) {
			redirect_header(icms_getPreviousPage('index.php'), 3, _NOPERM);
		}
		editpictures($picturesObj);
		break;

	case "addpictures":
        if (!$xoopsSecurity->check()) {
        	redirect_header(icms_getPreviousPage('index.php'), 3, _MD_PROFILE_SECURITY_CHECK_FAILED . implode('<br />', $xoopsSecurity->getErrors()));
        }
         include_once ICMS_ROOT_PATH.'/kernel/icmspersistablecontroller.php';
        $controller = new IcmsPersistableController($profile_pictures_handler);
		$controller->storeFromDefaultForm(_MD_PROFILE_PICTURES_CREATED, _MD_PROFILE_PICTURES_MODIFIED);
		break;

	case "del":
		$picturesObj = $profile_pictures_handler->get($clean_pictures_id);
		if (!$picturesObj->userCanEditAndDelete()) {
			redirect_header($picturesObj->getItemLink(true), 3, _NOPERM);
		}
		if (isset($_POST['confirm'])) {
		    if (!$xoopsSecurity->check()) {
		    	redirect_header($impresscms->urls['previouspage'], 3, _MD_PROFILE_SECURITY_CHECK_FAILED . implode('<br />', $xoopsSecurity->getErrors()));
		    }
		}
  	    include_once ICMS_ROOT_PATH.'/kernel/icmspersistablecontroller.php';
        $controller = new IcmsPersistableController($profile_pictures_handler);
		$controller->handleObjectDeletionFromUserSide();
		$icmsTpl->assign('profile_category_path', $picturesObj->getVar('title') . ' > ' . _DELETE);

		break;

	default:
		if($real_uid){
			$picturesObj = $profile_pictures_handler->get($clean_pictures_id);
			editpictures($picturesObj, true);
		}
		if($clean_pictures_id > 0){
			$profile_pictures_handler->updateCounter($clean_pictures_id);
			$icmsTpl->assign('profile_single_picture', $picturesObj->toArray());
		}elseif($clean_uid > 0){
			$picturesArray = $profile_pictures_handler->getPictures(false, false, $clean_uid);
			$icmsTpl->assign('profile_allpictures', $picturesArray);
		}elseif($real_uid > 0){
			$picturesArray = $profile_pictures_handler->getPictures(false, false, $real_uid);
			$icmsTpl->assign('profile_allpictures', $picturesArray);
		}else{
			redirect_header(PROFILE_URL);
		}


		/**
		 * Generating meta information for this page
		 */
		$icms_metagen = new IcmsMetagen($picturesObj->getVar('title'), $picturesObj->getVar('meta_keywords','n'), $picturesObj->getVar('meta_description', 'n'));
		$icms_metagen->createMetaTags();

		break;
	}
}
$icmsTpl->assign('profile_module_home', icms_getModuleName(true, true));

include_once 'footer.php';
?>