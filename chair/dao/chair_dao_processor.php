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
	class Chair_DAO_Processor extends Chair_DAO_Naming
	{
		private $database;
		public function __construct($database) 
		{
			$this->database = $database;
		}
		public function getDatabase()
		{
			return $this->database;
		}
		private function checkDataClassName($dataClassName) 
		{
			if ($this->isDataClassExist($dataClassName)) {
				return false;
			}
			$allAttributes = array();
			foreach ($this->getDataClasses() as $dataClass) {
				$allAttributes = array_merge($allAttributes, array_keys($this->getAttributes($dataClass)));
			}
			if (in_array($dataClassName, $allAttributes)) {
				return false;
			}
			if ($dataClassName === 'id') {
				return false;
			}
			if ($dataClassName === '') {
				return false;
			}
			return true;
		}
		private function checkAttributeNameAndClassNames($attributeName) 
		{
			return !$this->isDataClassExist($attributeName);
		}
		private function checkAttributeNameAndOtherAttributes($className, $attributeName) 
		{
			return !$this->isDataClassAttributeExist($className, $attributeName);
		}
		private function checkAttributeNamesToInsert($dataClassAttributeNames) 
		{
			foreach ($dataClassAttributeNames as $attributeName) {
				if (!$this->checkAttributeNameAndClassNames($attributeName)) {
					return false;
				}
				if ($attributeName === 'id') {
					return false;
				}
				if ($attributeName === '') {
					return false;
				}
			}
			return true;
		}
		private function checkAttributeNamesToAlter($className, $attributeName) 
		{
			if (!$this->checkAttributeNameAndClassNames($attributeName)) {
				return false;
			}
			if (!$this->checkAttributeNameAndOtherAttributes($className, $attributeName)) {
				return false;
			}
			return !($attributeName === 'id' || $attributeName === '');
		}
		public function addDataClass($dataClassName) 
		{
			if (!$this->checkDataClassName($dataClassName)) {
				throw new Exception("$dataClassName check failed");
			}
			$this->addTable($dataClassName);
		}
		private function addTable($tableName, $attributes = array())
		{
			if (!isset($attributes['id'])) {
				$attributes['id'] = 'INTEGER UNSIGNED NOT NULL AUTO_INCREMENT';
			}
			$attributesString = '';
			foreach ($attributes as $key => $value) {
				$attributesString .= "`$key` $value,";
			}
			$this->database->query(
				"CREATE TABLE `{$this->formDBDataClassName($tableName)}`(
					$attributesString
					PRIMARY KEY(`id`)
				) 
				ENGINE=MyISAM");
		}
		private function deleteTable($tableName)
		{
			$this->database->query("DROP TABLE IF EXISTS `{$this->formDBDataClassName($tableName)}`");
		}
		private function modifyTableName($oldTableName, $newTableName)
		{
			$this->database->query(
				"ALTER TABLE `{$this->formDBDataClassName($oldTableName)}` 
				RENAME TO `{$this->formDBDataClassName($newTableName)}`");
		}
		private function modifyTableColumnName($tableName, $oldColumnName, $newColumnName)
		{
			$this->database->query(
				"ALTER TABLE `{$this->formDBDataClassName($tableName)}` 
				CHANGE COLUMN `{$this->formDBDataClassAttributeName($oldColumnName)}` `{$this->formDBDataClassAttributeName($newColumnName)}` {$this->getDataClassAttributeType($tableName, $oldColumnName)}");
		}
		private function modifyTableColumnType($tableName, $columnName, $type)
		{
			$this->database->query(
				"ALTER TABLE `{$this->formDBDataClassName($tableName)}`
				MODIFY COLUMN `{$this->formDBDataClassAttributeName($columnName)}` $type");
		}
		private function addTableColumn($tableName, $columnName, $type)
		{
			$this->database->query(
				"ALTER TABLE `{$this->formDBDataClassName($tableName)}`
				ADD COLUMN `{$this->formDBDataClassAttributeName($columnName)}` $type");
		}
		private function deleteTableColumn($tableName, $columnName)
		{
			$this->database->query(
				"ALTER TABLE `{$this->formDBDataClassName($tableName)}`
				DROP COLUMN `{$this->formDBDataClassAttributeName($columnName)}`");
		}
		public function deleteDataClass($dataClassName) 
		{
			foreach($this->getLinks($dataClassName) as $tableLinkName) {
				$this->unlinkDataClasses($dataClassName, $tableLinkName);
			}
			$this->deleteTable($dataClassName);
		}
		public function modifyDataClassName($oldDataClassName, $newDataClassName) 
		{
			if (!$this->checkDataClassName($newDataClassName)) {
				throw new Exception("New class name {$newDataClassName} is failed");
			}
			$tableLinks = $this->getLinks($oldDataClassName);
			foreach($tableLinks as $keys => $tableLinkName) {
				$oldTableLinkName = $this->formDBDataClassLinkName($oldDataClassName, $tableLinkName);
				$newTableLinkName = $this->formDBDataClassLinkName($newDataClassName, $tableLinkName);
				$this->modifyTableName($oldTableLinkName, $newTableLinkName);
				$oldColumnName = $this->formDBDataClassLinkColumnName($oldDataClassName);
				$newColumnName = $this->formDBDataClassLinkColumnName($newDataClassName);
				$this->modifyTableColumnName($newTableLinkName, $oldColumnName, $newColumnName);
			}
			$this->modifyTableName($oldDataClassName, $newDataClassName);
		}
		public function modifyDataClassAttributeName($dataClassName, $oldAttributeName, $newAttributeName) 
		{
			if (!$this->checkAttributeNamesToAlter($dataClassName, $newAttributeName)) {
				throw new Exception("Attribute {$newAttributeName} for {$dataClassName} class check is failed");
			}
                        $dataClassAttributeType = $this->getDataClassAttributeType($dataClassName, $oldAttributeName);
			$this->modifyTableColumnName($dataClassName, $oldAttributeName, $newAttributeName, $dataClassAttributeType);
		}
		public function modifyDataClassAttributeType($dataClassName, $attributeName, $newType) 
		{
			$this->modifyTableColumnType($dataClassName, $attributeName, $newType);
		}
		public function addDataClassAttribute($dataClassName, $attributeName, $attributeType) 
		{
			if (!$this->checkAttributeNamesToAlter($dataClassName, $attributeName)) {
				throw new Exception("Attribute {$attributeName} for {$dataClassName} class check is failed");
			}
			$this->addTableColumn($dataClassName, $attributeName, $attributeType);
		}
		public function deleteDataClassAttribute($dataClassName, $attributeName) 
		{
			$this->deleteTableColumn($dataClassName, $attributeName);
		}
		public function linkDataClasses($firstDataClassName, $secondDataClassName) 
		{
			$this->addTable(
				$this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName),
				array(
					$this->formDBDataClassLinkColumnName($firstDataClassName) => 'INTEGER UNSIGNED NOT NULL', 
					$this->formDBDataClassLinkColumnName($secondDataClassName) => 'INTEGER UNSIGNED NOT NULL'));
		}
		public function unlinkDataClasses($firstDataClassName, $secondDataClassName) 
		{
			$this->deleteTable($this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName));
		}
		private function getTableNames() 
		{
			return $this->database->getTables();
		}
		public function getDataClasses() 
		{
			$tableNames = $this->getTableNames();
			$pattern = '/^([^&]+)$/';
			$dataClasses = array();
			foreach ($tableNames as $keys => $tableName) {
				preg_match($pattern, $tableName, $result);
				if (isset($result[1])) {
					$dataClasses[] = $result[1];
				}
			}
			return $dataClasses;
		}
		public function getAttributes($dataClassName) 
		{
			$arrAttributes = array();
			foreach ($this->database->query("DESCRIBE `{$this->formDBDataClassName($dataClassName)}`") as $values) {
				$columnName = $values['Field'];
                                $columnType = $values['Type'];
                                $columnNull = $values['Null'];
				if ($columnNull === 'YES') {
					$type = $columnType . ' null';
				} else {
					$type = $columnType . ' not null';
				}
				if ($columnName !== 'id') {
					$arrAttributes[$columnName] = $type;
				}
			}
			return $arrAttributes;
		}
		private function getClassLinks($dataClassName) 
		{
			$tableNames = $this->getDataClassLinkPairs();
			$classLinks = array();
			foreach ($tableNames as $keys => $values) {
				if ($values[0] === $dataClassName) {
					$classLinks[] = $values[1];
				} elseif ($values[1] === $dataClassName) {
					$classLinks[] = $values[0];
				}
			}
			return $classLinks ;
		}
		private function getDataClassLinkPairs() 
		{
			$tableNames = $this->getTableNames();
			$pattern = '/^(.+)&(.+)$/';
			$linkPairs = array();
			foreach ($tableNames as $keys => $values) {
				preg_match($pattern, $values, $result);
				if (isset($result[2])) {
					$linkPairs[] = array($result[1], $result[2]);
				}
			}
			return $linkPairs;
		}
		public function getLinks($dataClassName) 
		{
			return $this->getClassLinks($dataClassName);
		}
		public function isDataClassExist($dataClassName) 
		{
			return in_array($dataClassName, $this->getDataClasses());
		}
		public function isDataClassAttributeExist($dataClassName, $attributeName) 
		{
			return in_array($attributeName, $this->getAttributes($dataClassName));
		}
		public function isLinkExist($firstClassName, $secondClassName) 
		{
			return in_array($secondClassName, $this->getLinks($firstClassName));		
		}
		public function addDataObject($dataClassName, $arrAttributeValues) 
		{
			$tableName = $this->formDBDataClassName($dataClassName);
			$arrAttributeValuesWithId = array_merge(array('id' => null), $arrAttributeValues);
			return $this->database->$tableName->addRecord($arrAttributeValuesWithId);
		}	
		public function deleteDataObject($dataClassName, $dataClassId) 
		{
			$dataClassLinks = $this->getLinks($dataClassName);
			foreach ($dataClassLinks as $linkName) {
				$this->unlinkById($dataClassName, $dataClassId, $linkName);
			}
			$tableName = $this->formDBDataClassName($dataClassName);
			$this->database->$tableName->deleteRecords('id', $dataClassId);
		}
		private function unlinkById($firstDataClassName, $firstDataObjectId, $secondDataClassName) 
		{
			$tableName = $this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName);
			$columnName1 = $this->formDBDataClassLinkColumnName($firstDataClassName);
			$columnName2 = $this->formDBDataClassLinkColumnName($secondDataClassName);
			$this->database->$tableName->deleteRecords($columnName1, $firstDataObjectId);
		}
		public function modifyDataObject($dataClassName, $dataClassId, $arrAttributeValues) 
		{
			$tableName = $this->formDBDataClassName($dataClassName);
			$records = $this->database->$tableName->findRecords('id', $dataClassId);
			if (!isset($records[0])) {
				throw new Exception();
			}
			$this->database->$tableName->modifyRecord($records[0], $arrAttributeValues);
		}
		public function isDataObjectsLinked($firstDataClassName, $firstDataObjectId, $secondDataClassName, $secondDataObjectId) 
		{
			$tableName = $this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName);
			$columnName1 = $this->formDBDataClassLinkColumnName($firstDataClassName);
			$columnName2 = $this->formDBDataClassLinkColumnName($secondDataClassName);
			$result = $this->database->$tableName->addCriteria("`$columnName1` = $firstDataObjectId AND `$columnName2` = $secondDataObjectId");
			return isset($result[0]);
		}
		public function isDataObjectExist($dataClassName, $dataObjectId) 
		{
			$tableName = $this->formDBDataClassName($dataClassName);
			$result = $this->database->$tableName->findRecords('id', $dataObjectId);
			return isset($result[0]);
		}
		public function linkDataObjects($firstDataClassName, $firstDataObjectId, $secondDataClassName, $secondDataObjectId) 
		{
			if ($this->isDataObjectsLinked($firstDataClassName, $firstDataObjectId, $secondDataClassName, $secondDataObjectId)) {
				return;
			}
			if (!$this->isDataObjectExist($firstDataClassName, $firstDataObjectId)) {
				throw new Exception("Data object #$firstDataObjectId of the $firstDataClassName class is not exist.");
			}
			if (!$this->isDataObjectExist($secondDataClassName, $secondDataObjectId)) { 
				throw new Exception("Data object #$secondDataObjectId of the $secondDataClassName class is not exist.");
			}
			$tableName = $this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName);
			$columnName1 = $this->formDBDataClassLinkColumnName($firstDataClassName);
			$columnName2 = $this->formDBDataClassLinkColumnName($secondDataClassName);
			$this->database->$tableName->addRecord(array($columnName1 => $firstDataObjectId, $columnName2 => $secondDataObjectId));
		}
		public function unlinkDataObjects($firstDataClassName, $firstDataObjectId, $secondDataClassName, $secondDataObjectId) 
		{
			$tableName = $this->formDBDataClassLinkName($firstDataClassName, $secondDataClassName);
			$columnName1 = $this->formDBDataClassLinkColumnName($firstDataClassName);
			$columnName2 = $this->formDBDataClassLinkColumnName($secondDataClassName);
			$this->database->query("DELETE FROM `$tableName` WHERE `$columnName1` = $firstDataObjectId AND `$columnName2` = $secondDataObjectId");
		}
		public function getDataObjects() 
		{
			return new Chair_DAO($this->getDatabase());
		}
		public function getDataClassAttributeType($dataClassName, $attributeName) 
		{
                        $dataClassAttributes = $this->getAttributes($dataClassName);
			if (!isset($dataClassAttributes[$attributeName])) {
				throw new Exception("Attribute $attributeName do not exist inside $dataClassName class.");
			}
                        return $dataClassAttributes[$attributeName];
                }
		public function __get($class)
		{
			return new Chair_DAO_Data_Class($class, $this->getDatabase());
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
			$dao = $this->getDataObjects();
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
		public function getXML($path, $input = array())
		{
			return $this->generate($path, $input)->exportXML();
		}
		public function getArray($path, $input = array())
		{
			return $this->generate($path, $input)->exportArray();
		}
	}
