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
class Chair_DAO_XML
{
	private $database;
	public static function getInstance($database)
	{
		return new self($database);
	}
	private function __construct($database)
	{
		$this->database = $database;
	}
	private function generate($path, $arguments = array())
	{
		if (!file_exists($path)) {
			throw new Exception();
		}
		$variables = array();
		foreach ($arguments as $key => $value) {
			$variables["{@$key}"] = $value;
		}
		return $this->processDAO(simplexml_load_string(str_replace(array_keys($variables), array_values($variables), file_get_contents($path))));
	}
	private function processDAO($input)
	{
		$daoProcessor = new Chair_DAO_Processor($this->database);
		$dao = $daoProcessor->getDataObjects();
		if (isset($input->name)) {
			$dao->from((string)$input->name[0]);
		}
		if (isset($input->attribute)) {
			$attributes = array();
			foreach ($input->attribute as $attribute) {
				$attributes[] = (string)$attribute;
			}
			call_user_func_array(array($dao, 'get'), $attributes);
		}
		if (isset($input->orderBy)) {
			$dao->orderBy((string)$input->orderBy);
		}
		if (isset($input->groupBy)) {
			$dao->groupBy((string)$input->groupBy);
		}
		if (isset($input->having)) {
			$dao->having((string)$input->having);
		}
		if (isset($input->orderAsc)) {
			$dao->orderAsc();
		}
		if (isset($input->orderDesc)) {
			$dao->orderDesc();
		}
		if (isset($input->groupAsc)) {
			$dao->groupAsc();
		}
		if (isset($input->groupDesc)) {
			$dao->groupDesc();
		}
		if (isset($input->limit)) {
			$dao->limit((int)$input->limit);
		}
		if (isset($input->offset)) {
			$dao->offset((int)$input->offset);
		}
		if (isset($input->criteria)) {
			foreach ($input->criteria as $criteria) {
				$dao->withCriteria((string)$criteria->attribute, (string)$criteria->type, (string)$criteria->value);
			}
		}
		if (isset($input->class)) {
			foreach ($input->class as $class) {
				$dao->withLink($this->processDAO($class));
			}
		}
		return $dao;
	}
	public function getXML($name, $input = array())
	{
		return $this->generate($name, $input)->exportXML();
	}
	public function getArray($name, $input = array())
	{
		return $this->generate($name, $input)->exportArray();
	}
}
