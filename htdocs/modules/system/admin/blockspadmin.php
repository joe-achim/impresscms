<?php
/**
 * Admin ImpressCMS Block Positions
 *
 * List, add, edit and delete block positions
 *
 * @copyright	The ImpressCMS Project <http://www.impresscms.org>
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.2
 * @package 	Administration
 * @subpackage	Block Positions
 * @author		Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 * @author		Rodrigo Pereira Lima (AKA TheRplima) <therplima@impresscms.org>
 * @version		SVN: $Id: blockspadmin.php 11610 2012-02-28 03:53:55Z skenow $
 */

/* set get and post filters before including admin_header */
$id = 0;

$filter_get = array('id' => 'int');
$filter_post = array('id' => 'int');

include "admin_header.php";

/**
 * Edit a block position
 * @param $id
 */
function editblockposition($id = 0) {
	global $icms_admin_handler, $icmsAdminTpl;

	$blockObj = $icms_admin_handler->get($id);

	if (!$blockObj->isNew()) {
		$sform = $blockObj->getForm(_AM_SYSTEM_BLOCKSPADMIN_EDIT, 'addblockposition');
		$sform->assign($icmsAdminTpl);
	} else {
		$sform = $blockObj->getForm(_AM_SYSTEM_BLOCKSPADMIN_CREATE, 'addblockposition');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->assign('id', $id);
	$icmsAdminTpl->assign('lang_badmin', _AM_SYSTEM_BLOCKSPADMIN_TITLE);
	$icmsAdminTpl->display('db:admin/blockspadmin/system_adm_blockspadmin.html');
}

$clean_op = $op;
$clean_id = $id;

$valid_op = array ('mod', 'changedField', 'addblockposition', 'del', '');

if (in_array($clean_op, $valid_op, TRUE)) {

	switch ($clean_op) {
		case "mod":
		case "changedField":
			icms_cp_header();
			editblockposition($clean_id);
			break;

		case "addblockposition":
			$controller = new icms_ipf_Controller($icms_admin_handler);
			$controller->storeFromDefaultForm(_AM_SYSTEM_BLOCKSPADMIN_CREATED, _AM_SYSTEM_BLOCKSPADMIN_MODIFIED);
			break;

		case "del":
			$controller = new icms_ipf_Controller($icms_admin_handler);
			$controller->handleObjectDeletion();
			break;

		default:
			icms_cp_header();
			$objectTable = new icms_ipf_view_Table($icms_admin_handler, FALSE);

			$objectTable->addColumn(new icms_ipf_view_Column('pname'), 'center');
			$objectTable->addColumn(new icms_ipf_view_Column('title', FALSE, FALSE, 'getCustomTitle', FALSE, FALSE, FALSE));
			$objectTable->addColumn(new icms_ipf_view_Column('description'));

			$objectTable->addIntroButton('addblockposition', 'admin.php?fct=blockspadmin&amp;op=mod', _AM_SYSTEM_BLOCKSPADMIN_CREATE);
			$objectTable->addQuickSearch(array('pname', 'title', 'description'));

			$icmsAdminTpl->assign('icms_blockposition_table', $objectTable->fetch());
			$icmsAdminTpl->assign('lang_badmin', _AM_SYSTEM_BLOCKSPADMIN_TITLE);
			$icmsAdminTpl->assign('icms_blockposition_info', _AM_SYSTEM_BLOCKSPADMIN_INFO);

			$icmsAdminTpl->display('db:admin/blockspadmin/system_adm_blockspadmin.html');
			break;
	}
	icms_cp_footer();
}