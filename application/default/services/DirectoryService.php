<?php
/*
 * Copyright 2007 The University of Sydney. All rights reserved.
 *
 * This software is the confidential and proprietary information of the
 * University of Sydney ("Confidential Information").  You shall not disclose
 * such such Confidential Information and shall use it only in accordance
 * with the terms of the license agreement you entered into with The University
 * of Sydney.
 *
 * THE UNIVERSITY OF SYDNEY MAKES NO REPRESENTATIONS OR WARRANTIES ABOUT THE
 * SUITABILITY OF THE SOFTWARE, EITHER EXPRESS OR IMPLIED, INCLUDED BUT NOT
 * LIMITED TO THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE, OR NON-INFRINGEMENT.  THE UNIVERSITY SHALL NOT BE
 * LIABLE FOR ANY DAMAGES SUFFERED BY A LICENSEE AS A RESULT OF USING,
 * MODIFYING OR DISTRIBUTING THIS SOFTWARE OR ITS DERIVATIVES.
 */

// ldap search scopes
define('LDAP_SCOPE_ONELEVEL', 1);
define('LDAP_SCOPE_SUBTREE', 2);

/**
 * A class which provides high-level directory service (LDAP) manipulation
 * functionality.  Functions include creating accounts, removing accounts,
 * creating groups, removing groups, etc.
 * <p>
 * On the whole, the operations provided by this class such as
 * <code>addUserToGroup</code> will return <code>true</code> if the will throw
 * an <code>Exception</code> if the operation could not be carried out.  The
 * method will try and determine the error code as best as possible when
 * throwing the exception.
 * <p>
 * Additionally this class wraps around the native PHP LDAP methods providing
 * an object-oriented interface to <code>ldap_mod_add</code>, <code>ldap_mod_replace</code>,
 * etc...
 *
 * TODO: implement caching
 *
 * @since  2008-01-09
 * @author Stephen Matulewicz
 */
class DirectoryService {

	/** the connection object */
	private $conn = null;

	/** the ldap host */
	private $host = "";

	/** the ldap port */
	private $port = 389;

	/** the bind dn */
	private $bindDn = false;

	/** the bind password */
	private $bindPassword = false;

	/** the overall search base */
	private $base = "";

	/** the user base */
	private $userBase = "";

	/** the group base */
	private $groupBase = "";

	/** the default search scope */
	private $searchScope = LDAP_SCOPE_ONELEVEL;

	/** where to start looking for POSIX IDs from */
	private $POSIX_SEARCH_START = '26800';

	/** enable caching by specifying a cache directory */
	private $cacheDir = null;

	/**
	 * Configures the directory service manipulator class.
	 *
	 * @param host String the host to connect to
	 * @param port int the port to connect on
	 * @param bindDn String the dn to bind as
	 * @param bindPassword String the password to bind using
	 * @param base String the search base
	 * @param userBase String the ou under which accounts are located
	 * @param groupBase String the ou under which groups are located
	 * @param searchScope int the search scope (single or sub-tree)
	 * @param defaultAttrs array a list of returning attrs for searches
	 */
	public function configure($host=null, $port=null, $bindDn=null, $bindPassword=null,
			$base=null, $userBase=null, $groupBase=null, $searchScope=null,
			$defaultAttrs=null, $cacheDir=null) {
		if ($host !== null) { $this->host = $host; }
		if ($port !== null) { $this->port = $port; }
		if ($bindDn !== null) { $this->bindDn = $bindDn; }
		if ($bindPassword !== null) { $this->bindPassword = $bindPassword; }
		if ($base !== null) { $this->base = $base; }
		if ($userBase !== null) { $this->userBase = $userBase; }
		if ($groupBase !== null) { $this->groupBase = $groupBase; }
		if ($searchScope !== null) { $this->searchScope = $searchScope; }
		//if ($defaultAttrs !== null) { $this->searchDefaultAttrs = $defaultAttrs; }
		if ($cacheDir !== null) { $this->cacheDir = $cacheDir; }
	}

