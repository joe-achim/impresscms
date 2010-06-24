<?php
/**
 * Manage groups and memberships
 *
 * @copyright	The XOOPS Project <http://www.xoops.org/>
 * @copyright	XOOPS_copyrights.txt
 * @copyright	The ImpressCMS Project <http://www.impresscms.org/>
 * @license		LICENSE.txt
 * @since		XOOPS
 *
 * @author		Kazumi Ono (aka onokazo)
 * @author	The XOOPS Project Community <http://www.xoops.org>
 * @author	Gustavo Alejandro Pilla (aka nekro) <nekro@impresscms.org> <gpilla@nube.com.ar>
 *
 * @package	core
 * @subpackage	groupperm
 * @version		$Id: groupperm.php 19450 2010-06-18 14:15:29Z malanciault $
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

/**
 * Group permission handler class.
 *
 * This class is responsible for providing data access mechanisms to the data source
 * of group permission class objects.
 * This class is an abstract class to be implemented by child group permission classes.
 *
 * @see          icms_member_groupperm_Object
 * @author       Kazumi Ono  <onokazu@xoops.org>
 * @copyright	copyright (c) 2000-2003 XOOPS.org
 */
class icms_member_groupperm_Handler extends icms_core_ObjectHandler {
	public static $_cachedRights;

	/**
	 * Create a new {@link icms_member_groupperm_Object}
	 *
	 * @return	bool    $isNew  Flag the object as "new"?
	 * @see htdocs/kernel/icms_core_ObjectHandler#create()
	 */
	public function &create($isNew = true) {
		$perm = new icms_member_groupperm_Object();
		if ($isNew) {
			$perm->setNew();
		}
		return $perm;
	}

	/**
	 * Retrieve a group permission
	 *
	 * @param	int $id ID
	 *
	 * @return	object  {@link icms_member_groupperm_Object}, FALSE on fail
	 * @see htdocs/kernel/icms_core_ObjectHandler#get($int_id)
	 */
	public function &get($id) {
		$id = (int) ($id);
		$perm = false;
		if ($id > 0) {
			$sql = sprintf("SELECT * FROM %s WHERE gperm_id = '%u'", $this->db->prefix('group_permission'), $id);
			if ( !$result = $this->db->query($sql) ) {
				return $perm;
			}
			$numrows = $this->db->getRowsNum($result);
			if ( $numrows == 1 ) {
				$perm = new icms_member_groupperm_Object();
				$perm->assignVars($this->db->fetchArray($result));
			}
		}
		return $perm;
	}

	/**
	 * Store a {@link icms_member_groupperm_Object}
	 *
	 * @param	object  &$perm  {@link icms_member_groupperm_Object} object
	 *
	 * @return	bool    TRUE on success
	 * @see htdocs/kernel/icms_core_ObjectHandler#insert($object)
	 */
	public function insert(&$perm) {
		/**
		 * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
		 */
		if (!is_a($perm, 'icms_member_groupperm_Object')) {
			return false;
		}
		if ( !$perm->isDirty() ) {
			return true;
		}
		if (!$perm->cleanVars()) {
			return false;
		}
		foreach ($perm->cleanVars as $k => $v) {
			${$k} = $v;
		}
		if ($perm->isNew()) {
			$gperm_id = $this->db->genId('group_permission_gperm_id_seq');
			$sql = sprintf("INSERT INTO %s (gperm_id, gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES ('%u', '%u', '%u', '%u', %s)", $this->db->prefix('group_permission'), (int) ($gperm_id), (int) ($gperm_groupid), (int) ($gperm_itemid), (int) ($gperm_modid), $this->db->quoteString($gperm_name));
		} else {
			$sql = sprintf("UPDATE %s SET gperm_groupid = '%u', gperm_itemid = '%u', gperm_modid = '%u' WHERE gperm_id = '%u'", $this->db->prefix('group_permission'), (int) ($gperm_groupid), (int) ($gperm_itemid), (int) ($gperm_modid), (int) ($gperm_id));
		}
		if (!$result = $this->db->query($sql)) {
			return false;
		}
		if (empty($gperm_id)) {
			$gperm_id = $this->db->getInsertId();
		}
		$perm->assignVar('gperm_id', $gperm_id);
		return true;
	}

	/**
	 * Delete a {@link icms_member_groupperm_Object}
	 *
	 * @param	object  &$perm
	 *
	 * @return	bool    TRUE on success
	 * @see htdocs/kernel/icms_core_ObjectHandler#delete($object)
	 */
	public function delete(&$perm) {
		/**
		 * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
		 */
		if (!is_a($perm, 'icms_member_groupperm_Object')) {
			return false;
		}
		$sql = sprintf("DELETE FROM %s WHERE gperm_id = '%u'", $this->db->prefix('group_permission'), (int) ($perm->getVar('gperm_id')));
		if (!$result = $this->db->query($sql)) {
			return false;
		}
		return true;
	}

