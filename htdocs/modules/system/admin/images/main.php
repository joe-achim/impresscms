<?php
// $Id: main.php 2 2005-11-02 18:23:29Z skalpa $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
// Restructured by: Rodrigo P. Lima (AKA TheRplima)                          //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

if ( !is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid()) ) {
    exit("Access Denied");
} else {
	
	/**
	 * Instanciating template engine to use in image manager
	 */
	//include_once XOOPS_ROOT_PATH.'/class/template.php';
	//$tpl = new XoopsTpl();
	define('_IMANAGER_TPL_PATH',XOOPS_ROOT_PATH.'/modules/system/templates/admin/images');
	
    $op = 'list';
    if (isset($_POST)) {
        foreach ( $_POST as $k => $v ) {
            ${$k} = $v;
        }
    }
    if (isset($_GET['op'])) {
        $op = trim($_GET['op']);
    }
    if (isset($_GET['image_id'])) {
        $image_id = intval($_GET['image_id']);
    }
    if (isset($_GET['imgcat_id'])) {
        $imgcat_id = intval($_GET['imgcat_id']);
    }
     
    switch ($op){
    	case 'list':
    		xoops_cp_header();
    		echo imanager_index();
    		xoops_cp_footer();
    		break;
    	case 'listimg':
    		xoops_cp_header();
    		echo imanager_listimg($imgcat_id);
    		xoops_cp_footer();
    		break;
    	case 'addcat':
    		imanager_addcat();
    		break;
    	case 'editcat':
    		imanager_editcat($imgcat_id);
    		xoops_cp_footer();
    		break;
    	case 'updatecat':
    		imanager_updatecat();
    		break;
    	case 'delcat':
    		xoops_cp_header();
    		xoops_confirm(array('op' => 'delcatok', 'imgcat_id' => $imgcat_id, 'fct' => 'images'), 'admin.php', _MD_RUDELIMGCAT);
    		xoops_cp_footer();
    		break;
    	case 'delcatok':
    		imanager_delcatok($imgcat_id);
    		break;
    	case 'reordercateg':
    		imanager_reordercateg();
    		break;
    	case 'addfile':
    		imanager_addfile();
    		break;
    	case 'save':
    		imanager_updateimage();
    		break;
    	case 'delfile':
    		xoops_cp_header();
    		$image_handler = xoops_gethandler('image');
    		$image =& $image_handler->get($image_id);
    		$imgcat_handler = xoops_gethandler('imagecategory');
    		$imagecategory =& $imgcat_handler->get($image->getVar('imgcat_id'));
    		echo '<div style="margin:5px;" align="center">';
    		if ($imagecategory->getVar('imgcat_storetype') == 'db') {
    			echo '<img src="'.XOOPS_URL.'/image.php?id='.$image->getVar('image_id').'" title="'.$image->getVar('image_nicename').'" /><br />';
    		} else {
    			echo '<img src="'.XOOPS_UPLOAD_URL.'/'.$image->getVar('image_name').'" title="'.$image->getVar('image_nicename').'" /><br />';
    		}
    		echo '</div>';
    		xoops_confirm(array('op' => 'delfileok', 'image_id' => $image_id, 'imgcat_id' => $imgcat_id, 'fct' => 'images'), 'admin.php', _MD_RUDELIMG);
    		xoops_cp_footer();
    		break;
    	case 'delfileok':
    		imanager_delfileok($image_id,$imgcat_id);
    		break;
    	case 'cropimg':
    		imanager_cropimg();
    		break;
    	case 'filter':
    		imanager_filter();
    		break;
    	case 'cloneimg':
    		imanager_clone();
    		break;
    }
}



