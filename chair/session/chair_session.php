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
class Chair_Session
{
	private $db;
	private $groups = array();
	private $sessionId;

	public function __construct($sessionId, $db)
	{
		$this->db = $db;
            	$query = "
                	SELECT group_name,variable_name,variable_value
                	FROM session_variables
                	WHERE session_id=(SELECT id FROM session WHERE sid='{$this->db->escape($this->sessionId = $sessionId)}')";
            	$sessionVariables = array();
	    	foreach ($this->db->query($query) as $values) {
		    	$sessionVariables[$values['group_name']][$values['variable_name']] = unserialize($values['variable_value']);
	    	}
	    	foreach ($sessionVariables as $groupName => $groupVariables) {
		    	$this->groups[$groupName] = new Chair_Session_Group($this->db, $this->sessionId, $groupName, $groupVariables);
		}
        }
	public function getSessionId()
	{
            	return $this->sessionId;
        }
	public function __get($name) 
	{
            	return $this->groups[$name];
        }
	public function __isset($name) 
	{
            	return isset($this->groups[$name]);
        }
	public function __unset($name) 
	{
            	$this->db->query("
                	DELETE FROM session_variables
                	WHERE session_id=(SELECT id FROM session WHERE sid='{$this->sessionId}') AND group_name='{$name}'");
            	unset($this->groups[$name]);
        }
	public function getGroupNames() 
	{
            	return array_keys($this->groups);   
        }
	public function addGroup($name) 
	{
		if (!isset($this->groups[$name])) {
			$this->groups[$name] = new Chair_Session_Group($this->db, $this->sessionId, $name, array());
		}
		return $this;
        }
}
