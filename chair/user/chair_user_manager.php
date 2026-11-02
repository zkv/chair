<?php
/****************************************************************************************
 *											*
 *	Copyright (c) 2008 Kiryl Zhybul							*
 *											*
 *	Permission is hereby granted, free of charge, to any person obtaining a copy	*
 *	of this software and associated documentation files (the "Software"), to deal	*
 *	in the Software without restriction, including without limitation the rights	*
 *	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell	*
 *	copies of the Software, and to permit persons to whom the Software is		*
 *	furnished to do so, subject to the following conditions:			*
 *											*
 *	The above copyright notice and this permission notice shall be included in	*
 *	all copies or substantial portions of the Software.				*
 *											*
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR	*
 *	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,	*
 *	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE	*
 *	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER		*
 *	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,	*
 *	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN	*
 *	THE SOFTWARE.									*
 *											*
 ****************************************************************************************/

/****************************************************************************************
 *											*
 * 	This file is a part of the Chair project 					*
 *											*
 *	More information about Chair project you can find at the following websites:	*
 *											*
 *	http://chair.sourceforge.net/							*
 *	http://sourceforge.net/projects/chair/						*
 *	http://code.google.com/p/chair/							*
 *											*
 ****************************************************************************************/
class Chair_User_Manager
{
	private $context;
	private function getDAO()
	{
		return $this->context->getDAO();
	}
	public static function getInstance()
	{
		static $instance;
		return isset($instance) ? $instance : $instance = new self;
	}
	private function __construct()
	{
		$this->context = Chair_Context_Manager::getContext(__CLASS__);
	}
	public function getUserId($username)
	{
		try {
			return $this->getDAO()->user->addCriteria('login', 'equal', "'{$this->getDAO()->getDatabase()->escape($username)}'")->offsetGet(0)->id;
		} catch (Exception $e) {
			throw new Exception("No user {$username} exist");
		}
	}
	public function isUserExist($username)
	{
		try {
			$this->getUserId($username);
			return true;
		} 
		catch (Exception $e) {
			return false;
		}
	}
	public function getPassword($username)
	{
		try {
			return $this->getDAO()->user->addCriteria('login', 'equal', "'{$this->getDAO()->getDatabase()->escape($username)}'")->offsetGet(0)->password;
		} catch (Exception $e) {
			throw new Exception("No user {$username} exist");
		}
	}
	public function getRegistrationDate($username)
	{
		try {
			return $this->getDAO()->user->addCriteria('login', 'equal', "'{$this->getDAO()->getDatabase()->escape($username)}'")->offsetGet(0)->date;
		} catch (Exception $e) {
			throw new Exception("No user {$username} exist");
		}
	}
	public function encodePassword($password)
	{
		return sha1($password);
	}
	public function modifyUsername($oldUsername, $newUsername)
	{
		if ($this->isUserExist($newUsername)) {
			throw new Exception("User {$newUsername} exist");
		}
		$this->getDAO()->user->find($this->getUserId($oldUsername))->login = $newUsername;
	}
	public function modifyPassword($username, $newPassword)
	{
		$this->getDAO()->user->find($this->getUserId($username))->password = $this->encodePassword($newPassword);
	}
	public function addUser($username, $password)
	{
		if ($this->isUserExist($username)) {
			throw new Exception("User {$username} exist");
		}
		$this->getDAO()->user[] = array('login' => $username, 'password' => $this->encodePassword($password));
	}
	public function deleteUser($username)
	{
		foreach ($this->getDAO()->online_user->addCriteria('user_id', 'equal', $this->getUserId($username)) as $user) {
			$this->getDAO()->online_user->delete($user->id);
		}
		$this->getDAO()->user->delete($this->getUserId($username));
	}
	public function getGroupId($group)
	{
		try {
			return $this->getDAO()->group->addCriteria('name', 'equal', "'{$this->getDAO()->getDatabase()->escape($group)}'")->offsetGet(0)->id;
		} catch (Exception $e) {
			throw new Exception("No group {$group} exist");
		}
	}
	public function addUserToGroup($username, $group)
	{
		$this->getDAO()->user->find($this->getUserId($username))->linkWith('group', $this->getGroupId($group));
	}
	public function deleteUserFromGroup($username, $group)
	{
		$this->getDAO()->user->find($this->getUserId($username))->unlinkFrom('group', $this->getGroupId($group));
	}
	public function getUserGroups($username)
	{
		$groups = array();
		foreach ($this->getDAO()->group->addLink($this->getDAO()->user->addCriteria('id', 'equal', $this->getUserId($username))) as $group) {
			$groups[] = $group->name;
		}
		return $groups;
	}
	public function isGroupExist($group)
	{
		try {
			$groupId = $this->getGroupId($group);
			return true;
		} 
		catch (Exception $e) {
			return false;
		}
	}
	public function addGroup($group)
	{
		if ($this->isGroupExist($group)) {
			throw new Exception("Group {$group} exist");
		}
		$this->getDAO()->group[] = array('name' => $group);
	}
	public function deleteGroup($group)
	{
		$this->getDAO()->group->delete($this->getGroupId($group));
	}
	public function modifyGroup($oldGroup, $newGroup)
	{
		if ($this->isGroupExist($newGroup)) {
			throw new Exception("Group {$newGroup} exist");
		}
		$this->getDAO()->group->find($this->getGroupId($oldGroup))->name = $newGroup;
	}
	public function countGroups()
	{
		return count($this->getDAO()->group);
	}
	public function countUsers()
	{
		return count($this->getDAO()->user);
	}
	public function getGroups($limit = null, $offset = null)
	{
		$groups = array();
		foreach ($this->getDAO()->group->get($limit, $offset) as $group) {
			$groups[] = $group->name;
		}
		return $groups;
	}
	public function getUsers($limit = null, $offset = null)
	{
		$users = array();
		foreach ($this->getDAO()->user->get($limit, $offset) as $user) {
			$users[] = $user->login;
		}
		return $users;
	}
	public function isUserOnline($username)
	{
		return !(count($this->getDAO()->online_user->addCriteria('user_id', 'equal', $this->getUserId($username))) === 0);
	}
	public function getUsernameBySession($sessionId)
	{
		return $this->getDAO()->user->find($this->getDAO()->online_user->addCriteria('sid', 'equal', "'{$this->getDAO()->getDatabase()->escape($sessionId)}'")->offsetGet(0)->user_id)->login;
	}
	public function makeUserOnline($username, $sessionId)
	{
		try { 
			if ($this->getUsernameBySession($sessionId) == $username) {
				return;
			}
			throw new Exception;
		} catch (Exception $e) {
			$this->getDAO()->online_user[] = array('sid' => $sessionId, 'user_id' => $this->getUserId($username));
		}
	}
	public function makeUserOffline($username, $sessionId)
	{
		try {
			if ($this->getUsernameBySession($sessionId) != $username) {
				return;
			}
			throw new Exception;
		} catch (Exception $e) {
			foreach ($this->getDAO()->online_user
				->addCriteria('sid', 'equal', "'{$this->getDAO()->getDatabase()->escape($sessionId)}'")
				->addCriteria('user_id', 'equal', $this->getUserId($username)) as $onlineUser) 
			{
				$this->getDAO()->online_user->delete($onlineUser->id);
			}
		}
	}
	public function makeUsersOfflineBySession($sessionIds)
	{
		foreach ($sessionIds as $sessionId) {
			foreach ($this->getDAO()->online_user->addCriteria('sid', 'equal', "'{$this->getDAO()->getDatabase()->escape($sessionId)}'") as $onlineUser) {
				$this->getDAO()->online_user->delete($onlineUser->id);
			}
		}
	}
	public function changeOnlineUserSessionId($oldSessionId, $newSessionId)
	{
		foreach ($this->getDAO()->online_user->addCriteria('sid', 'equal', "'{$this->getDAO()->getDatabase()->escape($oldSessionId)}'") as $onlineUser) {
			$onlineUser->sid = $newSessionId;
		}
	}
	public function deleteUsers()
	{
		foreach ($this->getUsers() as $user) {
			$this->deleteUser($user);
		}
	}
	public function deleteGroups()
	{
		foreach ($this->getGroups() as $group) {
			$this->deleteGroup($group);
		}
	}
}