	public function getBase() {
		return $this->base;
	}

	/**
	 * Connects to an LDAP server. If the connection fails, an exception is
	 * throw, otherwise <code>True</code> is returned.
	 *
	 * @param host String the ldap server host
	 * @param port String the ldap server port (optional)
	 * @param dn String the dn to bind as (optional)
	 * @param password String the password to bind using (optional)
	 */
	public function connect($host=null, $port=null, $dn=null, $password=null) {
		if ($dn === null) {
			$dn = $this->bindDn;
		}
		if ($password === null) {
			$password = $this->bindPassword;
		}
		if ($host === null) {
			$host = $this->host;
		}
		if (!$host) {
			throw new Exception("Cannot connect to LDAP server: no host specified.");
		}
		if ($port !== null) {
			$this->conn = ldap_connect($host, $port);
		} else {
			$this->conn = ldap_connect($host, 389);
		}
		if ($dn !== null && $dn !== false) {
			ob_start();
			$r = ldap_bind($this->conn, $dn, $password);
			ob_end_clean();
			if (!$r) {
				throw new Exception("Unable to bind to LDAP server: " . ldap_error($this->conn));
			}
		}
		return true;
	}

	/**
	 * Disconnects from the LDAP server.
	 */
	public function disconnect() {
		$this->conn = null;
	}

	/**
	 * Gets the LDAP connection's error message.
	 *
	 * @return the error message for the ldap connection
	 */
	public function getErrorMessage() {
		if ($this->conn !== null) {
			return @ldap_error($this->conn);
		} else {
			return "Not connected.";
		}
	}

	/**
	 * Gets the LDAP connection's error number.
	 *
	 * @return the error number for the ldap connection
	 */
	public function getErrorNumber() {
		if ($this->conn !== null) {
			return @ldap_errno($this->conn);
		} else {
			return 1;
		}
	}

	/**
	 * Adds to an ldap entry at the attribute level.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the values to add
	 */
	public function addAttribute($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = @ldap_mod_add($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to add to the object: " . ldap_error($this->conn));
		}
	}

	/**
	 * Deletes from an ldap entry at the attribute level.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the values to remove
	 */
	public function delAttribute($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = @ldap_mod_del($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to delete from the object: " . ldap_error($this->conn));
		}
	}

	/**
	 * Adds an annotation to the account in chsEduPersonExtraDetails
	 * field in a specified format.  You must specify the uid of the
	 * person to add the annotation to, as well as the uid of the person
	 * making the annotation and the annotation iteslf.
	 *
	 * @param uid String the uid
	 * @param annotator String the uid of the person making the annotation
	 * @param annotation String the annotation itself
	 * @param string boolean if set to true, an exception is thrown if the
	 *                       annotation cannot be made
	 */
	public function addAccountAnnotation($uid, $annotator, $annotation, $strict=false) {
		$annotation = ereg_replace("\$", "", $annotation);
		$user = $this->getUser($uid);
		if (!in_array("chsEduPerson", $user['objectclass']) && !in_array('chseduperson', $user['objectclass'])) {
			if ($strict == true) {
				throw new Exception("Unable to annotate this account.");
			}
		} else {
			$now = date("Y-m-d H:i:s");
			$mods = array("chsedupersonextradetail" => "$annotator \$ $now \$ $annotation");
			$this->addAttribute($user['dn'], $mods);
		}
	}

	/**
	 * Replaces within an ldap entry at the attribute level.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the replacements to be made
	 */
	public function replaceAttribute($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = @ldap_mod_replace($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to replace the attributes: " . ldap_error($this->conn));
		}
	}

	/**
	 * Adds an object to the directory.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the object to add
	 */
	public function addObject($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = ldap_add($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to add the object: " . ldap_error($this->conn));
		}
	}

	/**
	 * Modifies an object within the directory at the object level.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the object to add
	 */
	public function modifyObject($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = ldap_modify($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to modify the object: " . ldap_error($this->conn));
		}
	}