function imanager_index(){
	global $tpl;

	$imgcat_handler = xoops_gethandler('imagecategory');
	$imagecategorys =& $imgcat_handler->getObjects();

	$tpl->assign('lang_imanager_title',_IMGMANAGER);
	$tpl->assign('lang_imanager_catid',_MD_IMAGECATID);
	$tpl->assign('lang_imanager_catname',_MD_IMAGECATNAME);
	$tpl->assign('lang_imanager_catmsize',_MD_IMAGECATMSIZE);
	$tpl->assign('lang_imanager_catmwidth',_MD_IMAGECATMWIDTH);
	$tpl->assign('lang_imanager_catmheight',_MD_IMAGECATMHEIGHT);
	$tpl->assign('lang_imanager_catstype',_MD_IMAGECATSTYPE);
	$tpl->assign('lang_imanager_catdisp',_MD_IMAGECATDISP);
	$tpl->assign('lang_imanager_catautoresize',_MD_IMAGECATATUORESIZE);
	$tpl->assign('lang_imanager_catweight',_MD_IMAGECATWEIGHT);
	$tpl->assign('lang_imanager_catqtde',_MD_IMAGECATQTDE);
	$tpl->assign('lang_imanager_catoptions',_MD_IMAGECATOPTIONS);

	$tpl->assign('lang_imanager_cat_edit',_EDIT);
	$tpl->assign('lang_imanager_cat_del',_DELETE);
	$tpl->assign('lang_imanager_cat_listimg',_LIST);
	$tpl->assign('lang_imanager_cat_submit',_SUBMIT);
	
	$tpl->assign('lang_imanager_cat_addnewcat',_MD_ADDIMGCATBTN);
	$tpl->assign('lang_imanager_cat_addnewimg',_MD_ADDIMGBTN);

	$tpl->assign('token',$GLOBALS['xoopsSecurity']->getTokenHTML());
	$tpl->assign('catcount',count($imagecategorys));

	$tpl->assign('imagecategorys',$imagecategorys);

	$image_handler =& xoops_gethandler('image');
	$count = $msize = array();
	$tpl->assign('catcount',$catcount = count($imagecategorys));
	for ($i = 0; $i < $catcount; $i++) {
		$msize[$i] = icms_convert_size($imagecategorys[$i]->getVar('imgcat_maxsize'));
		$count[$i] = $image_handler->getCount(new Criteria('imgcat_id', $imagecategorys[$i]->getVar('imgcat_id')));
	}
	$tpl->assign('msize',$msize);
	$tpl->assign('count',$count);

	include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
	if (!empty($catcount)) {
		$form = new XoopsThemeForm(_ADDIMAGE, 'image_form', 'admin.php', 'post', true);
		$form->setExtra('enctype="multipart/form-data"');
		$form->addElement(new XoopsFormText(_IMAGENAME, 'image_nicename', 50, 255), true);
		$select = new XoopsFormSelect(_IMAGECAT, 'imgcat_id');
		$select->addOptionArray($imgcat_handler->getList());
		$form->addElement($select, true);
		$form->addElement(new XoopsFormFile(_IMAGEFILE, 'image_file', 5000000));
		$form->addElement(new XoopsFormText(_IMGWEIGHT, 'image_weight', 3, 4, 0));
		$form->addElement(new XoopsFormRadioYN(_IMGDISPLAY, 'image_display', 1, _YES, _NO));
		$form->addElement(new XoopsFormHidden('op', 'addfile'));
		$form->addElement(new XoopsFormHidden('fct', 'images'));
		$tray = new XoopsFormElementTray('' ,'');
		$tray->addElement(new XoopsFormButton('', 'img_button', _SUBMIT, 'submit'));
		$btn = new XoopsFormButton('', 'reset', _CANCEL, 'button');
		$btn->setExtra('onclick="document.getElementById(\'addimgform\').style.display = \'none\'; return false;"');
		$tray->addElement($btn);
		$form->addElement($tray);
		$tpl->assign('addimgform',$form->render());
	}
	$form = new XoopsThemeForm(_MD_ADDIMGCAT, 'imagecat_form', 'admin.php', 'post', true);
	$form->addElement(new XoopsFormText(_MD_IMGCATNAME, 'imgcat_name', 50, 255), true);
	$form->addElement(new XoopsFormSelectGroup(_MD_IMGCATRGRP, 'readgroup', true, XOOPS_GROUP_ADMIN, 5, true));
	$form->addElement(new XoopsFormSelectGroup(_MD_IMGCATWGRP, 'writegroup', true, XOOPS_GROUP_ADMIN, 5, true));
	$form->addElement(new XoopsFormText(_IMGMAXSIZE, 'imgcat_maxsize', 10, 10, 50000));
	$form->addElement(new XoopsFormText(_IMGMAXWIDTH, 'imgcat_maxwidth', 3, 4, 120));
	$form->addElement(new XoopsFormText(_IMGMAXHEIGHT, 'imgcat_maxheight', 3, 4, 120));
	$form->addElement(new XoopsFormText(_MD_IMGCATWEIGHT, 'imgcat_weight', 3, 4, 0));
	$form->addElement(new XoopsFormRadioYN(_MD_IMGCATDISPLAY, 'imgcat_display', 1, _YES, _NO));
	$storetype = new XoopsFormRadio(_MD_IMGCATSTRTYPE.'<br /><span style="color:#ff0000;">'._MD_STRTYOPENG.'</span>', 'imgcat_storetype', 'file');
	$storetype->addOptionArray(array('file' => _MD_ASFILE, 'db' => _MD_INDB));
	$form->addElement($storetype);
	$form->addElement(new XoopsFormHidden('op', 'addcat'));
	$form->addElement(new XoopsFormHidden('fct', 'images'));
	$tray1 = new XoopsFormElementTray('' ,'');
	$tray1->addElement(new XoopsFormButton('', 'imgcat_button', _SUBMIT, 'submit'));
	$btn = new XoopsFormButton('', 'reset', _CANCEL, 'button');
	$btn->setExtra('onclick="document.getElementById(\'addcatform\').style.display = \'none\'; return false;"');
	$tray1->addElement($btn);
	$form->addElement($tray1);
	$tpl->assign('addcatform',$form->render());

	return $tpl->fetch(_IMANAGER_TPL_PATH.'/tpl_imanager_index.html');
}

