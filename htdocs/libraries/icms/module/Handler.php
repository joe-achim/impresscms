<?php
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
/**
 * Manage modules
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @category	ICMS
 * @package		Module
 * @version	$Id$
 */

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path is not defined");

/**
 * Module handler class.
 *
 * This class is responsible for providing data access mechanisms to the data source
 * of module class objects.
 *
 * @category	ICMS
 * @package		Module
 * @author	Kazumi Ono 	<onokazu@xoops.org>
 * @copyright	Copyright (c) 2000 XOOPS.org
 */
class icms_module_Handler extends icms_core_ObjectHandler {
	/**
	 * holds an array of cached module references, indexed by module dirname
	 *
	 * @var    array
	 * @access private
	 */
	private $_cachedModule = array();

	/**
	 * holds the lookup table for cache access by module id
	 *
	 * @var    array
	 * @access private
	 */
	private $_cachedModule_lookup = array();

	/**
	 * Create a new {@link icms_module_Object} object
	 *
	 * @param   boolean     $isNew   Flag the new object as "new"
	 * @return  object      {@link icms_module_Object}
	 */
	public function &create($isNew = TRUE) {
		$module = new icms_module_Object();
		if ($isNew) $module->setNew();
		return $module;
	}

	/**
	 * Load a module from the database
	 *
	 * @param  	int     $id			ID of the module
	 * @param	bool	$loadConfig	set to TRUE in case you want to load the module config in addition
	 * @return	object  {@link icms_module_Object} FALSE on fail
	 */
	public function &get($id, $loadConfig = FALSE) {
		$id = (int) $id;
		$module = FALSE;
		if ($id > 0) {
			if (!empty($this->_cachedModule_lookup[$id]) &&
					!empty($this->_cachedModule[$this->_cachedModule_lookup[$id]])
			) {
				if ($loadConfig) $this->loadConfig($this->_cachedModule[$this->_cachedModule_lookup[$id]]);
				return $this->_cachedModule[$this->_cachedModule_lookup[$id]];
			} else {
				$sql = "SELECT * FROM " . $this->db->prefix('modules') . " WHERE mid = '" . $id . "'";
				if (!$result = $this->db->query($sql)) return $module;
				$numrows = $this->db->getRowsNum($result);
				if ($numrows == 1) {
					$module = new icms_module_Object();
					$myrow = $this->db->fetchArray($result);
					$module->assignVars($myrow);
					// load module config
					if ($loadConfig) $this->loadConfig($module);
					// cache module
					$this->_cachedModule_lookup[$id] = $module->getVar("dirname");
					$this->_cachedModule[$module->getVar('dirname')] = $module;
					return $module;
				}
			}
		}
		return $module;
	}

	/**
	 * Load a module by its dirname
	 *
	 * @param	string	$dirname
	 * @param	bool	$loadConfig	set to TRUE in case you want to load the module config in addition
	 * @return	object  {@link icms_module_Object} FALSE on fail
	 */
	public function &getByDirname($dirname, $loadConfig = FALSE) {
		if (!empty($this->_cachedModule[$dirname]) &&
				$this->_cachedModule[$dirname]->getVar('dirname') == $dirname
		) {
			if ($loadConfig) $this->loadConfig($this->_cachedModule[$dirname]);
			return $this->_cachedModule[$dirname];
		} else {
			$module = FALSE;
			$sql = "SELECT * FROM " . $this->db->prefix('modules') . " WHERE dirname = '" . trim($dirname) . "'";
			if (!$result = $this->db->query($sql)) return $module;
			$numrows = $this->db->getRowsNum($result);
			if ($numrows == 1) {
				$module = new icms_module_Object();
				$myrow = $this->db->fetchArray($result);
				$module->assignVars($myrow);
				// load module config
				if ($loadConfig) $this->loadConfig($module);
				// cache module
				$this->_cachedModule[$dirname] = $module;
				$this->_cachedModule_lookup[$module->getVar('mid')] = $module->getVar("dirname");
			}
			return $module;
		}
	}

	/**
	 * load config for a module before caching it
	 *
	 * @param	icms_module_Object	$module
	 * @return	bool				TRUE
	 */
	private function loadConfig($module) {
		if ($module->config !== NULL) return TRUE;
		icms_loadLanguageFile($module->getVar("dirname"), "main");
		if ($module->getVar("hasconfig") == 1
				|| $module->getVar("hascomments") == 1
				|| $module->getVar("hasnotification") == 1
		) {
			$module->config = icms::$config->getConfigsByCat(0, $module->getVar("mid"));
		}
		return TRUE;
	}