	/**
	 * Deletes an object from the directory.
	 *
	 * @param dn String the dn of the entry to add to
	 * @param mods array an array containing the object to add
	 */
	public function deleteObject($dn, $mods) {
		if ($this->conn === null) {
			$this->connect();
		}
		$r = ldap_delete($this->conn, $dn, $mods);
		if (!$r) {
			throw new Exception("Unable to delete the object: " . ldap_error($this->conn));
		}
	}

	/**
	 * Searches through a directory starting at a particular <code>base</code>
	 * for entries which match a <code>filter</code> looking for attributes
	 * matching the array, <code>attrs</code>.
	 * <p>
	 * Results are returned as an array of associative arrays, each one
	 * containing a key of an attribute name, and an array containing the
	 * values for that attribute.
	 *
	 * @param base String the base to search
	 * @param filter String the filter to search for
	 * @param attrs Array the attributes to return
	 * @param scope int the search scope (one level, or sub-tree)
	 * @return an array of search results
	 */
	public function search($filter, $base=null, $attrs=null, $scope=null) {
		// ensure everything is connected
		if ($this->conn === null) {
			$this->connect();
		}

		// set the default args
		if ($attrs === null) {
			$attrs = $this->searchDefaultAttrs();
		}
		if ($base === null) {
			$base = $this->base;
		}
		if ($scope === null) {
			$scope = $this->searchScope;
		}
		$results = array();

		// perform the search or list (depending on the scope)
		ob_start();
		if ($scope == LDAP_SCOPE_ONELEVEL) {
			$rs = ldap_list($this->conn, $base, $filter, $attrs);
		} else {
			$rs = ldap_search($this->conn, $base, $filter, $attrs);
		}
		ob_end_clean();
		if (ldap_errno($this->conn) != 0) {
			throw new Exception("Unable to search the directory: " . ldap_error($this->conn));
		}

		// iterate through the set of results
		$info = ldap_get_entries($this->conn, $rs);
		for ($e=0; $e<$info['count']; $e++) {
			$entry = Array();
			$entry['dn'] = $info[$e]['dn'];
			for ($i=0; $i<$info[$e]['count']; $i++) {
				$attrName = $info[$e][$i];
				$attrList = array();
				for ($j=0; $j<$info[$e][$attrName]['count']; $j++) {
					$attrList[] = stripslashes($info[$e][$attrName][$j]);
				}
				$entry[strtolower(trim($attrName))] = $attrList;
			}
			$results[] = $entry;
		}
		ldap_free_result($rs);
		return $results;
	}

	/**
	 * Gets a list of the names of groups currently stored in the
	 * directory.  Group CNs are returned.
	 *
	 * @return an array of group names currently in the directory
	 */
	public function getGroups($filter) {
		if ($this->conn !== null) {
			$this->connect();
		}
		$groups = array();
		if ($filter == null) {
			$results = $this->search("cn=*", $this->groupBase, array('cn'), LDAP_SCOPE_ONELEVEL);
		} else {
			$results = $this->search($filter, $this->groupBase, array('cn'), LDAP_SCOPE_ONELEVEL);
		}
		foreach ($results as $result) {
			$groups[] = $result['cn'][0];
		}
		sort($groups);
		return $groups;
	}

	public function getUsers($params=null) {
		$result = array();
        $a2z_arr = range('a', 'z');
        foreach ($a2z_arr as $v) {
            $details = $this->search("uid=$v*", $this->userBase, array('cn', 'uid'), LDAP_SCOPE_ONELEVEL);
            if (is_array($details)) {
            	foreach ($details as $detail) {
            		if (isset($detail['cn'][0]) && isset($detail['uid'][0]) && strpos($detail['cn'][0], 'nactive') === false)
            			$result[] = $detail['cn'][0].'|'.$detail['uid'][0];
            	}
            }
        }
		return $result;
	}
	