function imanager_listimg($imgcat_id) {
	global $tpl;

	$imgcat_id = intval($imgcat_id);
	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	$query = isset($_POST['query']) ? $_POST['query'] : null;

	if ($imgcat_id <= 0) {
		redirect_header('admin.php?fct=images',1);
	}
	$imgcat_handler = xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get($imgcat_id);
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}

	$tpl->assign('lang_imanager_title',sprintf(_MD_IMAGESIN,$imagecategory->getVar('imgcat_name')));
	$tpl->assign('lang_imanager_catmsize',_MD_IMAGECATMSIZE);
	$tpl->assign('lang_imanager_catmwidth',_MD_IMAGECATMWIDTH);
	$tpl->assign('lang_imanager_catmheight',_MD_IMAGECATMHEIGHT);
	$tpl->assign('lang_imanager_catstype',_MD_IMAGECATSTYPE);
	$tpl->assign('lang_imanager_catdisp',_MD_IMAGECATDISP);
	$tpl->assign('lang_imanager_catqtde',_MD_IMAGECATQTDE);
	$tpl->assign('lang_imanager_catoptions',_MD_IMAGECATOPTIONS);

	$tpl->assign('lang_imanager_cat_edit',_EDIT);
	$tpl->assign('lang_imanager_cat_clone',_CLONE);
	$tpl->assign('lang_imanager_cat_del',_DELETE);
	$tpl->assign('lang_imanager_cat_listimg',_LIST);
	$tpl->assign('lang_imanager_cat_submit',_SUBMIT);
	$tpl->assign('lang_imanager_cat_back',_BACK);
	$tpl->assign('lang_imanager_cat_addimg',_ADDIMAGE);
	
	$tpl->assign('lang_imanager_cat_addnewcat',_MD_ADDIMGCATBTN);
	$tpl->assign('lang_imanager_cat_addnewimg',_MD_ADDIMGBTN);

	$tpl->assign('cat_maxsize',icms_convert_size($imagecategory->getVar('imgcat_maxsize')));
	$tpl->assign('cat_maxwidth',$imagecategory->getVar('imgcat_maxwidth'));
	$tpl->assign('cat_maxheight',$imagecategory->getVar('imgcat_maxheight'));
	$tpl->assign('cat_storetype',$imagecategory->getVar('imgcat_storetype'));
	$tpl->assign('cat_display',$imagecategory->getVar('imgcat_display'));
	$tpl->assign('cat_id',$imagecategory->getVar('imgcat_id'));
	
	$tpl->assign('lang_imanager_img_preview',_PREVIEW);
	
	$tpl->assign('lang_image_name',_IMAGENAME);
	$tpl->assign('lang_image_mimetype',_IMAGEMIME);
	$tpl->assign('lang_image_cat',_IMAGECAT);
	$tpl->assign('lang_image_weight',_IMGWEIGHT);
	$tpl->assign('lang_image_disp',_IMGDISPLAY);
	$tpl->assign('lang_submit',_SUBMIT);
	$tpl->assign('lang_cancel',_CANCEL);
	$tpl->assign('lang_yes',_YES);
	$tpl->assign('lang_no',_NO);
	$tpl->assign('lang_search',_SEARCH);
	
	$tpl->assign('xoops_root_path',XOOPS_ROOT_PATH);
	$tpl->assign('query',$query);
	
	$image_handler = xoops_gethandler('image');
	$criteria = new CriteriaCompo(new Criteria('imgcat_id', $imgcat_id));
	if (!is_null($query)){
		$criteria->add(new Criteria('image_nicename', $query.'%','LIKE'));
	}
	$imgcount = $image_handler->getCount($criteria);
	$criteria->setStart($start);
	$criteria->setOrder('DESC');
	$criteria->setSort('image_weight');
	$criteria->setLimit(15);
	$images =& $image_handler->getObjects($criteria, true, false);

	$tpl->assign('imgcount',$imgcount);
	//$tpl->assign('images',$images);

	$arrimg = array();
    foreach (array_keys($images) as $i) {
		$arrimg[$i]['id'] = $images[$i]->getVar('image_id');
		$arrimg[$i]['name'] = $images[$i]->getVar('image_name');
		$arrimg[$i]['nicename'] = $images[$i]->getVar('image_nicename');
		$arrimg[$i]['mimetype'] = $images[$i]->getVar('image_mimetype');
		$arrimg[$i]['weight'] = $images[$i]->getVar('image_weight');
		$arrimg[$i]['display'] = $images[$i]->getVar('image_display');
		$arrimg[$i]['categ_id'] = $images[$i]->getVar('imgcat_id');
		$arrimg[$i]['display_nicename'] = xoops_substr($images[$i]->getVar('image_nicename'),0,20);
		
    	$imginfo = ($imagecategory->getVar('imgcat_storetype') != 'db')?$images[$i]->getInfo(XOOPS_UPLOAD_URL,'url',true):$images[$i]->getInfo(XOOPS_URL.'/image.php?id='.$i,'db',true);
    	$arrimg[$i]['width'] = $imginfo['width'];
    	$arrimg[$i]['height'] = $imginfo['height'];
    	
		if ($imagecategory->getVar('imgcat_storetype') == 'db') {
			$src = XOOPS_URL.'/image.php?id='.$i;
			include_once XOOPS_ROOT_PATH.'/class/image.class.php';
			$newimage = Image::open($src);
			$newimage->save(XOOPS_UPLOAD_PATH.'/'.$images[$i]->getVar('image_name'));
            $arrimg[$i]['size'] = icms_convert_size(filesize(XOOPS_UPLOAD_PATH.'/'.$images[$i]->getVar('image_name')));
			@unlink(XOOPS_UPLOAD_PATH.'/'.$images[$i]->getVar('image_name'));
		} else {
			$src = XOOPS_UPLOAD_URL.'/'.$images[$i]->getVar('image_name');
			$src1 = XOOPS_ROOT_PATH.'/uploads/'.$images[$i]->getVar('image_name');
			$arrimg[$i]['size'] = icms_convert_size(filesize($src1));
		}
		$arrimg[$i]['src'] = $src.'?'.time();

		$preview_url = '<a href="'.$src.'" rel="lightbox[categ'.$images[$i]->getVar('imgcat_id').']" title="'.$images[$i]->getVar('image_nicename').'"><img src="images/view.png" title="'._PREVIEW.'" alt="'._PREVIEW.'" /></a>';
		$arrimg[$i]['preview_link'] = $preview_url;

		$list =& $imgcat_handler->getList(array(), null, null, $imagecategory->getVar('imgcat_storetype'));
		$div = '';
		foreach ($list as $value => $name) {
			$sel = '';
			if ($value == $images[$i]->getVar('imgcat_id')) {
				$sel = ' selected="selected"';
			}
			$div .= '<option value="'.$value.'"'.$sel.'>'.$name.'</option>';
		}
		$arrimg[$i]['ed_selcat_options'] = $div;
		
		$arrimg[$i]['ed_token'] = $GLOBALS['xoopsSecurity']->getTokenHTML();
		$arrimg[$i]['clone_token'] = $GLOBALS['xoopsSecurity']->getTokenHTML();
    }
    
	$tpl->assign('images',$arrimg);
	if ($imgcount > 0) {
		if ($imgcount > 15) {
			include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
			$nav = new XoopsPageNav($imgcount, 15, $start, 'start', 'fct=images&amp;op=listimg&amp;imgcat_id='.$imgcat_id);
			$tpl->assign('pag','<div class="img_list_info_panel" align="center">'.$nav->renderNav().'</div>');
		}else{
		    $tpl->assign('pag','');
	    }
	}else{
		$tpl->assign('pag','');
	}
	$tpl->assign('addimgform',showAddImgForm($imgcat_id));
	
	return $tpl->fetch(_IMANAGER_TPL_PATH.'/tpl_imanager_imglist.html');
}