	/**
	 * Inserts a module into the database
	 *
	 * @param   object  &$module reference to a {@link icms_module_Object}
	 * @return  bool
	 */
	public function insert(&$module) {
		if (get_class($module) != 'icms_module_Object') return FALSE;
		if (!$module->isDirty()) return TRUE;
		if (!$module->cleanVars()) return FALSE;

		/**
		 * Editing the insert and update methods
		 * this is temporaray as will soon be based on a persistableObjectHandler
		 */
		$fieldsToStoreInDB = array();
		foreach ($module->cleanVars as $k => $v) {
			if ($k == 'last_update') {
				$v = time();
			}
			if ($module->vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
				$cleanvars[$k] = (int) $v;
			} elseif (is_array($v)) {
				$cleanvars[$k] = $this->db->quoteString(implode(',', $v));
			} else {
				$cleanvars[$k] = $this->db->quoteString($v);
			}
			$fieldsToStoreInDB[$k] = $cleanvars[$k];
		}

		if ($module->isNew()) {
			$sql = "INSERT INTO " . $this->db->prefix('modules')
			. " (" . implode(',', array_keys($fieldsToStoreInDB))
			. ") VALUES (" . implode(',', array_values($fieldsToStoreInDB)) . ")";
		} else {
			$sql = "UPDATE " . $this->db->prefix('modules') . " SET";
			foreach ($fieldsToStoreInDB as $key => $value) {
				if (isset($notfirst)) {
					$sql .= ",";
				}
				$sql .= " " . $key . " = " . $value;
				$notfirst = TRUE;
			}
			$whereclause = 'mid' . " = " . $module->getVar('mid');
			$sql .= " WHERE " . $whereclause;
		}

		if (!$result = $this->db->query($sql)) return FALSE;
		if ($module->isNew()) {
			$module->assignVar('mid', $this->db->getInsertId());
		}
		if (!empty($this->_cachedModule[$module->getVar('dirname')])) {
			unset($this->_cachedModule[$module->getVar('dirname')]);
		}
		if (!empty($this->_cachedModule_lookup[$module->getVar('mid')])) {
			unset($this->_cachedModule_loopup[$module->getVar('mid')]);
		}
		return TRUE;
	}

	/**
	 * Delete a module from the database
	 *
	 * @param   object  &$module {@link icms_module_Object}
	 * @return  bool
	 */
	public function delete(&$module) {
		if (get_class($module) != 'icms_module_Object') return FALSE;

		$sql = sprintf(
				"DELETE FROM %s WHERE mid = '%u'",
				$this->db->prefix('modules'), (int) $module->getVar('mid')
		);
		if (!$result = $this->db->query($sql)) return FALSE;

		// delete admin permissions assigned for this module
		$sql = sprintf(
				"DELETE FROM %s WHERE gperm_name = 'module_admin' AND gperm_itemid = '%u'",
				$this->db->prefix('group_permission'), (int) $module->getVar('mid')
		);
		$this->db->query($sql);
		// delete read permissions assigned for this module
		$sql = sprintf(
				"DELETE FROM %s WHERE gperm_name = 'module_read' AND gperm_itemid = '%u'",
				$this->db->prefix('group_permission'), (int) $module->getVar('mid')
		);
		$this->db->query($sql);

		$sql = sprintf(
				"SELECT block_id FROM %s WHERE module_id = '%u'",
				$this->db->prefix('block_module_link'), (int) $module->getVar('mid')
		);
		if ($result = $this->db->query($sql)) {
			$block_id_arr = array();
			while ($myrow = $this->db->fetchArray($result)) {
				array_push($block_id_arr, $myrow['block_id']);
			}
		}

		// loop through block_id_arr
		if (isset($block_id_arr)) {
			foreach ($block_id_arr as $i) {
				$sql = sprintf(
						"SELECT block_id FROM %s WHERE module_id != '%u' AND block_id = '%u'",
						$this->db->prefix('block_module_link'), (int) $module->getVar('mid'), (int) $i
				);
				if ($result2 = $this->db->query($sql)) {
					if (0 < $this->db->getRowsNum($result2)) {
						// this block has other entries, so delete the entry for this module
						$sql = sprintf(
								"DELETE FROM %s WHERE (module_id = '%u') AND (block_id = '%u')",
								$this->db->prefix('block_module_link'), (int) $module->getVar('mid'), (int) $i
						);
						$this->db->query($sql);
					} else {
						// this block doesnt have other entries, so disable the block and let it show on top page only. otherwise, this block will not display anymore on block admin page!
						$sql = sprintf(
								"UPDATE %s SET visible = '0' WHERE bid = '%u'",
								$this->db->prefix('newblocks'), (int) $i
						);
						$this->db->query($sql);
						$sql = sprintf(
								"UPDATE %s SET module_id = '-1' WHERE module_id = '%u'",
								$this->db->prefix('block_module_link'), (int) $module->getVar('mid')
						);
						$this->db->query($sql);
					}
				}
			}
		}

		if (!empty($this->_cachedModule[$module->getVar('dirname')])) {
			unset($this->_cachedModule[$module->getVar('dirname')]);
		}
		if (!empty($this->_cachedModule_lookup[$module->getVar('mid')])) {
			unset($this->_cachedModule_lookup[$module->getVar('mid')]);
		}
		return TRUE;
	}

