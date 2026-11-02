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
class Chair_DAL_Record 
{
	private $data;
	private $table;
	public function __construct($input, $table) 
	{
		$this->table = $table;
		$tableAttributes = $table->getAttributes();
		if ($this->isValidInput($input, $tableAttributes)) {
			$data = array();
			foreach ($tableAttributes as $tableAttribute) {
				$attributeName = $tableAttribute->getName();
				$this->data[$attributeName] = $this->correctType($input[$attributeName], $tableAttribute->getType());
			}
		} 
		else {
			throw new Exception('Input is not valid');
		}
	}
	private function correctType($value, $type) {
		if ($value === null) {
			return $value;
		}
		if (preg_match('/varchar/', $type)) {
			return (string) $value;
		}
		if (preg_match('/int/', $type)) {
			return (int) $value;
		}
		if (preg_match('/decimal/', $type)) {
			return (int) $value;
		}
		if (preg_match('/float/', $type)) {
			return (int) $value;
		}
		if (preg_match('/double/', $type)) {
			return (int) $value;
		}
		if (preg_match('/blob/', $type)) {
			return (string) $value;
		}
		if (preg_match('/text/', $type)) {
			return (string) $value;
		}
		if (preg_match('/date/', $type)) {
			return (string) $value;
		}
		if (preg_match('/year/', $type)) {
			return (string) $value;
		}
		if (preg_match('/time/', $type)) {
			return (string) $value;
		}
	}
	private function isValidInput($input, $tableAttributes) 
	{
		return count($input) === count($tableAttributes) && count(array_diff_key($input, $tableAttributes)) === 0;
	}
	public function getTable() 
	{
		return $this->table;
	}
	public function exportArray() 
	{
		return $this->data;
	}
	public function __get($name) 
	{
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		$exploded = explode('=', $name);
		if (count($exploded) !== 2) {
			return null;
		}
		$attributeName = $exploded[0];
		$relationInformation = $exploded[1];

		if (!isset($this->$attributeName)) {
			return null;
		}

		$relation = explode('.', $relationInformation);
		
		switch (count($relation)) {
		case 1:
			return $this->getTable()->findRecords($relation[0], $this->$attributeName);
		case 2:
			$database = $this->getTable()->getDatabase();
			$tableName = $relation[0];
			if (!isset($database->$tableName)) {
				throw new Exception('Table is not exist');
			}
			return $database->$tableName->findRecords($relation[1], $this->$attributeName);
		case 3:
			$database = Chair_DAL_Database_Connection_Manager::getInstance()->getDatabaseByConfig($relation[0]);
			$tableName = $relation[1];
			if (!isset($database->$tableName)) {
				throw new Exception('Table is not exist');
			}
			return $database->$tableName->findRecords($relation[2], $this->$attributeName);
		default:
			throw new Exception("{$name} is not found");
		}
	}
	public function __unset($name)
	{
		$this->$name = null;
	}
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}
	public function __set($name, $value) 
	{
		$this->update(array($name => $value));		
	}
	public function isEqual(Chair_DAL_Record $record) 
	{
		return (0 === strcmp(serialize($this->exportArray()), serialize($record->exportArray())));
	}
	public function update($input) 
	{
		$this->table->modifyRecord($this, $input);
	}
}