function imanager_addcat() {
    if (isset($_POST)) {
        foreach ( $_POST as $k => $v ) {
            ${$k} = $v;
        }
    }
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$imgcat_handler =& xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->create();
	$imagecategory->setVar('imgcat_name', $imgcat_name);
	$imagecategory->setVar('imgcat_maxsize', $imgcat_maxsize);
	$imagecategory->setVar('imgcat_maxwidth', $imgcat_maxwidth);
	$imagecategory->setVar('imgcat_maxheight', $imgcat_maxheight);
	$imgcat_display = empty($imgcat_display) ? 0 : 1;
	$imagecategory->setVar('imgcat_display', $imgcat_display);
	$imagecategory->setVar('imgcat_weight', $imgcat_weight);
	$imagecategory->setVar('imgcat_storetype', $imgcat_storetype);
	$imagecategory->setVar('imgcat_type', 'C');
	if (!$imgcat_handler->insert($imagecategory)) {
		exit();
	}
	$newid = $imagecategory->getVar('imgcat_id');
	$imagecategoryperm_handler =& xoops_gethandler('groupperm');
	if (!isset($readgroup)) {
		$readgroup = array();
	}
	if (!in_array(XOOPS_GROUP_ADMIN, $readgroup)) {
		array_push($readgroup, XOOPS_GROUP_ADMIN);
	}
	foreach ($readgroup as $rgroup) {
		$imagecategoryperm =& $imagecategoryperm_handler->create();
		$imagecategoryperm->setVar('gperm_groupid', $rgroup);
		$imagecategoryperm->setVar('gperm_itemid', $newid);
		$imagecategoryperm->setVar('gperm_name', 'imgcat_read');
		$imagecategoryperm->setVar('gperm_modid', 1);
		$imagecategoryperm_handler->insert($imagecategoryperm);
		unset($imagecategoryperm);
	}
	if (!isset($writegroup)) {
		$writegroup = array();
	}
	if (!in_array(XOOPS_GROUP_ADMIN, $writegroup)) {
		array_push($writegroup, XOOPS_GROUP_ADMIN);
	}
	foreach ($writegroup as $wgroup) {
		$imagecategoryperm =& $imagecategoryperm_handler->create();
		$imagecategoryperm->setVar('gperm_groupid', $wgroup);
		$imagecategoryperm->setVar('gperm_itemid', $newid);
		$imagecategoryperm->setVar('gperm_name', 'imgcat_write');
		$imagecategoryperm->setVar('gperm_modid', 1);
		$imagecategoryperm_handler->insert($imagecategoryperm);
		unset($imagecategoryperm);
	}
	redirect_header('admin.php?fct=images',2,_MD_AM_DBUPDATED);
}

