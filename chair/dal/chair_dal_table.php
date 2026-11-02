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
class Chair_DAL_Table implements ArrayAccess, Countable, IteratorAggregate 
{
	private $name;
	private $database;
	private $attributes = array(); 
	public function __construct($name, $database) 
	{
		$this->name = $name;
		$this->database = $database;
		$this->sql = Chair_DAL_SQL::getInstance();
		$this->initializeAttributes();
	}
	public function __isset($attributeName)
	{
		return isset($this->attributes[$attributeName]);
	}
	public function __get($name)
	{
		return $this->getAttribute($name);
	}
	private function initializeAttributes() {
		$attributeList = $this->database->query("DESCRIBE `{$this->name}`");
		foreach ($attributeList as $attributeDescriptionArray) {
			$attribute = new Chair_DAL_Attribute($attributeDescriptionArray);
			$this->attributes[$attribute->getName()] = $attribute;
		}
	}
	public function getAttribute($attributeName) {
		return $this->attributes[$attributeName];
	}
	public function getAttributes() {
		return $this->attributes;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getDatabase() 
	{
		return $this->database;
	}
	public function count() 
	{
		$query = $this->generateSelect()->target('COUNT(*) AS count')->generate();
		$result = $this->database->query($query);
		return $result[0]['count'];
	}
	public function offsetGet($index) 
	{
		$query = $this->generateSelect()->limit(1)->offset($index)->generate();
		$queryResult = $this->database->query($query);
		if (!isset($queryResult[0])) {
			throw new Exception("No {$index} offset");
		}
		$recordArray = $queryResult[0];
		return new Chair_DAL_Record($recordArray, $this);
	}
	public function offsetSet($index, $array) 
	{
		if ($index === null) {
			return $this->addRecord($array);
		}
		else {
			if (!isset($this[$index])) {
				throw new Exception('Offset do not exist');
			}
			else {
				$this->modifyRecord($this[$index], $array);
			}
		}
	}
	public function offsetExists($index) 
	{
		$query = $this->generateSelect()->limit(1)->offset($index)->generate();
		$queryResult = $this->database->query($query);
		return isset($queryResult[0]);
	}
	public function offsetUnset($index) 
	{
		$this->deleteRecord($this->offsetGet($index));
	}
	public function getIterator() 
	{
		return new Chair_DAL_Table_Iterator($this->getRecords(), count($this));
	}
	public function getRecords($limit = null, $offset = null)
	{
		if (!isset($limit) && isset($offset)) {
			throw new Exception('Limit should be set to use offset');
		}
		$selectQuery = $this->generateSelect();
		if (isset($limit)) {
			$selectQuery->limit((int)$limit);
		}
		if (isset($offset)) {
			$selectQuery->offset((int)$offset);
		}
		$query = $selectQuery->generate();
		$records = array();
		foreach ($this->database->query($query) as $record) {
			$records[] = new Chair_DAL_Record($record, $this);
		}
		return $records;
	}
	private function generateCriteriaPair($attributeName, $attributeValue) {
		if (is_null($attributeValue)) {
			return "`{$attributeName}` IS NULL";
		}
		elseif (is_numeric($attributeValue)) {
			;
		}
		elseif (is_string($attributeValue)) {
			$attributeValue = "'{$this->database->escape($attributeValue)}'";
		}
		else {
			throw new Exception('Attribute value type is not supported');
		}
		return "`{$attributeName}`={$attributeValue}";
	}
	private function generateSetPair($attributeName, $attributeValue) {
		if (is_null($attributeValue)) {
			$attributeValue = 'NULL';
		}
		elseif (is_numeric($attributeValue)) {
			;
		}
		elseif (is_string($attributeValue)) {
			$attributeValue = "'{$this->database->escape($attributeValue)}'";
		}
		else {
			throw new Exception('Attribute value type is not supported');
		}
		return "`{$attributeName}`={$attributeValue}";
	}
	private function prepareInputValues($input) {
		$valuesArray = array();
		foreach ($input as $attributeName => $attributeValue) {
			$valuesArray[] = $this->generateSetPair($attributeName, $attributeValue);
		}
		return implode($valuesArray, ',');
	}
	private function prepareCriteriaValues($input) {
		$criteriaArray = array();
		foreach ($input as $attributeName => $attributeValue) {
			$criteriaArray[] = $this->generateCriteriaPair($attributeName, $attributeValue);
		}
		return $this->sql->logicAndArray($criteriaArray);
	}
	public function addRecord($inputArray)
	{
		$this->database->query("INSERT INTO `{$this->name}` SET {$this->prepareInputValues($inputArray)}");
		return $this->database->getLastInsertId();			
	}
	public function findRecords($attributeName, $value) 
	{
		return $this->addCriteria($this->generateSetPair($attributeName, $value));
	}
	public function modifyRecord($record, $input) {
		$this->database->query("UPDATE `{$this->name}` SET {$this->prepareInputValues($input)} WHERE {$this->prepareCriteriaValues($record->exportArray())}");
	}
	public function modifyRecords($attributeName, $value, $input)
	{
		$this->database->query("UPDATE `$this->name` SET {$this->prepareInputValues($input)} WHERE {$this->generateCriteriaPair($attributeName, $value)}");
	}
	public function deleteRecord($record) 
	{	
		$this->database->query("DELETE FROM `{$this->name}` WHERE {$this->prepareCriteriaValues($record->exportArray())}");
	}
	public function deleteRecords($attributeName, $value) 
	{
		if ($value === null) {
			$this->database->query("DELETE FROM `{$this->name}` WHERE `{$attributeName}`=NULL");
		}
		$this->database->query("DELETE FROM `{$this->name}` WHERE `{$attributeName}`='{$value}'");
	}
	public function deleteAllRecords() 
	{
		$this->database->query("DELETE FROM `{$this->name}`");
	}
	private $criteria;
	private $order = array();
	private $group = array();
	private $having;
	public function addCriteria($criteria)
	{
		$clone = clone $this;
		$clone->criteria = $criteria;
		return $clone;
	}
	public function orderAsc($name)
	{
		if (!isset($name) || !is_string($name)) {
			throw new Exception('Order attribute should be string.');
		}
		$clone = clone $this;
		$clone->order[] = array('attribute' => $name, 'type' => 'ASC');
		return $clone;
	}
	public function orderDesc($name)
	{
		if (!isset($name) || !is_string($name)) {
			throw new Exception('Order attribute should be string.');
		}
		$clone = clone $this;
		$clone->order[] = array('attribute' => $name, 'type' => 'DESC');
		return $clone;
	}
	public function having($having)
	{
		$clone = clone $this;
		$clone->having = $having;
		return $clone;
	}
	public function groupAsc($name)
	{
		if (!isset($name) || !is_string($name)) {
			throw new Exception('Group attribute should be string.');
		}
		$clone = clone $this;
		$clone->group[] = array('attribute' => $name, 'type' => 'ASC');
		return $clone;
	}
	public function groupDesc($name)
	{
		if (!isset($name) || !is_string($name)) {
			throw new Exception('Group attribute should be string.');
		}
		$clone = clone $this;
		$clone->group[] = array('attribute' => $name, 'type' => 'DESC');
		return $clone;
	}
	private function generateSelect()
	{
		$selectQuery = new Chair_DAL_Select_Query();
		$selectQuery->source($this->sql->tableName($this->name));
		$attributes = array();
		foreach (array_keys($this->attributes) as $attributeName) {
			$attributes[] = $this->sql->escape($attributeName);
		}
		$selectQuery->target(implode(',', $attributes));
		if (isset($this->criteria)) {
			$selectQuery->where($this->criteria);
		}
		if (isset($this->order)) {
			foreach ($this->order as $order) {
				if ($order['type'] === 'ASC') {
					$selectQuery->orderAsc($this->sql->escape($order['attribute']));
				} elseif ($order['type'] === 'DESC') {
					$selectQuery->orderDesc($this->sql->escape($order['attribute']));
				} else {
					throw new Exception('Wrong order type');
				}
			}
		}
		if (isset($this->having)) {
			$selectQuery->having($this->having);
		}
		if (isset($this->group)) {
			foreach ($this->group as $group) {
				if ($group['type'] === 'ASC') {
					$selectQuery->groupAsc($this->sql->escape($group['attribute']));
				} elseif ($group['type'] === 'DESC') {
					$selectQuery->groupDesc($this->sql->escape($group['attribute']));
				} else {
					throw new Exception('Wrong group type');
				}
			}
		}
		return $selectQuery;
	}
}
