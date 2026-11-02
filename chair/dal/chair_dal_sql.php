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
class Chair_DAL_SQL
{
	private function __construct()
	{
	}
	public static function getInstance()
	{
		static $instance;
		return isset($instance) ? $instance : $instance = new self();
	}
	public function join($table1, $table2, $criteria = null)
	{
		return $this->joinAlgo('JOIN', $table1, $table2, $criteria);
	}
	public function leftJoin($table1, $table2, $criteria = null)
	{
		return $this->joinAlgo('LEFT_JOIN', $table1, $table2, $criteria);
	}
	public function rightJoin($table1, $table2, $criteria = null)
	{
		return $this->joinAlgo('RIGHT_JOIN', $table1, $table2, $criteria);
	}
	public function straightJoin($table1, $table2, $criteria = null)
	{
		return $this->joinAlgo('STRAIGHT_JOIN', $table1, $table2, $criteria);
	}
	private function joinAlgo($type, $table1, $table2, $criteria)
	{
		$table1 = trim($table1);
		$table2 = trim($table2);
		if (isset($criteria)) {
			$criteria = trim($criteria);
			return "{$table1} {$type} {$table2} ON {$criteria}";
		}
		else {
			return "{$table1} {$type} {$table2}";
		}
	}
	public function escape($string)
	{
		return "`$string`";
	}
	public function attributeName($attributeName)
	{
		return $this->escape(trim($attributeName));
	}
	public function fullTableName($databaseName, $tableName)
	{
		$databaseName = $this->escape($databaseName);
		$tableName = $this->escape($tableName);
		return "{$databaseName}.{$tableName}";
	}
	public function tableName($tableName)
	{
		return $this->escape(trim($tableName));
	}
	public function fullAttributeName($tableName, $attributeName)
	{
		$tableName = $this->escape(trim($tableName));
		$attributeName = $this->escape(trim($attributeName));
		return "{$tableName}.{$attributeName}";
	}
	public function enumeration($array)
	{
		return implode(',', $array);
	}
	public function tableWithAlias($table, $alias)
	{
		$table = trim($table);
		$alias = trim($alias);
		return "{$table} AS {$this->escape($alias)}";
	}
	public function nest($value)
	{
		$value = trim($value);
		return "({$value})";
	}
	public function logicAnd($criteria1, $criteria2)
	{
		$criteria1 = trim($criteria1);
		$criteria2 = trim($criteria2);
		return "{$criteria1} AND {$criteria2}";
	}
	public function logicAndArray($array) 
	{
		return implode(' AND ', array_map('trim', $array));
	}
	public function logicOr($criteria1, $criteria2)
	{
		$criteria1 = trim($criteria1);
		$criteria2 = trim($criteria2);
		return "{$criteria1} OR {$criteria2}";
	}
	public function logicOrArray($array) {
		return implode(' OR ', array_map('trim', $array));
	}
	public function logicNot($criteria)
	{
		$criteria = trim($criteria);
		return "NOT {$criteria}";
	}
	public function regexp($name, $regexp)
	{
		$name = trim($name);
		$regexp = trim($regexp);
		return "{$name} REGEXP {$regexp}";
	}
	public function in($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name} IN({$value})";
	}
	public function notIn($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name} NOT IN({$value})";
	}
	public function between($name, $left, $right)
	{
		$name = trim($name);
		$left = trim($left);
		$right = trim($right);
		return "{$name} BETWEEN {$left} AND {$right}";
	}
	public function notBetween($name, $left, $right)
	{
		$name = trim($name);
		$left = trim($left);
		$right = trim($right);
		return "{$name} NOT BETWEEN {$left} AND {$right}";
	}
	public function equal($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}={$value}";
	}
	public function isNull($name)
	{
		return "{$name} IS NULL";	
	}
	public function isNotNull($name)
	{
		return "{$name} IS NOT NULL";
	}
	public function notEqual($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}!={$value}";
	}
	public function more($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}>{$value}";
	}
	public function moreOrEqual($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}>={$value}";
	}
	public function less($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}<{$value}";
	}
	public function lessOrEqual($name, $value)
	{
		$name = trim($name);
		$value = trim($value);
		return "{$name}<={$value}";
	}
	public function getLogicTrue()
	{
		return 'TRUE';
	}
	public function getLogicFalse()
	{
		return 'FALSE';
	}
	public function getNull()
	{
		return 'NULL';
	}
}