	/**
	 * Retrieve list of installed modules from the database
	 *
	 * @param   object  $criteria   {@link icms_db_criteria_Element}
	 * @param   boolean $id_as_key  Use the ID as key into the array
	 * @return  array	Array of objects - installed module
	 */
	public function &getObjects($criteria = NULL, $id_as_key = FALSE) {
		$ret = array();
		$limit = $start = 0;
		$sql = "SELECT * FROM " . $this->db->prefix('modules');
		if (isset($criteria) && is_subclass_of($criteria, 'icms_db_criteria_Element')) {
			$sql .= " " . $criteria->renderWhere();
			$sql .= " ORDER BY weight " . $criteria->getOrder() . ", mid ASC";
			$limit = $criteria->getLimit();
			$start = $criteria->getStart();
		}
		$result = $this->db->query($sql, $limit, $start);
		if (!$result) return $ret;
		while ($myrow = $this->db->fetchArray($result)) {
			$module = new icms_module_Object();
			$module->assignVars($myrow);
			if (!$id_as_key) {
				$ret[] = $module;
			} else {
				$ret[$myrow['mid']] = $module;
			}
			$this->_cachedModule_lookup[$myrow['mid']] = $module->getVar("dirname");
			$this->_cachedModule[$myrow['dirname']] = $module;
			unset($module);
		}
		return $ret;
	}

	/**
	 * Count some modules
	 *
	 * @param   object  $criteria   {@link icms_db_criteria_Element}
	 * @return  int
	 */
	public function getCount($criteria = NULL) {
		$sql = "SELECT COUNT(*) FROM " . $this->db->prefix('modules');
		if (isset($criteria) && is_subclass_of($criteria, 'icms_db_criteria_Element')) {
			$sql .= " " . $criteria->renderWhere();
		}
		if (!$result = $this->db->query($sql)) return 0;
		list($count) = $this->db->fetchRow($result);
		return $count;
	}

	/**
	 * returns an array of installed module names
	 *
	 * @param   bool    $criteria
	 * @param   boolean $dirname_as_key
	 *      if TRUE, array keys will be module directory names
	 *      if FALSE, array keys will be module id
	 * @return  array
	 */
	public function getList($criteria = NULL, $dirname_as_key = FALSE) {
		$ret = array();
		$modules = $this->getObjects($criteria, TRUE);
		foreach (array_keys($modules) as $i) {
			if (!$dirname_as_key) {
				$ret[$i] = $modules[$i]->getVar('name');
			} else {
				$ret[$modules[$i]->getVar('dirname')] = $modules[$i]->getVar('name');
			}
		}
		return $ret;
	}

	/**
	 * Returns an array of all available modules, based on folders in the modules directory
	 *
	 * The getList method cannot be used for this, because uninstalled modules are not listed
	 * in the database
	 *
	 * @since	1.3
	 * @return	array	List of folder names in the modules directory
	 */
	static public function getAvailable() {
		$dirtyList = $cleanList = array();
		$dirtyList = icms_core_Filesystem::getDirList(ICMS_MODULES_PATH . '/');
		foreach ($dirtyList as $item) {
			if (file_exists(ICMS_MODULES_PATH . '/' . $item . '/icms_version.php')) {
				$cleanList[$item] = $item;
			} elseif (file_exists(ICMS_MODULES_PATH . '/' . $item . '/xoops_version.php')) {
				$cleanList[$item] = $item;
			}
		}
		return $cleanList;
	}