	/**
	 * Retrieve multiple {@link icms_member_groupperm_Object}s
	 *
	 * @param	object  $criteria   {@link icms_criteria_Element}
	 * @param	bool    $id_as_key  Use IDs as array keys?
	 *
	 * @return	array   Array of {@link icms_member_groupperm_Object}s
	 */
	public function getObjects($criteria = null, $id_as_key = false) {
		$ret = array();
		$limit = $start = 0;
		$sql = 'SELECT * FROM '.$this->db->prefix('group_permission');
		if (isset($criteria) && is_subclass_of($criteria, 'icms_criteria_Element')) {
			$sql .= ' '.$criteria->renderWhere();
			$limit = $criteria->getLimit();
			$start = $criteria->getStart();
		}
		$result = $this->db->query($sql, $limit, $start);
		if (!$result) {
			return $ret;
		}
		while ($myrow = $this->db->fetchArray($result)) {
			$perm = new icms_member_groupperm_Object();
			$perm->assignVars($myrow);
			if (!$id_as_key) {
				$ret[] =& $perm;
			} else {
				$ret[$myrow['gperm_id']] =& $perm;
			}
			unset($perm);
		}
		return $ret;
	}

	/**
	 * Count some {@link icms_member_groupperm_Object}s
	 *
	 * @param	object  $criteria   {@link icms_criteria_Element}
	 *
	 * @return	int
	 */
	public function getCount($criteria = null) {
		$sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('group_permission');
		if (isset($criteria) && is_subclass_of($criteria, 'icms_criteria_Element')) {
			$sql .= ' '.$criteria->renderWhere();
		}
		$result = $this->db->query($sql);
		if (!$result) {
			return 0;
		}
		list($count) = $this->db->fetchRow($result);
		return $count;
	}

	/**
	 * Delete all permissions by a certain criteria
	 *
	 * @param	object  $criteria   {@link icms_criteria_Element}
	 *
	 * @return	bool    TRUE on success
	 */
	public function deleteAll($criteria = null) {
		$sql = sprintf("DELETE FROM %s", $this->db->prefix('group_permission'));		if (isset($criteria) && is_subclass_of($criteria, 'icms_criteria_Element')) {
			$sql .= ' '.$criteria->renderWhere();
		}
		if (!$result = $this->db->query($sql)) {
			return false;
		}
		return true;
	}

	/**
	 * Delete all module specific permissions assigned for a group
	 *
	 * @param	int  $gperm_groupid ID of a group
	 * @param	int  $gperm_modid ID of a module
	 *
	 * @return	bool TRUE on success
	 */
	public function deleteByGroup($gperm_groupid, $gperm_modid = null) {
		$criteria = new icms_criteria_Compo(new icms_criteria_Item('gperm_groupid', (int) ($gperm_groupid)));
		if (isset($gperm_modid)) {
			$criteria->add(new icms_criteria_Item('gperm_modid', (int) ($gperm_modid)));
		}
		return $this->deleteAll($criteria);
	}

	/**
	 * Delete all module specific permissions
	 *
	 * @param	int  $gperm_modid ID of a module
	 * @param	string  $gperm_name Name of a module permission
	 * @param	int  $gperm_itemid ID of a module item
	 *
	 * @return	bool TRUE on success
	 */
	public function deleteByModule($gperm_modid, $gperm_name = null, $gperm_itemid = null) {
		$criteria = new icms_criteria_Compo(new icms_criteria_Item('gperm_modid', (int) ($gperm_modid)));
		if (isset($gperm_name)) {
			$criteria->add(new icms_criteria_Item('gperm_name', $gperm_name));
			if (isset($gperm_itemid)) {
				$criteria->add(new icms_criteria_Item('gperm_itemid', (int) ($gperm_itemid)));
			}
		}
		return $this->deleteAll($criteria);
	}
	/**#@-*/

