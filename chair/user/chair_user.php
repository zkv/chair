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
class Chair_User
{
	private function getSessionManager()
	{
		return Chair_Session_Manager::getInstance();
	}
	private function getUserManager()
	{
		return Chair_User_Manager::getInstance();
	}
	public function login($username, $password)
	{
		if ($this->getUserManager()->getPassword($username) === $this->getUserManager()->encodePassword($password)) {
			$this->getUserManager()->makeUserOnline($username, $this->getSessionManager()->getSession()->getSessionId());
		} else {
			throw new Chair_User_Exception('Login failed');
		}
	}
	public function logout()
	{
		$this->getSessionManager()->destroySession();
	}
	public function isLoggedOn()
	{
		try {
			$this->getUsername();
			return true;

		} catch (Exception $e) {

			return false;
		}
	}
	public function getUsername()
	{
		if (!$this->getSessionManager()->isInSession()) {
			throw new Chair_User_Exception('User is not in session');
		}
		return $this->getUserManager()->getUsernameBySession($this->getSessionManager()->getSession()->getSessionId());
	}
	public function getGroups()
	{
		$groups = $this->getSessionManager()->isInSession() ? $this->getUserManager()->getUserGroups($this->getUsername()) : array();
		if (!in_array('guest', $groups)) {
			$groups[] = 'guest';
		}
		return $groups;
	}
	public function getResource($resource)
	{
		return new Chair_User_Resource($resource, $this);
	}
}
