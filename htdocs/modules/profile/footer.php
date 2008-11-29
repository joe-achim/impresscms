<?php
/**
 * Extended User Profile
 *
 *
 * @copyright       The ImpressCMS Project http://www.impresscms.org/
 * @license         LICENSE.txt
 * @license			GNU General Public License (GPL) http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package         modules
 * @since           1.2
 * @author          Jan Pedersen
 * @author          The SmartFactory <www.smartfactory.ca>
 * @author	   		Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @version         $Id$
 */

$xoopsTpl->assign("profile_adminpage", "<a href='" . ICMS_URL . "/modules/".basename( dirname( __FILE__ ) )."/admin/user.php'>" ._CO_SOBJECT_ADMIN_PAGE . "</a>");
$xoopsTpl->assign("profile_isAdmin", $profile_isAdmin);
$xoopsTpl->assign('profile_url', SMARTPROFILE_URL);
$xoopsTpl->assign('profile_images_url', SMARTPROFILE_IMAGES_URL);

$xoopsTpl->assign("xoops_module_header", '<link rel="stylesheet" type="text/css" href="' . SMARTPROFILE_URL . 'module.css" />');

$xoopsTpl->assign("ref_smartfactory", "Profile is developed by The SmartFactory (http://smartfactory.ca), a division of INBOX International (http://inboxinternational.com)");

include ICMS_ROOT_PATH.'/footer.php';
?>