function imanager_editcat($imgcat_id){
	if ($imgcat_id <= 0) {
		redirect_header('admin.php?fct=images',1);
	}
	$imgcat_handler = xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get($imgcat_id);
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}
	include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
	$imagecategoryperm_handler =& xoops_gethandler('groupperm');
	$form = new XoopsThemeForm(_MD_EDITIMGCAT, 'imagecat_form', 'admin.php', 'post', true);
	$form->addElement(new XoopsFormText(_MD_IMGCATNAME, 'imgcat_name', 50, 255, $imagecategory->getVar('imgcat_name')), true);
	$form->addElement(new XoopsFormSelectGroup(_MD_IMGCATRGRP, 'readgroup', true, $imagecategoryperm_handler->getGroupIds('imgcat_read', $imgcat_id), 5, true));
	$form->addElement(new XoopsFormSelectGroup(_MD_IMGCATWGRP, 'writegroup', true, $imagecategoryperm_handler->getGroupIds('imgcat_write', $imgcat_id), 5, true));
	$form->addElement(new XoopsFormText(_IMGMAXSIZE, 'imgcat_maxsize', 10, 10, $imagecategory->getVar('imgcat_maxsize')));
	$form->addElement(new XoopsFormText(_IMGMAXWIDTH, 'imgcat_maxwidth', 3, 4, $imagecategory->getVar('imgcat_maxwidth')));
	$form->addElement(new XoopsFormText(_IMGMAXHEIGHT, 'imgcat_maxheight', 3, 4, $imagecategory->getVar('imgcat_maxheight')));
	$form->addElement(new XoopsFormText(_MD_IMGCATWEIGHT, 'imgcat_weight', 3, 4, $imagecategory->getVar('imgcat_weight')));
	$form->addElement(new XoopsFormRadioYN(_MD_IMGCATDISPLAY, 'imgcat_display', $imagecategory->getVar('imgcat_display'), _YES, _NO));
	$storetype = array('db' => _MD_INDB, 'file' => _MD_ASFILE);
	$form->addElement(new XoopsFormLabel(_MD_IMGCATSTRTYPE, $storetype[$imagecategory->getVar('imgcat_storetype')]));
	$form->addElement(new XoopsFormHidden('imgcat_id', $imgcat_id));
	$form->addElement(new XoopsFormHidden('op', 'updatecat'));
	$form->addElement(new XoopsFormHidden('fct', 'images'));
	$form->addElement(new XoopsFormButton('', 'imgcat_button', _SUBMIT, 'submit'));
	xoops_cp_header();
	echo '<a href="admin.php?fct=images">'. _MD_IMGMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'.$imagecategory->getVar('imgcat_name').'<br /><br />';
	$form->display();
}