	/**
	 * Gets the user details for a user identified by <code>uid</code>.
	 * Returns the user details if they exist, or null if no such details
	 * exist at all.  Some extra processing is done to extract the names
	 * of the groups to which this member belongs.
	 *
	 * @param uid String the uid of the person to get details for
	 * @param params array an array of parameters to get
	 * @return an array containing the user's details
	 */
	public function getUser($uid, $params=null) {
		$details = $this->search("uid=" . $uid, $this->userBase, $params, LDAP_SCOPE_ONELEVEL);
		if (isset($details[0])) {
			$groups = array();
			$memberDetails = $this->search("uniqueMember=".$details[0]['dn'],
					$this->groupBase, array('cn'), LDAP_SCOPE_ONELEVEL);
			foreach ($memberDetails as $member) {
				$groups[] = strtolower(trim($member['cn'][0]));
			}
			$details[0]['groups'] = $groups;
			return $details[0];
		} else {
			return null;
		}
	}

	/**
	 * Gets the group details for a group identified by <code>gid</code>.
	 * Returns the group details (if they exist) or null if no such
	 * group exists at all.  Some extra processing is done to tease out the
	 * names of each of the groups.
	 *
	 * @param gid String the group id
	 * @return an array containing the group's details
	 */
	public function getGroup($gid) {
		$details = $this->search("cn=" . $gid, $this->groupBase, null, LDAP_SCOPE_ONELEVEL);
		if ($details && $details[0]) {
			$members = array();
			if (isset($details[0]['uniquemember'])) {
				foreach ($details[0]['uniquemember'] as $dn) {
					$members[] = $this->getDnId($dn);
				}
			} else {
				$details[0]['uniquemember'] = array();
			}
			$details[0]['members'] = $members;
			return $details[0];
		} else {
			return null;
		}
	}

	public function getGroupBase() {
		return $this->groupBase;
	}

	/**
	 * Gets a single object given a DN.
	 */
	public function getSingleObject($dn, $attrs=null) {
		if ($attrs === null) {
			$attrs = $this->searchDefaultAttrs();
		}
		$parts = split(',', $dn, 2);
		$results = $this->search($parts[0], $parts[1], $attrs);
		if (isset($results[0])) {
			return $results[0];
		} else {
			return null;
		}
	}

	/**
	 * Identifies an LDAP object as being either a user, a group, or
	 * whatever, based on whatever LDAP properties it can get its hands
	 * on.
	 *
	 * @param dn String the dn
	 * @return an array containing the object types
	 */
	public function getObjectRoles($dn) {
		$parts = split(',', $dn, 2);
		$filter = $parts[0];
		$base = $parts[1];
		if ($this->conn === null) {
			$this->connect();
		}

		$results = $this->search($filter, $base);
		$types = array();

		if (sizeof($results) > 0) {
			$obj = $results[0];
			if ($this->in_array("person", $obj['objectclass'])) 			{ $types[] = "person"; }
			if ($this->in_array("groupofuniquenames", $obj['objectclass'])) { $types[] = "group"; }
			if ($this->in_array("chsstudent", $obj['objectclass'])) 		{ $types[] = "student"; }
			if ($this->in_array("chsstaff", $obj['objectclass'])) 			{ $types[] = "staff"; }
			if ($this->in_array("vmailperson", $obj['objectclass'])) 		{ $types[] = "mail"; }
			if ($this->in_array("sambasamaccount", $obj['objectclass'])) 	{ $types[] = "samba"; }
			if ($this->in_array("posixaccount", $obj['objectclass'])) 		{ $types[] = "posix"; }
		}
		return $types;
	}

	/**
	 * Determines in a case insensitive manner whether an item is in an array.
	 *
	 * @param itm Mixed the item
	 * @param arr array the array to hunt through
	 * @return true if the item is in the array
	 */
	private function in_array($itm, $arr) {
		$lcArr = array();
		foreach ($arr as $a) {
			$lcArr[] = trim(strtolower($a));
		}
		return in_array(trim(strtolower($itm)), $lcArr);
	}

