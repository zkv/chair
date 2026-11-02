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
class Chair_DAL_Database 
{
	private $name;
	private $adapter;
	public function __construct($name, Chair_DAL_Database_Adapter $adapter) 
	{
		$this->name = $name;
		$this->adapter = $adapter;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getAdapter() 
	{
		return $this->adapter;
	}
	public function query($query) 
	{
		return $this->adapter->query($query);
	}
	public function queryList($queryList) 
	{
		return $this->adapter->queryList($queryList);
	}
	public function escape($string) 
	{
		return $this->adapter->escape($string);
	}
	public function getLastInsertId() 
	{
		return $this->adapter->getLastInsertId();
	}
	public function __get($tableName) 
	{
		return new Chair_DAL_Table($tableName, $this);
	}
	public function __isset($tableName)
	{
		return in_array($tableName, $this->getTables());
	}
	public function getTables()
	{
		return $this->adapter->getTables();
	}
}