function imanager_updatecat() {
    if (isset($_POST)) {
        foreach ( $_POST as $k => $v ) {
            ${$k} = $v;
        }
    }
	
	if (!$GLOBALS['xoopsSecurity']->check() || $imgcat_id <= 0) {
		redirect_header('admin.php?fct=images',1, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$imgcat_handler = xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get($imgcat_id);
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}
	$imagecategory->setVar('imgcat_name', $imgcat_name);
	$imgcat_display = empty($imgcat_display) ? 0 : 1;
	$imagecategory->setVar('imgcat_display', $imgcat_display);
	$imagecategory->setVar('imgcat_maxsize', $imgcat_maxsize);
	$imagecategory->setVar('imgcat_maxwidth', $imgcat_maxwidth);
	$imagecategory->setVar('imgcat_maxheight', $imgcat_maxheight);
	$imagecategory->setVar('imgcat_weight', $imgcat_weight);
	if (!$imgcat_handler->insert($imagecategory)) {
		exit();
	}
	$imagecategoryperm_handler =& xoops_gethandler('groupperm');
	$criteria = new CriteriaCompo(new Criteria('gperm_itemid', $imgcat_id));
	$criteria->add(new Criteria('gperm_modid', 1));
	$criteria2 = new CriteriaCompo(new Criteria('gperm_name', 'imgcat_write'));
	$criteria2->add(new Criteria('gperm_name', 'imgcat_read'), 'OR');
	$criteria->add($criteria2);
	$imagecategoryperm_handler->deleteAll($criteria);
	if (!isset($readgroup)) {
		$readgroup = array();
	}
	if (!in_array(XOOPS_GROUP_ADMIN, $readgroup)) {
		array_push($readgroup, XOOPS_GROUP_ADMIN);
	}
	foreach ($readgroup as $rgroup) {
		$imagecategoryperm =& $imagecategoryperm_handler->create();
		$imagecategoryperm->setVar('gperm_groupid', $rgroup);
		$imagecategoryperm->setVar('gperm_itemid', $imgcat_id);
		$imagecategoryperm->setVar('gperm_name', 'imgcat_read');
		$imagecategoryperm->setVar('gperm_modid', 1);
		$imagecategoryperm_handler->insert($imagecategoryperm);
		unset($imagecategoryperm);
	}
	if (!isset($writegroup)) {
		$writegroup = array();
	}
	if (!in_array(XOOPS_GROUP_ADMIN, $writegroup)) {
		array_push($writegroup, XOOPS_GROUP_ADMIN);
	}
	foreach ($writegroup as $wgroup) {
		$imagecategoryperm =& $imagecategoryperm_handler->create();
		$imagecategoryperm->setVar('gperm_groupid', $wgroup);
		$imagecategoryperm->setVar('gperm_itemid', $imgcat_id);
		$imagecategoryperm->setVar('gperm_name', 'imgcat_write');
		$imagecategoryperm->setVar('gperm_modid', 1);
		$imagecategoryperm_handler->insert($imagecategoryperm);
		unset($imagecategoryperm);
	}
	redirect_header('admin.php?fct=images',2,_MD_AM_DBUPDATED);
}

function imanager_delcatok($imgcat_id) {
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$imgcat_id = intval($imgcat_id);
	if ($imgcat_id <= 0) {
		redirect_header('admin.php?fct=images',1);
	}
	$imgcat_handler = xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get($imgcat_id);
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}
	if ($imagecategory->getVar('imgcat_type') != 'C') {
		xoops_cp_header();
		xoops_error(_MD_SCATDELNG);
		xoops_cp_footer();
		exit();
	}
	$image_handler =& xoops_gethandler('image');
	$images =& $image_handler->getObjects(new Criteria('imgcat_id', $imgcat_id), true, false);
	$errors = array();
	foreach (array_keys($images) as $i) {
		if (!$image_handler->delete($images[$i])) {
			$errors[] = sprintf(_MD_FAILDEL, $i);
		} else {
			if (file_exists(XOOPS_UPLOAD_PATH.'/'.$images[$i]->getVar('image_name')) && !unlink(XOOPS_UPLOAD_PATH.'/'.$images[$i]->getVar('image_name'))) {
				$errors[] = sprintf(_MD_FAILUNLINK, $i);
			}
		}
	}
	if (!$imgcat_handler->delete($imagecategory)) {
		$errors[] = sprintf(_MD_FAILDELCAT, $imagecategory->getVar('imgcat_name'));
	}
	if (count($errors) > 0) {
		xoops_cp_header();
		xoops_error($errors);
		xoops_cp_footer();
		exit();
	}
	redirect_header('admin.php?fct=images',2,_MD_AM_DBUPDATED);
}

