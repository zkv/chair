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
class Chair_DAL_Database_Connection_Manager 
{
	private $configs = array();
	private $databases = array();
	private function __construct()
	{

	}
	public static function getInstance()
	{
		static $instance;
		return isset($instance) ? $instance : $instance = new self;
	}
	public function saveConfig($configName, $config) 
		{
		$this->configs[$configName] = $config;
		unset($this->databases[$configName]);
	}
	public function getDatabaseByConfig($configName) 
	{
		if (isset($this->databases[$configName])) {
			return $this->databases[$configName];
		}
		try {
			$adapterClassName = 'Chair_DAL_' . $this->configs[$configName]->adapter . '_Adapter';
			return $this->databases[$configName] = new Chair_DAL_Database($databaseName = $this->configs[$configName]->databaseName, new $adapterClassName($this->configs[$configName]));
		} 
		catch (Exception $e) {
			throw new Exception("Database config {$configName}  selection error. Check your config values.");
		}
	}
}
