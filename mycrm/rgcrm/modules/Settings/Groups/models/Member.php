<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Roles Record Model Class
 */
class Settings_Groups_Member_Model extends Mycrm_Base_Model {

	const MEMBER_TYPE_USERS = 'Users';
	const MEMBER_TYPE_GROUPS = 'Groups';
	const MEMBER_TYPE_ROLES = 'Roles';
	const MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';

	/**
	 * Function to get the Qualified Id of the Group Member
	 * @return <Number> Id
	 */
	public function getId() {
		return $this->get('id');
	}

	public function getIdComponents() {
		return explode(':', $$this->getId());
	}

	public function getMemberType() {
		$idComponents = $this->getIdComponents();
		if($idComponents && count($idComponents) > 0) {
			return $idComponents[0];
		}
		return false;
	}

	public function getMemberId() {
		$idComponents = $this->getIdComponents();
		if($idComponents && count($idComponents) > 1) {
			return $idComponents[1];
		}
		return false;
	}

	/**
	 * Function to get the Group Name
	 * @return <String>
	 */
	public function getName() {
		return $this->get('name');
	}

	/**
	 * Function to get the Group Name
	 * @return <String>
	 */
	public function getQualifiedName() {
		return $this->getMemberType().' - '.$this->get('name');
	}

	public static function getIdComponentsFromQualifiedId($id) {
		return explode(':', $id);
	}

	public static function getQualifiedId($type, $id) {
		return $type.':'.$id;
	}

	public static function getAllByTypeForGroup($groupModel, $type) {
		$db = PearDatabase::getInstance();

		$members = array();

		if($type == self::MEMBER_TYPE_USERS) {
			$sql = 'SELECT mycrm_users.id, mycrm_users.last_name, mycrm_users.first_name FROM mycrm_users
							INNER JOIN mycrm_users2group ON mycrm_users2group.userid = mycrm_users.id
							WHERE mycrm_users2group.groupid = ?';
			$params = array($groupModel->getId());
			$result = $db->pquery($sql, $params);
			$noOfUsers = $db->num_rows($result);

			for($i=0; $i<$noOfUsers; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$userId = $row['id'];
				$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_USERS, $userId);
				$name = getFullNameFromArray('Users', $row);
				$member = new self();
				$members[$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $name)->set('userId', $userId);
			}
		}

		if($type == self::MEMBER_TYPE_GROUPS) {
			$sql = 'SELECT mycrm_groups.groupid, mycrm_groups.groupname FROM mycrm_groups
							INNER JOIN mycrm_group2grouprel ON mycrm_group2grouprel.containsgroupid = mycrm_groups.groupid
							WHERE mycrm_group2grouprel.groupid = ?';
			$params = array($groupModel->getId());
			$result = $db->pquery($sql, $params);
			$noOfGroups = $db->num_rows($result);

			for($i=0; $i<$noOfGroups; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_GROUPS, $row['groupid']);
				$name = $row['groupname'];
				$member = new self();
				$members[$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $name)->set('groupId', $row['groupid']);
			}
		}

		if($type == self::MEMBER_TYPE_ROLES) {
			$sql = 'SELECT mycrm_role.roleid, mycrm_role.rolename FROM mycrm_role
							INNER JOIN mycrm_group2role ON mycrm_group2role.roleid = mycrm_role.roleid
							WHERE mycrm_group2role.groupid = ?';
			$params = array($groupModel->getId());
			$result = $db->pquery($sql, $params);
			$noOfRoles = $db->num_rows($result);

			for($i=0; $i<$noOfRoles; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLES, $row['roleid']);
				$name = $row['rolename'];
				$member = new self();
				$members[$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $name)->set('roleId', $row['roleid']);
			}
		}

		if($type == self::MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
			$sql = 'SELECT mycrm_role.roleid, mycrm_role.rolename FROM mycrm_role
							INNER JOIN mycrm_group2rs ON mycrm_group2rs.roleandsubid = mycrm_role.roleid
							WHERE mycrm_group2rs.groupid = ?';
			$params = array($groupModel->getId());
			$result = $db->pquery($sql, $params);
			$noOfRoles = $db->num_rows($result);

			for($i=0; $i<$noOfRoles; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLE_AND_SUBORDINATES, $row['roleid']);
				$name = $row['rolename'];
				$member = new self();
				$members[$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $name)->set('roleId', $row['roleid']);
			}
		}

		return $members;
	}

	/**
	 * Function to get Detail View Url of this member
	 * return <String> url
	 */
	public function getDetailViewUrl() {
		list($type, $recordId) = self::getIdComponentsFromQualifiedId($this->getId());
		switch ($type) {
			case 'Users'	: $recordModel = Users_Record_Model::getCleanInstance($type);
							  $recordModel->setId($recordId);
							  return $recordModel->getDetailViewUrl();


			case 'RoleAndSubordinates' :
			case 'Roles'	: $recordModel = new Settings_Roles_Record_Model();
							  $recordModel->set('roleid', $recordId);
							  return $recordModel->getEditViewUrl();

			case 'Groups'	: $recordModel = new Settings_Groups_Record_Model();
							  $recordModel->setId($recordId);
							  return $recordModel->getDetailViewUrl();
		}
	}

	/**
	 * Function to get all the groups
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAllByGroup($groupModel) {
		$db = PearDatabase::getInstance();

		$members = array();
		$members[self::MEMBER_TYPE_USERS] = self::getAllByTypeForGroup($groupModel, self::MEMBER_TYPE_USERS);
		$members[self::MEMBER_TYPE_GROUPS] = self::getAllByTypeForGroup($groupModel, self::MEMBER_TYPE_GROUPS);
		$members[self::MEMBER_TYPE_ROLES] = self::getAllByTypeForGroup($groupModel, self::MEMBER_TYPE_ROLES);
		$members[self::MEMBER_TYPE_ROLE_AND_SUBORDINATES] = self::getAllByTypeForGroup($groupModel, self::MEMBER_TYPE_ROLE_AND_SUBORDINATES);

		return $members;
	}

	/**
	 * Function to get all the groups
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAll($onlyActive=true) {
		$members = array();

		$allUsers = Users_Record_Model::getAll($onlyActive);
		foreach($allUsers as $userId => $userModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_USERS, $userId);
			$member = new self();
			$members[self::MEMBER_TYPE_USERS][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $userModel->getName());
		}

		$allGroups = Settings_Groups_Record_Model::getAll();
		foreach($allGroups as $groupId => $groupModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_GROUPS, $groupId);
			$member = new self();
			$members[self::MEMBER_TYPE_GROUPS][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $groupModel->getName());
		}

		$allRoles = Settings_Roles_Record_Model::getAll();
		foreach($allRoles as $roleId => $roleModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLES, $roleId);
			$member = new self();
			$members[self::MEMBER_TYPE_ROLES][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $roleModel->getName());

			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLE_AND_SUBORDINATES, $roleId);
			$member = new self();
			$members[self::MEMBER_TYPE_ROLE_AND_SUBORDINATES][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $roleModel->getName());
		}

		return $members;
	}
}