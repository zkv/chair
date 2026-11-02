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
class Chair_Session_Manager
{
	private $context;
	private $db;
	private $request;
	private $response;
	private $cleanerHandlers = array();
	private $regenerationHandlers = array();
	private $createdSessionId;
	public static function getInstance()
	{
		static $instance;
		return isset($instance) ? $instance : $instance = new self;
	}
	private function __construct()
	{
		$this->context = Chair_Context_Manager::getContext(__CLASS__);
		$this->db = $this->context->getDatabase();
		$this->request = $this->context->getRequest();
		$this->response = $this->context->getResponse();
	}
	public function registerCleanerHandler($callback)
	{
		$this->cleanerHandlers[] = $callback;
	}
	public function registerRegenerationHandler($callback)
	{
		$this->regnerationHandlers[] = $callback;
	}
	public function isInSession()
	{
		return 	$this->request->issetAttribute('cookies') && isset($this->request->getAttribute('cookies')->sessionId) && $this->isSessionIdExist($this->request->getAttribute('cookies')->sessionId);
	}
	public function isSessionIdExist($sessionId)
	{
		$result = $this->db->query("SELECT id FROM session WHERE sid='{$this->db->escape($sessionId)}'");
		return isset($result[0]['id']);
	}
	private function setSessionCookie($sessionId)
	{
		$this->response->setCookie('sessionId', $sessionId, time() + 60 * 60 * 24 * 100, $this->request->issetAttribute('location') ? $this->request->getAttribute('location') : '');
	}
	private function unsetSessionCookie()
	{
		$this->response->setCookie('sessionId', '', time() - 60 * 60 * 24 * 100, $this->request->issetAttribute('location') ? $this->request->getAttribute('location') : '');
	}
	public function getSession()
	{
		switch (func_num_args()) {
			case 0:
				$this->deleteOldSessions();
				if ($this->isInSession()) {
					$sessionId = $this->getCurrentSessionId();
					$this->updateSession($sessionId);
				} else {
					if (!isset($this->createdSessionId)) {
						$this->createdSessionId = $this->createSession();
					}
					$sessionId = $this->createdSessionId;
				}
				break;
			case 1:
				if ($this->isSessionIdExist(func_get_arg(0))) {
					$sessionId = func_get_arg(0);
				}
				break;
			default:
				throw new Chair_Session_Manager_Exception('Wrong number of arguments');
		}
		return new Chair_Session($sessionId, $this->db);
	}
	private function createSession()
	{
		$sessionId = $this->generateSessionId();
		$this->db->query("INSERT INTO session VALUES(null,'{$this->db->escape($sessionId)}',NOW(),NOW())");
		$this->setSessionCookie($sessionId);
		return $sessionId;
	}
	private function deleteOldSessions()
	{
		$oldSessionIds = array();
		foreach ($this->db->query('SELECT sid FROM session WHERE(DATE_SUB(NOW(), INTERVAL 1 HOUR) > last_visit_date)') as $oldSessionRow) {
			$oldSessionIds[] = $oldSessionRow['sid'];
		}
		$this->destroySession($oldSessionIds);
	}
	private function updateSession($sessionId)
	{
		$this->db->query("UPDATE session SET last_visit_date=NOW() WHERE sid='{$this->db->escape($sessionId)}'");
	}
	private function generateSessionId()
	{
		return md5(uniqid(mt_rand(), true));
	}
	private function executeCleanerHandlers($sessionIds)
	{
		$params = array($sessionIds);
		foreach ($this->cleanerHandlers as $handler) {
			call_user_func_array($handler, $params);
		}
	}
	private function getCurrentSessionId()
	{
		if (!$this->isInSession()) {
			throw new Chair_Session_Manager_Exception('We are not in session');
		}
		return $this->request->getAttribute('cookies')->sessionId;
	}
	private function executeRegenerationHandlers($currentSessionId, $newSessionId)
	{
		$params = array($currentSessionId, $newSessionId);
		foreach ($this->regenerationHandlers as $handler) {
			call_user_func_array($handler, $params);
		}
	}
	public function regenerateSession()
	{
		$this->executeRegenerationHandlers($currentSessionId = $this->getCurrentSessionId(), $newSessionId = $this->generateSessionId());
		$this->db->query("UPDATE session SET sid = '{$this->db->escape($newSessionId)}' WHERE sid = '{$this->db->escape($currentSessionId)}'");
		$this->setSessionCookie($newSessionId);
	}
	public function destroySession()
	{
		$oldSessions = array();
		if ($this->isInSession()) {
			$currentSessionId = $this->getCurrentSessionId();
		}
		if (func_num_args() == 0) {
			if (isset($currentSessionId)) {
				$oldSessions[] = $currentSessionId;
			}
		} else {
			foreach (func_get_args() as $arg) {
				if (is_scalar($arg)) {
					$oldSessions[] = (string) $arg;
				} else {
					if (!is_array($arg)) {
						throw new Chair_Session_Manager_Exception('Wrong argument type');
					}
					foreach ($arg as $potentialSessionId) {
						if (is_scalar($potentialSessionId)) {
							$oldSessions[] = (string) $potentialSessionId;
						} else {
							throw new Chair_Session_Manager_Exception('Wrong argument type');
						}
					}
				}
			}
		}
		if (count($oldSessions)) {
			$this->executeCleanerHandlers($oldSessions);
        	    	$sessionsQuoted = array();
			foreach ($oldSessions as $session) {
				$sessionsQuoted[] = "'{$this->db->escape($session)}'";
			}
            		$queryList = array();
			foreach($sessionsQuoted as $sessionId) {
				$queryList[] = "DELETE FROM session_variables WHERE session_id=(SELECT id FROM session WHERE sid={$sessionId})";
			}
            		$range = implode(',', $sessionsQuoted);    
			$queryList[] = "DELETE FROM session WHERE sid IN({$range})";
			$this->db->queryList($queryList);
			if (isset($currentSessionId)) {
				if (in_array($currentSessionId, $oldSessions)) {
					$this->unsetSessionCookie();
				}
			}
		}
        }
}