	/**
	 * Check permission
	 *
	 * @param	string    $gperm_name       Name of permission
	 * @param	int       $gperm_itemid     ID of an item
	 * @param	int/array $gperm_groupid    A group ID or an array of group IDs
	 * @param	int       $gperm_modid      ID of a module
	 * @param	bool	  $webmasterAlwaysTrue	If true, then Webmasters will always return true, if false, a real check will be made
	 *
	 * @return	bool    TRUE if permission is enabled
	 */
	public function checkRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1, $webmasterAlwaysTrue=true) {
		$criteria = new icms_criteria_Compo(new icms_criteria_Item('gperm_modid', $gperm_modid));
		$criteria->add(new icms_criteria_Item('gperm_name', $gperm_name));
		$gperm_itemid = (int) ($gperm_itemid);
		if ($gperm_itemid > 0) {
			$criteria->add(new icms_criteria_Item('gperm_itemid', $gperm_itemid));
		}
		if (is_array($gperm_groupid)) {
			if ($webmasterAlwaysTrue && in_array(XOOPS_GROUP_ADMIN, $gperm_groupid)) {
				return true;
			}
			$criteria2 = new icms_criteria_Compo();
			foreach ($gperm_groupid as $gid) {
				$criteria2->add(new icms_criteria_Item('gperm_groupid', $gid), 'OR');
			}
			$criteria->add($criteria2);
		} else {
			if ($webmasterAlwaysTrue && XOOPS_GROUP_ADMIN == $gperm_groupid) {
				return true;
			}
			$criteria->add(new icms_criteria_Item('gperm_groupid', $gperm_groupid));
		}
		if ($this->getCount($criteria) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Add a permission
	 *
	 * @param	string  $gperm_name       Name of permission
	 * @param	int     $gperm_itemid     ID of an item
	 * @param	int     $gperm_groupid    ID of a group
	 * @param	int     $gperm_modid      ID of a module
	 *
	 * @return	bool    TRUE if success
	 */
	public function addRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1) {
		$perm =& $this->create();
		$perm->setVar('gperm_name', $gperm_name);
		$perm->setVar('gperm_groupid', $gperm_groupid);
		$perm->setVar('gperm_itemid', $gperm_itemid);
		$perm->setVar('gperm_modid', $gperm_modid);
		return $this->insert($perm);
	}

	/**
	 * Get all item IDs that a group is assigned a specific permission
	 *
	 * @param	string    $gperm_name       Name of permission
	 * @param	int/array $gperm_groupid    A group ID or an array of group IDs
	 * @param	int       $gperm_modid      ID of a module
	 *
	 * @return  array   array of item IDs
	 */
	public function getItemIds($gperm_name, $gperm_groupid, $gperm_modid = 1) {
		$ret = array();
		$criteria = new icms_criteria_Compo(new icms_criteria_Item('gperm_name', $gperm_name));
		$criteria->add(new icms_criteria_Item('gperm_modid', (int) ($gperm_modid)));
		if (is_array($gperm_groupid)) {
			$criteria2 = new icms_criteria_Compo();
			foreach ($gperm_groupid as $gid) {
				$criteria2->add(new icms_criteria_Item('gperm_groupid', $gid), 'OR');
			}
			$criteria->add($criteria2);
		} else {
			$criteria->add(new icms_criteria_Item('gperm_groupid', (int) ($gperm_groupid)));
		}
		$perms = $this->getObjects($criteria, true);
		foreach (array_keys($perms) as $i) {
			$ret[] = $perms[$i]->getVar('gperm_itemid');
		}
		return array_unique($ret);
	}

	/**
	 * Get all group IDs assigned a specific permission for a particular item
	 *
	 * @param	string  $gperm_name       Name of permission
	 * @param	int     $gperm_itemid     ID of an item
	 * @param	int     $gperm_modid      ID of a module
	 *
	 * @return  array   array of group IDs
	 */
	public function getGroupIds($gperm_name, $gperm_itemid, $gperm_modid = 1) {
		$ret = array();
		$perms = array();
		if(isset( $this->_cachedRights[$gperm_name][$gperm_itemid][$gperm_modid] ))
  			$perms = array($this->_cachedRights[$gperm_name][$gperm_itemid][$gperm_modid]);
  		else{
			$criteria = new icms_criteria_Compo(new icms_criteria_Item('gperm_name', $gperm_name));
			$criteria->add(new icms_criteria_Item('gperm_itemid', (int) ($gperm_itemid)));
			$criteria->add(new icms_criteria_Item('gperm_modid', (int) ($gperm_modid)));
			$perms = $this->getObjects($criteria, true);
			foreach($perms as $perm)
		  		$this->_cachedRights[$gperm_name][$gperm_itemid][$gperm_modid] = $perm;
	  		}
		foreach (array_keys($perms) as $i)
			$ret[] = $perms[$i]->getVar('gperm_groupid');
		return $ret;
	}
}

/**
 * @deprecated	Use icms_member_groupperm_Handler, instead
 * @todo		Remove in version 1.4
 *
 */
class XoopsGrouppermHandler extends icms_member_groupperm_Handler {

}