	/**
	 * Locks the account of the user <code>uid</code>.
	 *
	 * @param uid String the uid of the account to lock
	 * @return true on success
	 */
	public function lockAccount($uid) {
		if ($this->conn === null) {
			$this->connect();
		}
		$user = $this->getUser($uid);
		if ($user !== null) {
			if (isset($user['nsaccountlock']) && trim(strtolower($user['nsaccountlock'][0])) == 'true') {
					return false;
			}
			$mod = array();
			$mod['nsaccountlock'] = 'true';
			return @ldap_mod_replace($this->conn, $user['dn'], $mod);
		} else {
			throw new Exception("Unable to lock the account: The specified user could not be found.");
		}
	}

	/**
	 * Unlocks the account of a user <code>uid</code>.
	 *
	 * @param uid String the uid of the accoutn to unlock
	 * @return true on success
	 */
	public function unlockAccount($uid) {
		if ($this->conn === null) {
			$this->connect();
		}
		$user = $this->getUser($uid);
		if ($user !== null) {
			if (isset($user['nsaccountlock']) && trim(strtolower($user['nsaccountlock'][0])) == 'false') {
					return false;
			}
			$mod = array();
			$mod['nsaccountlock'] = 'false';
			return @ldap_mod_replace($this->conn, $user['dn'], $mod);
		} else {
			throw new Exception("Unable to unlock the account: The specified user could not be found.");
		}
	}

	/**
	 * Annotates an account by filling in the <code>chsedupersonextradetail</code>
	 * field in a specified way.  An exception will be thrown if the account
	 * cannot be annotated because the user is not a chsEduPerson.  Annotations
	 * are just pieces of plain text that one account holder (usually an admin) can
	 * attach to another account.
	 *
	 * @param uid String the uid of the account to annotate
	 * @param annotator String the uid of the person making the annotation
	 * @param annotation String the annotation being made
	 * @return true on success
	 */
	public function annotateAccount($uid, $annotator, $annotation) {
		if ($this->conn === null) {
			$this->connect();
		}
		$user = $this->getUser($uid);
		if ($user !== null) {
			if ($this->in_array("chseduperson", $user['objectclass'])) {
				$annotation = str_replace("$ ", "", $annotation);
				$annotation = str_replace(" $", "", $annotation);
				$annotation = str_replace("$", "", $annotation);
				$now = date('Y-m-d H:i:s');
				$mods = array();
				$mods['chsedupersonextradetail'] = "$annotator \$ $now \$ $annotation";
				return @ldap_mod_add($this->conn, $user['dn'], $mods);
			} else {
				throw new Exception("Cannot add annotation: The user '$uid' is not a CHS Eduperson.");
			}
		} else {
			throw new Exception("Cannot add annotation: The user '$uid' does not exist.");
		}
		return false;
	}

	/**
	 * Creates a group.
	 *
	 * @param gid String the group ID to create
	 */
	public function createGroup($gid) {
		if ($this->conn === null) {
			$this->connect();
		}
		$skel = Array();
		$skel['objectclass'] = array();
		$skel['objectclass'][] = 'top';
		$skel['objectclass'][] = 'groupOfUniqueNames';
		$skel['cn'] = array();
		$skel['cn'][] = $gid;
		$dn = "cn=" . $gid . "," . $this->groupBase;
		ob_start();
		ldap_add($this->conn, $dn, $skel);
		ob_end_clean();
		if (ldap_errno($this->conn) != 0) {
			throw new Exception("Unable to add group: " . ldap_error($this->conn));
		}
		return true;
	}

	/**
	 * Deletes a group.
	 *
	 * @param gid String the group ID to delete
	 */
	public function deleteGroup($gid) {
		if ($this->conn === null) {
			$this->connect();
		}
		$group = $this->getGroup($gid);
		if ($group !== null) {
			$members = $group['members'];
			foreach ($members as $uid) {
				$this->removeUserFromGroup($uid, $gid);
			}
			$dn = "cn=" . $gid . "," . $this->groupBase;
			ldap_delete($this->conn, $dn);
		} else {
			throw new Exception("Unable to delete group: The group does not exist.");
		}
	}

