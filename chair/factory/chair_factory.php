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
class Chair_Factory
{
	private $config = array();
	private $objectsCounter = array();
	public function get()
	{
		$args = func_get_args();
		if (!isset($args[0])) {
			throw new Chair_Factory_Exception("Class name is not provided");
		}
		$className = $args[0];
		if (isset($this->config[$className]) && $this->config[$className] <= $this->objectsCounter[$className]) {
			throw new Chair_Factory_Exception("Object of the {$className} class cannot be created.");
		}
		try {
			$object = call_user_func_array(array('Chair_Creator', 'create'), $args);
			if (isset($this->objectsCounter[$className])) {
				$this->objectsCounter[$className]++;
			}
			return $object;
		} catch (Exception $e) {
			throw new Chair_Factory_Exception("Class instantiation error");
		}
	}
	public static function loadConfig($path)
	{
		foreach (simplexml_load_file($path) as $value) {
			$className = (string)$value['name'];
			$this->config[$className] = (string)$value['instances'];
			$this->objectsCounter[$className] = 0;
		}
	}
}