function imanager_reordercateg() {
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images',1, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$count = count($_POST['imgcat_weight']);
	$err = 0;
	if ($count > 0) {
		$imgcat_handler = xoops_gethandler('imagecategory');
		foreach ($_POST['imgcat_weight'] as $k=>$v){
			$cat = $imgcat_handler->get($k);
			$cat->setVar('imgcat_weight',$v);
			if (!$imgcat_handler->insert($cat)){
				$err++;
			}
		}
		if ($err){
			$msg = _MD_FAILEDITCAT;
		}else{
			$msg = _MD_AM_DBUPDATED;
		}
		redirect_header('admin.php?fct=images',2,$msg);
	}
}

function imanager_addfile() {
    if (isset($_POST)) {
        foreach ( $_POST as $k => $v ) {
            ${$k} = $v;
        }
    }
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$imgcat_handler =& xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get(intval($imgcat_id));
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}

	include_once XOOPS_ROOT_PATH.'/class/uploader.php';
	$uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png', 'image/bmp'), $imagecategory->getVar('imgcat_maxsize'), $imagecategory->getVar('imgcat_maxwidth'), $imagecategory->getVar('imgcat_maxheight'));
	$uploader->setPrefix('img');
	$err = array();
	$ucount = count($_POST['xoops_upload_file']);
	for ($i = 0; $i < $ucount; $i++) {
		if ($uploader->fetchMedia($_POST['xoops_upload_file'][$i])) {
			if (!$uploader->upload()) {
				$err[] = $uploader->getErrors();
			} else {
				$image_handler =& xoops_gethandler('image');
				$image =& $image_handler->create();
				$image->setVar('image_name', $uploader->getSavedFileName());
				$image->setVar('image_nicename', $image_nicename);
				$image->setVar('image_mimetype', $uploader->getMediaType());
				$image->setVar('image_created', time());
				$image_display = empty($image_display) ? 0 : 1;
				$image->setVar('image_display', $image_display);
				$image->setVar('image_weight', $image_weight);
				$image->setVar('imgcat_id', $imgcat_id);
				if ($imagecategory->getVar('imgcat_storetype') == 'db') {
					$fp = @fopen($uploader->getSavedDestination(), 'rb');
					$fbinary = @fread($fp, filesize($uploader->getSavedDestination()));
					@fclose($fp);
					$image->setVar('image_body', $fbinary, true);
					@unlink($uploader->getSavedDestination());
				}
				if (!$image_handler->insert($image)) {
					$err[] = sprintf(_FAILSAVEIMG, $image->getVar('image_nicename'));
				}
			}
		} else {
			$err[] = sprintf(_FAILFETCHIMG, $i);
			$err = array_merge($err, $uploader->getErrors(false));
		}
	}
	if (count($err) > 0) {
		xoops_cp_header();
		xoops_error($err);
		xoops_cp_footer();
		exit();
	}
	if (isset($imgcat_id)){
		$redir = '&op=listimg&imgcat_id='.$imgcat_id;
	}else{
		$redir = '';
	}
	redirect_header('admin.php?fct=images'.$redir,2,_MD_AM_DBUPDATED);
}

function imanager_updateimage() {
    if (isset($_POST)) {
        foreach ( $_POST as $k => $v ) {
            ${$k} = $v;
        }
    }
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$count = count($image_id);
	if ($count > 0) {
		$image_handler =& xoops_gethandler('image');
		$error = array();
		for ($i = 0; $i < $count; $i++) {
			$image =& $image_handler->get($image_id[$i]);
			if (!is_object($image)) {
				$error[] = sprintf(_FAILGETIMG, $image_id[$i]);
				continue;
			}
			$image_display[$i] = empty($image_display[$i]) ? 0 : 1;
			$image->setVar('image_display', $image_display[$i]);
			$image->setVar('image_weight', $image_weight[$i]);
			$image->setVar('image_nicename', $image_nicename[$i]);
			$image->setVar('imgcat_id', $imgcat_id[$i]);
			if (!$image_handler->insert($image)) {
				$error[] = sprintf(_FAILSAVEIMG, $image_id[$i]);
			}
		}
		if (count($error) > 0) {
			xoops_cp_header();
			foreach ($error as $err) {
				echo $err.'<br />';
			}
			xoops_cp_footer();
			exit();
		}
	}
	if (isset($redir)){
		$redir = '&op=listimg&imgcat_id='.$redir;
	}else{
		$redir = '';
	}
	redirect_header('admin.php?fct=images'.$redir,2,_MD_AM_DBUPDATED);
}