	/**
	 * Removes a user from a group.
	 *
	 * @param uid String the uid of the user to add to the group
	 * @param gid String the gid of the user to add to the group
	 * @return true if the user could be removed from the group
	 */
	public function removeUserFromGroup($uid, $gid) {
		$config = Zend_Registry::get('config');
		$memberof_attrib = $config->ldapdirectory->attrib->memberof;
		// validate input, ensure that the user and group details exist
		if (!$uid || $uid == '' || !$gid || $gid == '') {
			throw new Exception("Unable to remove user from group: Neither the user UID nor " .
					"the group GID are allowed to be blank.");
		}

		// try to connect if we're not already connected
		if ($this->conn === null) $this->connect();

		// fetch the user details, modify the user account (if it exists)
		$userDetails = $this->getUser($uid);
		$groupDetails = $this->getGroup($gid);
		$userDn = "uid=" . $uid . "," . $this->userBase;
		$groupDn = "cn=" . $gid . "," . $this->groupBase;

		// remove the ismemberof entry from the user's account
		if ($userDetails) {
			$umod = array();
			foreach ($userDetails[$memberof_attrib] as $userMemberOf) {
				if (compareDn($userMemberOf, $groupDn)) {
					$umod[$memberof_attrib] = $userMemberOf;
					ldap_mod_del($this->conn, $userDn, $umod);
					break;
				}
			}
		}

		// remove the uniquemember entry from the group record
		if ($groupDetails) {
			$umod = array();
			foreach ($groupDetails['uniquemember'] as $gum) {
				if (compareDn($gum, $userDn)) {
					$umod['uniquemember'] = $gum;
					ldap_mod_del($this->conn, $groupDn, $umod);
					break;
				}
			}
		}

		return true;
	}

	/**
	 * Adds a user to a group.  The user is identified by <code>uid</code> and
	 * the group is identified by <code>gid</code>.  Neither the uid nor the gid
	 * are allowed to be null, false or blank strings.
	 *
	 * @param uid String the user uid
	 * @param gid String the group id
	 * @return true if the user could be removed from the group
	 */
	public function addUserToGroup($uid, $gid) {
		$config = Zend_Registry::get('config');
		$memberof_attrib = $config->ldapdirectory->attrib->memberof;
		// validate the input
		if (!$uid || trim($uid) == '' || !$gid || trim($gid) == '') {
			throw new Exception("Unable to add to LDAP group: Neither the UID " .
					"nor the GID are allowed to be blank.");
		}

		// try and connect
		if ($this->conn === null) {
			$this->connect();
		}

		// ensure that the user and group exist
		$userDetails = $this->getUser($uid);
		$groupDetails = $this->getGroup($gid);
		if ($userDetails === null || $groupDetails === null) {
			throw new Exception("Unable to add to LDAP group: Either the user or the " .
					"group could not be found.");
		}

		// only add to the user's ismemberOf if they are not already
		// a member of the group
		$isMember = false;
		foreach ($userDetails[$memberof_attrib] as $mo) {
			if (compareDn($mo, $groupDetails['dn'])) {
				$isMember = true;
				break;
			}
		}
		if ($isMember == false) {
			$umod = Array($memberof_attrib => $groupDetails['dn']);
			ldap_mod_add($this->conn, $userDetails['dn'], $umod);
		}

		// only add to the group's uniequeMember if the user is
		// not already there
		$isMember = false;
		foreach ($groupDetails['uniquemember'] as $um) {
			if (compareDn($um, $userDetails['dn'])) {
				$isMember = true;
				break;
			}
		}
		if ($isMember == false) {
			$umod = Array("uniquemember" => $userDetails['dn']);
			ldap_mod_add($this->conn, $groupDetails['dn'], $umod);
		}

		return true;
	}

	/**
	 * Teases out the identifier from a DN.  So for instance, if a DN is
	 * <code>uid=smatulew,dc=usyd,dc=edu,dc=au</code> the first part is
	 * returned, minus its identifying tag (in this case, <code>smatulew</code>.)
	 *
	 * @param dn String the dn
	 * @return the identifier
	 */
	public function getDnId($dn) {
		$parts = split(',', $dn);
		$parts = split('=', $parts[0]);
		return trim(strtolower($parts[1]));
	}

