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
class Chair_DAL_MySQL_Adapter extends Chair_DAL_Database_Adapter
{
	private $link;
	private $connectionConfig;
	public function __construct($connectionConfig) 
	{
		$this->connectionConfig = $connectionConfig;
		$this->connect();
		$this->setEncoding();
		$this->selectDatabase();
	}
	private function selectDatabase()
	{
		if (!mysql_select_db($this->connectionConfig->databaseName, $this->link)) {
			throw new Exception("No {$this->connectionConfig->databaseName} database found");
		}
	}
	private function setEncoding()
	{
		$this->query("SET NAMES '{$this->connectionConfig->encoding}'");
	}
	private function connect() 
	{
		$this->link = mysql_connect($this->connectionConfig->host, 
					    $this->connectionConfig->username,
					    $this->connectionConfig->password, 
					    true);
		if (!$this->link) {
			throw new Exception('Cannot connect database');
		}
	}
	public function query($query) 
	{
		if (!is_bool($queryResult = mysql_query($query, $this->link))) {
			while ($record = mysql_fetch_array($queryResult, MYSQL_ASSOC)) {
				$result[] = $record;
			}
			return isset($result) ? $result : array();
		}
		if ($queryResult === false) {
			throw new Exception('Query failed');
		}
	}
	public function queryList($queryList) 
	{
		foreach ($queryList as $query) {
			$this->query($query);
		}
	}
	public function getLastInsertId() 
	{
		return mysql_insert_id($this->link);
	}
	public function escape($string) 
	{
		return mysql_real_escape_string($string);
	}
	public function getTables()
	{
		$tableNames = array();
		foreach ($this->query('SHOW TABLES') as $table) {
			$tableNames[] = $table["Tables_in_{$this->connectionConfig->databaseName}"];
		}
		return $tableNames;
	}
}
