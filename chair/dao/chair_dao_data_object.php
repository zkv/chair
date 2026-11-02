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
class Chair_DAO_Data_Object
{
	private $data;
	private $dataClass;
	private $dataClassAttributes;
	public function __construct($input, $dataClass) 
	{
		$this->dataClass = $dataClass;
		$this->dataClassAttributes = $dataClass->getAttributes();
		if ($this->isValidInput($input)) {
			$data = array();
			foreach ($this->dataClassAttributes as $dataClassAttribute) {
				$attributeName = $dataClassAttribute->getName();
				$this->data[$attributeName] = $this->correctType($input[$attributeName], $dataClassAttribute->getType());
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
	public function __get($name) 
	{
		if (isset($this->dataClassAttributes[$name])) {
			return isset($this->data[$name]) ? $this->data[$name] : null;
		}
		$selectTable = new Chair_DAO_Data_Class($name, $this->dataClass->getDatabase());
		return $selectTable->addLink($this->dataClass->addCriteria('id', 'equal', $this->data['id']));
	}

	public function update($input) 
	{
		foreach ($input as $key => $value) {
			$this->$key = $value;
		}
	}
	public function linkWith() 
	{
		$args = func_get_args();
		if (is_object($args[0])) {
			$this->dataClass->getDaoProcessor()->linkDataObjects($this->dataClass->getName(), $this->id, $args[0]->getDataClass()->getName(), $args[0]->id);
		} else {
			$this->dataClass->getDaoProcessor()->linkDataObjects($this->dataClass->getName(), $this->id, $args[0], $args[1]);
		}
	}
	public function unlinkFrom() 
	{
		$args = func_get_args();
		if (is_object($args[0])) {
			$this->dataClass->getDaoProcessor()->unlinkDataObjects($this->dataClass->getName(), $this->id, $args[0]->getDataClass()->getName(), $args[0]->id);
		} else {
			$this->dataClass->getDaoProcessor()->unlinkDataObjects($this->dataClass->getName(), $this->id, $args[0], $args[1]);
		}
	}
	private function isValidInput($input)
	{
		return count($input) === count($this->dataClassAttributes) && count(array_diff_key($input, $this->dataClassAttributes)) === 0;
	}
	public function getDataClass() 
	{
		return $this->dataClass;
	}
	public function exportArray() 
	{
		return $this->data;
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
		$this->dataClass->getDaoProcessor()->modifyDataObject($this->dataClass->getName(), $this->id, array($name => $value));
	}
	public function isEqual($daoDataObject) 
	{
		return (($daoDataObject instanceof Chair_DAO_Data_Object) &&
			(0 === strcmp(serialize($this->exportArray()), serialize($daoDataObject->exportArray()))));
	}
}
