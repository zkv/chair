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
class Chair_DAO_Data_Class implements ArrayAccess, Countable, IteratorAggregate
{
	private $daoProcessor;
	private $database;
	private $attributes;
	private $name;
	private $criterias = array();
	private $links = array();
	private $group = array();
	private $order = array();
	private $having;
	public function getName() 
	{
		return $this->name;
	}
	public function getDatabase() 
	{
		return $this->database;
	}
	public function getAttributes()
	{
		return $this->attributes;
	}
	public function getDaoProcessor()
	{
		return $this->daoProcessor;
	}
	public function isAttributeExist($attributeName)
	{
		return isset($this->attributes[$attributeName]);
	}
	public function __isset($attributeName) 
	{
		return $this->isAttributeExist($attributeName);
	}
	public function __get($attributeName)
	{
		return $this->getAttribute($attributeName);
	}
	public function getAttribute($attributeName)
	{
		return $this->attributes[$attributeName];
	}
	public function __construct($name, $database) 
	{
		$this->name = $name;
		$this->database = $database;
		$this->attributes = $this->database->$name->getAttributes();
		$this->daoProcessor = new Chair_DAO_Processor($database);
	}
	public function count() 
	{
		$results = $this->generate()->limit(0)->countAll()->exportArray();
		if (!isset($results[$this->name]['foundRows'])) {
			throw new Exception('Count rows error');
		}
		return (int)$results[$this->name]['foundRows'];
	}
	public function offsetGet($index) 
	{
		$result = $this->generate()->limit(1)->offset($index)->exportArray();
		if (isset($result[$this->name][0])) {
			return new Chair_DAO_Data_Object($this->filter($result[$this->name][0]), $this);
		}
		throw new Exception("Record #{$index} is not exist");
	}
	public function offsetSet($index, $value) 
	{
		if ($index === null) {
			return $this->add($value);
		}
		$this[$index]->update($value);
	}
	public function offsetExists($index) 
	{
		$result = $this->generate()->limit(1)->offset($index)->exportArray();
		return isset($result[$this->name][0]);
	}
	public function offsetUnset($index) 
	{
		$this->delete($this[$index]->id);
        }
	public function getIterator() 
	{
		$countAndResults = $this->get(null, null, true);
		return new Chair_DAO_Data_Class_Iterator($countAndResults['count'], $countAndResults['values']);
	}
	public function get($limit = null, $offset = null, $withCount = false) 
	{
		$daoQuery = $this->generate();
		if (isset($limit)) {
			$daoQuery->limit($limit);
		}
		if (isset($offset)) {
			$daoQuery->offset($offset);
		}
		if ($withCount !== false) {
			$daoQuery->countAll();
		}
		$result = $daoQuery->exportArray();
		if (!count($result[$this->name])) {
			return array();
		}
		if ($withCount !== false) {
			$count = $result[$this->name]['foundRows'];
			unset($result[$this->name]['foundRows']);
		}
		$objectsArray = array();
		foreach ($result[$this->name] as $value) {
			$objectsArray[] = new Chair_DAO_Data_Object($this->filter($value), $this);
		}
		if ($withCount === false) {
			return $objectsArray;
		}
		return array('count' => $count, 'values' => $objectsArray);
	}
	public function add($array) 
	{
		return $this->daoProcessor->addDataObject($this->name, $array);
	}
	public function find($id) 
	{
		call_user_func_array(array($dao = $this->daoProcessor->getDataObjects()->from($this->name), 'get'), array_merge(array('id'), array_keys($this->daoProcessor->getAttributes($this->name))));
		$result = $dao->withCriteria('id', 'equal', $id)->exportArray();
		if (isset($result[$this->name][0])) {
			return new Chair_DAO_Data_Object($result[$this->name][0], $this);
		} 
		throw new Exception("Data object #{$id} is not found");
	}
	public function delete($id) 
	{
		return $this->daoProcessor->deleteDataObject($this->name, $id);
	}
	public function deleteAll() 
	{
		foreach ($this->get() as $object) {
			$this->delete($object->id);
		}
	}
	private function filter($array) 
	{
		$result = array();
		foreach (array_merge(array('id'), array_keys($this->getAttributes())) as $key) {
			$result[$key] = $array[$key];
		}
		return $result;
	}
	public function addCriteria() 
	{
		$clone = clone $this;
		$clone->criterias[] = func_get_args();
		return $clone;
	}
	public function orderAsc($order) 
	{
		$clone = clone $this;
		$clone->order[] = array('attribute' => $order, 'type' => 'ASC');
		return $clone;
	}
	public function orderDesc($order) 
	{
		$clone = clone $this;
		$clone->order[] = array('attribute' => $order, 'type' => 'DESC');
		return $clone;
	}
	public function groupAsc($group) 
	{
		$clone = clone $this;
		$clone->group[] = array('attribute' => $group, 'type' => 'ASC');
		return $clone;
	}
	public function groupDesc($group) 
	{
		$clone = clone $this;
		$clone->group[] = array('attribute' => $group, 'type' => 'DESC');
		return $clone;
	}
	public function having($having) 
	{
		$clone = clone $this;
		$clone->having = $having;
		return $clone;
	}
	public function addLink($selectTable) 
	{
		$clone = clone $this;
		$clone->links[] = $selectTable;
		return $clone;
	}
	private function generate() 
	{
		$object = $this->daoProcessor->getDataObjects()->from($this->name);
		$params = array_merge(array('id'), array_keys($this->daoProcessor->getAttributes($this->name)));
		call_user_func_array(array($object, 'get'), $params);
		foreach ($this->criterias as $criteria) {
			call_user_func_array(array($object, 'withCriteria'), $criteria);
		}
		foreach ($this->group as $group) {
			if ($group['type'] === 'ASC') {
				$object->groupAsc($group['attribute']);
			} elseif ($group['type'] === 'DESC') {
				$object->groupDesc($group['attribute']);
			} else {
				throw new Exception('Group type is unknown');
			}
		}
		if (isset($this->having)) {
			$object->having($this->having);
		}
		foreach ($this->order as $order) {
			if ($order['type'] === 'ASC') {
				$object->orderAsc($order['attribute']);
			} elseif ($order['type'] === 'DESC') {
				$object->orderDesc($order['attribute']);
			} else {
				throw new Exception('Order type is unknown');
			}
		}
		foreach ($this->links as $key => $link) {
			$object->withLink($link->generate());
		}
		return $object;
	}
}