function imanager_delfileok($image_id,$redir=null) {
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}
	$image_id = intval($image_id);
	if ($image_id <= 0) {
		redirect_header('admin.php?fct=images',1);
	}
	$image_handler =& xoops_gethandler('image');
	$image =& $image_handler->get($image_id);
	if (!is_object($image)) {
		redirect_header('admin.php?fct=images',1);
	}
	if (!$image_handler->delete($image)) {
		xoops_cp_header();
		xoops_error(sprintf(_MD_FAILDEL, $image->getVar('image_id')));
		xoops_cp_footer();
		exit();
	}
	@unlink(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name'));
	if (isset($redir)){
		$redir = '&op=listimg&imgcat_id='.$redir;
	}else{
		$redir = '';
	}
	redirect_header('admin.php?fct=images'.$redir,2,_MD_AM_DBUPDATED);
}

function showAddImgForm($imgcat_id){
	include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
	$imgcat_handler = xoops_gethandler('imagecategory');
	$form = new XoopsThemeForm(_ADDIMAGE, 'image_form', 'admin.php', 'post', true);
	$form->setExtra('enctype="multipart/form-data"');
	$form->addElement(new XoopsFormText(_IMAGENAME, 'image_nicename', 50, 255), true);
	$select = new XoopsFormSelect(_IMAGECAT, 'imgcat_id',intval($imgcat_id));
	$select->addOptionArray($imgcat_handler->getList());
	$form->addElement($select, true);
	$form->addElement(new XoopsFormFile(_IMAGEFILE, 'image_file', 5000000));
	$form->addElement(new XoopsFormText(_IMGWEIGHT, 'image_weight', 3, 4, 0));
	$form->addElement(new XoopsFormRadioYN(_IMGDISPLAY, 'image_display', 1, _YES, _NO));
	$form->addElement(new XoopsFormHidden('imgcat_id', $imgcat_id));
	$form->addElement(new XoopsFormHidden('op', 'addfile'));
	$form->addElement(new XoopsFormHidden('fct', 'images'));
	$tray = new XoopsFormElementTray('' ,'');
	$tray->addElement(new XoopsFormButton('', 'img_button', _SUBMIT, 'submit'));
	$btn = new XoopsFormButton('', 'reset', _CANCEL, 'button');
	$btn->setExtra('onclick="document.getElementById(\'addimgform\').style.display = \'none\'; return false;"');
	$tray->addElement($btn);
	$form->addElement($tray);
	return $form->render();
}

function imanager_clone() {
	if (!$GLOBALS['xoopsSecurity']->check()) {
		redirect_header('admin.php?fct=images', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
	}

	$imgcat_id = intval($_POST['imgcat_id']);
	$image_id = intval($_POST['image_id']);

	$imgcat_handler =& xoops_gethandler('imagecategory');
	$imagecategory =& $imgcat_handler->get(intval($imgcat_id));
	if (!is_object($imagecategory)) {
		redirect_header('admin.php?fct=images',1);
	}

	$image_handler =& xoops_gethandler('image');
	$image =& $image_handler->get($image_id);
	if ( ($ext = strrpos( $image->getVar('image_name'), '.' )) !== false ) {
		$ext = strtolower(substr( $image->getVar('image_name'), $ext + 1 ));
	}
	include_once XOOPS_ROOT_PATH.'/class/image.class.php';

	$imgname = 'img'.icms_random_str(12).'.'.$ext;
	$newimg =& $image_handler->create();
	$newimg->setVar('image_name', $imgname);
	$newimg->setVar('image_nicename', $_POST['image_nicename']);
	$newimg->setVar('image_mimetype', $image->getVar('image_mimetype'));
	$newimg->setVar('image_created', time());
	$newimg->setVar('image_display', $_POST['image_display']);
	$newimg->setVar('image_weight', $_POST['image_weight']);
	$newimg->setVar('imgcat_id', $imgcat_id);
	if ($imagecategory->getVar('imgcat_storetype') == 'db') {
		$newimage = Image::open(XOOPS_URL.'/image.php?id='.$image->getVar('image_id'));
		$newimage->save(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name'));
		$fp = @fopen(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name'), 'rb');
		$fbinary = @fread($fp, filesize(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name')));
		@fclose($fp);
		$newimg->setVar('image_body', $fbinary, true);
		@unlink(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name'));
	}else{
		if (!@copy(XOOPS_UPLOAD_PATH.'/'.$image->getVar('image_name'),XOOPS_UPLOAD_PATH.'/'.$imgname)){
			$msg = sprintf(_FAILSAVEIMG, $image->getVar('image_nicename'));
		}
	}
	if (!$image_handler->insert($newimg)) {
		$msg = sprintf(_FAILSAVEIMG, $newimg->getVar('image_nicename'));
	}else{
		$msg = _MD_AM_DBUPDATED;
	}


	if (isset($imgcat_id)){
		$redir = '&op=listimg&imgcat_id='.$imgcat_id;
	}else{
		$redir = '';
	}
	redirect_header('admin.php?fct=images'.$redir,2,$msg);
}
?>