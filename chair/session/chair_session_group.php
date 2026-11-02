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
class Chair_Session_Group 
{
        private $db;
        private $groupName;
        private $variables;
        private $sessionId;
	public function __construct($database, $sessionId, $groupName, $variables) 
	{
            	$this->db = $database;
            	$this->sessionId = $sessionId;
            	$this->groupName = $groupName;
            	$this->variables = $variables;
        }
	public function __get($name) 
	{
           	return $this->variables[$name];
        }
	public function __set($name, $value) 
	{
            	$sessionVariable = addslashes(serialize($value));
            	if (isset($this->variables[$name])) {
                	if ($this->variables[$name] !== $value) {
	                    	$query = "
        	               	 	UPDATE session_variables
               		         	SET variable_value='{$sessionVariable}'
               	        	 	WHERE
                            			session_id=(SELECT id FROM session WHERE sid='{$this->sessionId}')
                            			AND group_name='{$this->groupName}'
			    			AND variable_name='{$name}'";
			} else {
				return;
			}
		} else {
                	$query = "
                    		INSERT INTO session_variables
                    		VALUES(
                        		null,
                        		(select id from session where sid='{$this->sessionId}'),
                        		'{$this->groupName}',
                        		'{$name}',
                        		'{$sessionVariable}'
				)";
		}
		if ($this->db->query($query)) {
			$this->variables[$name] = $value;
		}
        }
	public function __isset($name) 
	{
            	return isset($this->variables[$name]);
        }
	public function __unset($name) 
	{
            	$this->db->query("
                	DELETE FROM session_variables
                	WHERE session_id=(
                    		SELECT id
                    		FROM session
                    		WHERE sid='{$this->sessionId}'
                	)
               	 	AND group_name='{$this->groupName}'
                	AND variable_name='{$name}'"
            	);
            	unset($this->variables[$name]);
        }
	public function getName() 
	{
            	return $this->groupName;
        }
	public function getVariableNames() 
	{
            	return array_keys($this->variables);
        }
}