	/**
	 * Gets the next available POSIX ID from LDAP.
	 *
	 * @return the next available posix ID from LDAP
	 */
	public function getNextPosixId() {
		$cachedNumber = $this->cacheDir . '/lastPosixId.tmp';
		if (file_exists($cachedNumber)) {
			$startAt = (int) trim(join('', file($cachedNumber)));
		} else {
			$startAt = $this->POSIX_SEARCH_START;
		}

		// keep doing queries until an available ID is found
		$nextAvailableId = $startAt;
		$found = true;
		while ($found == true) {
			$filter = "(uidnumber=$nextAvailableId)";
			$results = $this->search($filter, $this->base, array('uidnumber'), LDAP_SCOPE_SUBTREE);
			echo $filter . "<br>";
			if (sizeof($results) > 0) {
				$found = true;
				$nextAvailableId ++;
			} else {
				$found = false;
			}
		}

		// write the good number to a temporary spot on disk
		if (is_writable($this->cacheDir)) {
			$fh = fopen($cachedNumber, 'w');
			fwrite($fh, $nextAvailableId);
			fclose($fh);
		}

		// return the ID
		return $nextAvailableId;
	}
	
	/** the default set of search attributes to return */
	public function searchDefaultAttrs() {
		return array('objectclass', 'userpassword', 'nsaccountlock',
			'cn', 'uid', Zend_Registry::get('config')->ldapdirectory->attrib->memberof,
			'uniquemember', 'chsedupersonextradetail', 'givenname',
			'sn', 'chsedupersonprimaryfacultyaffiliation', 'otheraffiliations', 'mail',
			'telephonenumber', 'facsimiletelephonenumber', 'l', 'postaladdress',
			'description', 'labeleduri', 'title', 'mobile', 'chsmobileispublic',
			'chsedupersondisplaymail', 'chsedupersonmailispublic', 'roomnumber',
			'chsedupersonmiddlenames', 'usydpersonsalutation', 'o',
			'edupersonprimaryaffiliation', 'edupersonaffiliation', 'manager',
			'chsedupersonbirthdate', 'chsedupersonsex', 'chsstaffid', 'chsedupersonbiographicalinfo',
			'vmailalternateaddress', 'vmailvacationmessage', 'vmailforwardingaddress',
			'chsstaffeducationalhistory', 'vmailmailquota', 'uidnumber', 'gidnumber',
			'homedirectory', 'gecos', 'loginshell', 'usydstudentid','chsstudenteducationalhistory',
			'chsstudentenrolledyear','chsstudentclinicalschool','chsstudentclinicalsuballocation');
	}
}

/**
 * Normalizes a given DN.  Because different DNs have different types of
 * whitespace but would equal the same thing.  For instance 'uid=bob' is the
 * same in queries as 'uid =   bob'. This function, imported from the old, old
 * account tool (by Chris Albone) normalizes the whitespace in a DN.
 *
 * @param dn String a dn
 * @return string
 */
function normalizeDn($dn) {
	$newdn = trim($dn);
	$newdn = str_replace(", ",",",$newdn);
	$newdn = str_replace(" ,",",",$newdn);
	$newdn = str_replace(", ",",",$newdn);
	$newdn = str_replace(" ,",",",$newdn);
	$newdn = str_replace(", ",",",$newdn);
	$newdn = str_replace(" ,",",",$newdn);
	$newdn = str_replace(",",", ",$newdn);
	$newdn = strtolower($newdn);
	return $newdn;
}

/**
 * Compares two DNs.  Compares two DNs by first normalizing the two DNs
 * and then comparing the normalized, trimmed, lowercase versions.
 *
 * @param dn1 String the first dn
 * @param dn2 String the second dn
 * @return true if the two DNs are equal in their normalized state
 */
function compareDn($dn1, $dn2) {
	return (normalizeDn(strtolower($dn1)) === normalizeDn(strtolower($dn2)));
}

?>