	/**
	 * Get a list of active modules, with the folder name as the key
	 *
	 * This method is necessary to be able to use a static method
	 *
	 * @since	1.3
	 * @return	array	List of active modules
	 */
	static public function getActive() {
		$module_handler = new self(icms::$xoopsDB);
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('isactive', 1));
		return $module_handler->getList($criteria, TRUE);
	}

	/**
	 * Finds and initializes the current module.
	 * @param bool $inAdmin Whether we are on the admin side or not
	 */
	static public function service($inAdmin = FALSE) {
		$module = NULL;
		if ($inAdmin || file_exists('./xoops_version.php') || file_exists('./icms_version.php')) {
			$url_arr = explode('/', strstr($_SERVER['PHP_SELF'], '/modules/'));
			if (isset($url_arr[2])) {
				/* @var $module icms_module_Object */
				$module = icms::handler("icms_module")->getByDirname($url_arr[2], TRUE);
				if (!$inAdmin && (!$module || !$module->getVar('isactive'))) {
					include_once ICMS_ROOT_PATH . '/header.php';
					echo "<h4>" . _MODULENOEXIST . "</h4>";
					include_once ICMS_ROOT_PATH . '/footer.php';
					exit();
				}
			}
		}
		if (!self::checkModuleAccess($module, $inAdmin)) {
			redirect_header(ICMS_URL . "/user.php", 3, _NOPERM, FALSE);
		}
		if ($module) $module->launch();
		return $module ? $module : NULL;
	}

	/**
	 * Checks if the current user can access the specified module
	 * @param icms_module_Object $module
	 * @param bool $inAdmin
	 * @return bool
	 */
	static protected function checkModuleAccess($module, $inAdmin = FALSE) {
		if ($inAdmin && !icms::$user) return FALSE;
		/* @var $perm_handler icms_member_groupperm_Handler */
		$perm_handler = icms::handler('icms_member_groupperm');
		if ($inAdmin) {
			if (!$module) {
				// We are in /admin.php
				return icms::$user->isAdmin(-1);
			} else {
				return $perm_handler->checkRight('module_admin', $module->getVar('mid'), icms::$user->getGroups());
			}
		} elseif ($module) {
			$groups = (icms::$user) ? icms::$user->getGroups() : ICMS_GROUP_ANONYMOUS;
			return $perm_handler->checkRight('module_read', $module->getVar('mid'), $groups);
		}
		// We are in /something.php: let the page handle permissions
		return TRUE;
	}

	/**
	 * Function and rendering for installation of a module
	 *
	 * @param 	string	$dirname
	 * @return	string	Results of the installation process
	 */
	public function install($dirname) {

	}

	/**
	 * Logic for uninstalling a module
	 *
	 * @param unknown_type $dirname
	 * @return	string	Result messages for uninstallation
	 */
	public function uninstall($dirname) {

	}

	/**
	 * Logic for updating a module
	 *
	 * @param 	str $dirname
	 * @return	str	Result messages from the module update
	 */
	public function update($dirname) {

	}

	/**
	 * Logic for activating a module
	 *
	 * @param	int	$mid
	 * @return	string	Result message for activating the module
	 */
	public function activate($mid) {

	}

	/**
	 * Logic for deactivating a module
	 *
	 * @param	int	$mid
	 * @return	string	Result message for deactivating the module
	 */
	public function deactivate($mid) {

	}

	/**
	 * Logic for changing the weight (order) and name of modules
	 *
	 * @param int $mid		Unique ID for the module to change
	 * @param int $weight	Integer value of the weight to be applied to the module
	 * @param str $name		Name to be applied to the module
	 */
	public function change($mid, $weight, $name) {

	}

	/**
	 *
	 * @param	string	$dirname	Directory name of the module
	 * @param	string	$template	Name of the template file
	 * @param	boolean	$block		Are you trying to retrieve the template for a block?
	 */
	public function getTemplate($dirname, $template, $block = FALSE) {

	}

	/**
	 * Posts a notification of an install or update of the system module
	 *
	 * @todo	Add language constants
	 *
	 * @param	string	$versionstring	A string representing the version of the module
	 * @param	string	$icmsroot		A unique identifier for the site
	 * @param	string	$modulename		The module being installed or updated, 'system' for the core
	 * @param	string	$action			Action triggering the notification: install, uninstall, activate, deactivate, update
	 */
	public static function installation_notify($versionstring, $icmsroot, $modulename = 'system', $action = 'install') {

		$validActions = array('install', 'update', 'uninstall', 'activate', 'deactivate');
		if (!in_array($action, $validActions)) $action = 'install';

		// @todo: change the URL to an official ImpressCMS server
		//set POST variables
		$url = 'http://qc.impresscms.org/notify/notify.php?'; // this may change as testing progresses.
		$fields = array(
				'siteid' => hash('sha256', $icmsroot),
				'version' => urlencode($versionstring),
				'module' => urlencode($modulename),
				'action' => urlencode($action),
		);

		//url-ify the data for the POST
		$fields_string = "";
		foreach($fields as $key=>$value) {
			$fields_string .= $key . '=' . $value . '&';
		}
		rtrim($fields_string, '&');

		try {
			//open connection - this causes a fatal error if the extension is not loaded
			if (!extension_loaded('curl')) throw new Exception("cURL extension not loaded");
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

			//execute post
			if (curl_exec($ch)) {
				icms_core_Message::error($url . $fields_string, 'Notification Sent to');
			} else {
				throw new Execption("Unable to contact update server");
			}

			//close connection
			curl_close($ch);
		} catch(Exception $e) {
			icms_core_Message::error(sprintf($e->getMessage()));
		}
	}
}
