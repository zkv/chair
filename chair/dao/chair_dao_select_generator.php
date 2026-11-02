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
	class Chair_DAO_Select_Generator extends Chair_DAO_Naming
	{
		private $database;
		private $counter;
		public function __construct($database)
		{
			$this->database = $database;
			$this->counter = 1;
			$this->sql = Chair_DAL_SQL::getInstance();
		}
		private function getCriteria($criteriaArray) 
		{
			foreach($criteriaArray as $keys => $values) {
				switch($values[1]) {
				case 'regexp' 		: { $criteriaResultArray[] = $this->sql->regexp(	$values[0], $values[2])			; break; }
				case 'equal' 		: { $criteriaResultArray[] = $this->sql->equal(		$values[0], $values[2])			; break; }
				case 'notEqual' 	: { $criteriaResultArray[] = $this->sql->notEqual(	$values[0], $values[2])			; break; }
				case 'in' 		: { $criteriaResultArray[] = $this->sql->in(		$values[0], $values[2])			; break; }
				case 'notIn' 		: { $criteriaResultArray[] = $this->sql->notIn(		$values[0], $values[2])			; break; }
				case 'between' 		: { $criteriaResultArray[] = $this->sql->between(	$values[0], $values[2], $values[3])	; break; }
				case 'notBetween'	: { $criteriaResultArray[] = $this->sql->notBetween(	$values[0], $values[2], $values[3])	; break; }
				case 'more' 		: { $criteriaResultArray[] = $this->sql->more(		$values[0], $values[2])			; break; }
				case 'less' 		: { $criteriaResultArray[] = $this->sql->less(		$values[0], $values[2])			; break; }
				case 'moreOrEqual' 	: { $criteriaResultArray[] = $this->sql->moreOrEqual(	$values[0], $values[2])			; break; }
				case 'lessOrEqual' 	: { $criteriaResultArray[] = $this->sql->lessOrEqual(	$values[0], $values[2])			; break; }
				case 'isNull'		: { $criteriaResultArray[] = $this->sql->isNull(	$values[0])				; break; }
				case 'isNotNull'	: { $criteriaResultArray[] = $this->sql->isNotNull(	$values[0])				; break; }
				}
			}
			return isset($criteriaResultArray) ? $this->sql->logicAndArray($criteriaResultArray) : '';
		}
		public function generate($dataObjectSelect) 
		{
			return is_array($result = $this->generateFromNormalizedInput($normalized = $this->normalize($dataObjectSelect))) ? array($normalized['tableName'] => $result) : array();
		}
		public function generateXML($dataObjectSelect)
		{
			return "<?xml version='1.0' standalone='yes'?><result>" . $this->genXML($this->generate($dataObjectSelect)) . '</result>';
		}
		public function genXML($array)
		{
			$result = '' ;
			foreach($array as $keys => $values) {
				foreach($values as $k => $v) {
					$result .= "<$keys>";
					foreach($v as $kk => $vv) {
						if (!is_array($vv)) {
							$result .= "<$kk><![CDATA[$vv]]></$kk>";
						}
						else {
							$result .= $this->genXML(array($kk => $vv));
						}
					}
					$result .= "</$keys>" ;
				}
			}
			return $result;
		}
		private function generateFromNormalizedInput($normalizedInput, $rootId = 0) 
		{	
			$sql = $this->generateSQLString($normalizedInput, $rootId);
			if ($sql === '&') {
				return '';
			}
			$result = $this->database->query($sql);
			if (isset($normalizedInput['countAll'])) {
				$foundRows = $this->database->query('SELECT FOUND_ROWS() AS foundRows');
			}
			if (isset($result[0])) {
				if ($normalizedInput['kernelInformation']['numberOfChilds']) {
					foreach($result as $idKey => $id) {
						foreach($normalizedInput['links'] as $keys => $values) {
							foreach($resArr = $this->generateFromNormalizedInput($values, $id['id']) as $kk => $vv) {
								if ($kk !== 'foundRows') {
									$result[$idKey][$values['tableName']][] = $vv;
								}
								if (isset($resArr['foundRows'])) {
									$result[$idKey][$values['tableName']]['foundRows'] = $resArr['foundRows'];
								}
							}
						}
					}
				}
			}
			if (isset($foundRows)) {
				$result['foundRows'] = $foundRows[0]['foundRows'];
			}
			return $result;
		}
		private function normalize($dataObjectSelect)
		{
			return $this->normalizeLevel3($this->normalizeLevel2($this->normalizeLevel1($dataObjectSelect)));
		}
		private function normalizeLevel1($dataObjectSelect) 
		{
			$array = $dataObjectSelect->getState();
			foreach($array as $keys => $values) {
				if (isset($values['withLink'])) {
					if (is_object($values['withLink'][0])) {
						unset($array[$keys]['withLink']);
						$array[$keys]['link'] = $this->normalizeLevel1($values['withLink'][0]);		
					}
				}
			}
			return $array;
		}
		private function normalizeLevel2($array) 
		{
			$result = array();
			$result['attributes'] = array();
			foreach($array as $keys => $values) {
				if (isset($values['from'])) {
					$result['tableName'] = $values['from'][0];
				}
				if (isset($values['get'])) {
					$result['attributes'] = $values['get'];
				}
				if (isset($values['groupAsc'])) {
					$result['group'][] = array('attribute'=> $values['groupAsc'][0], 'type' => 'ASC');
				}
				if (isset($values['groupDesc'])) {
					$result['group'][] = array('attribute'=> $values['groupDesc'][0], 'type' => 'DESC');
				}
				if (isset($values['having'])) {
					$result['having'] = $values['having'][0];
				}
				if (isset($values['orderAsc'])) {
					$result['order'][] = array('attribute'=> $values['orderAsc'][0], 'type' => 'ASC');
				}
				if (isset($values['orderDesc'])) {
					$result['order'][] = array('attribute'=> $values['orderDesc'][0], 'type' => 'DESC');
				}
				if (isset($values['countAll'])) {
					$result['countAll'] = true;
				}
				if (isset($values['withCriteria'])) {
					$result['criteria'][] = $values['withCriteria'];
				}
				if (isset($values['link'])) {
					$result['links'][] = $this->normalizeLevel2($values['link']);
				}
				if (isset($values['limit'])) {
					$result['limit'] = $values['limit'][0];
				}
				if (isset($values['offset'])) {
					$result['offset'] = $values['offset'][0];
				}
			}
			return $result;
		}
		private function normalizeLevel3($array, $parentUniqueId = 0, $parentName = '') 
		{
			$array['kernelInformation']['uniqueId'] = $this->counter;
			if ($parentUniqueId) {
				$array['kernelInformation']['parentUniqueId'] = $parentUniqueId;
				$array['kernelInformation']['isParentExist'] = true;
			} else {
				$array['kernelInformation']['isParentExist'] = false;
			}
			if ($parentName) {
				$array['kernelInformation']['parentTableName'] = $parentName;
			}
			$parentUniqueId = $this->counter;
			if (isset($array['tableName'])) {
				$parentName = $array['tableName'];
				foreach($array as $keys => $values) {
					if ($keys === 'links') {
						$linksCounter = 0;
						foreach($values as $k => $v) {
							$this->counter++ ;
							$array[$keys][$k] = $this->normalizeLevel3($v, $parentUniqueId, $parentName);
							$linksCounter++ ;
						}
						$array['kernelInformation']['numberOfChilds'] = $linksCounter;
					}
				}
				if (!isset($array['kernelInformation']['numberOfChilds'])) {
					$array['kernelInformation']['numberOfChilds'] = 0;	
				}
			}
			return $array;
		}
		private function getNextLevelCriteria($arraySelect) 
		{
			if (!isset($arraySelect['tableName'])) {
				return '';
			}
			if (isset($arraySelect['criteria'])) {
				foreach($arraySelect['criteria'] as $keys => $values) {
					$arraySelect['criteria'][$keys][0] = $this->sql->fullAttributeName(
						$arraySelect['tableName'] . $arraySelect['kernelInformation']['uniqueId'], 
						$values[0]);
				}
				$criteria = $this->getCriteria($arraySelect['criteria']);
			} 
			else {
				$criteria = 'true';
			}
			if (!$arraySelect['kernelInformation']['numberOfChilds']) {
				return $criteria;
			}
			foreach($arraySelect['links'] as $keys => $values) {
				if (!isset($values['tableName'])) {
					return '&';
				}
				$childName = $values['tableName'];
				$childUniqueId = $values['kernelInformation']['uniqueId'];
				$childAlias = $childName . $childUniqueId;
				$parentName = $arraySelect['tableName'];
				$parentUniqueId = $arraySelect['kernelInformation']['uniqueId'];
				$parentAlias = $parentName . $parentUniqueId;
				$linkTableName = $this->formDBDataClassLinkName($childName, $parentName);
				$linkTableAlias = $linkTableName . $childUniqueId;
				$criteriaCurrent = $this->sql->equal(
					$this->sql->fullAttributeName($linkTableAlias, $parentName . '_id'),
					$this->sql->fullAttributeName($parentAlias, 'id'));
				$nextLevelCriteria = $this->sql->equal(
					$this->sql->fullAttributeName($linkTableAlias, $childName . '_id'),
					$this->sql->fullAttributeName($childAlias, 'id'));
				$nextCriteria = $this->getNextLevelCriteria($values);
				$nextCriteria = $nextCriteria ? $this->sql->logicAnd($nextLevelCriteria, $nextCriteria) : $nextLevelCriteria;
				$endAddition = isset($source) ? ' AND ' . $source : '';
				$source = $this->sql->straightJoin(
					$this->sql->straightJoin(
						$criteria, 
						$this->sql->tableWithAlias($this->sql->tableName($linkTableName), $linkTableAlias),
						$criteriaCurrent),
					$this->sql->tableWithAlias($this->sql->tableName($childName), $childName . $childUniqueId),
					$nextCriteria) . $endAddition;
			}
			return $source ;
		}
		private function generateSQLString($arraySelect, $parentId) 
		{
			$sqlGen = new Chair_DAL_Select_Query();
			$attributes = array();	
			if (!isset($arraySelect['tableName'])) {
				return '&';
			}
			if (!in_array('id', $arraySelect['attributes'])) {
				$arraySelect['attributes'] = array_merge(array('id'), $arraySelect['attributes']);
			}
			foreach($arraySelect['attributes'] as $keys => $values) {
				$attributes[] = $this->sql->fullAttributeName($arraySelect['tableName'] . $arraySelect['kernelInformation']['uniqueId'], $values);
			}
			if (isset($arraySelect['criteria'])) {
				foreach($arraySelect['criteria'] as $keys => $values) {
					$arraySelect['criteria'][$keys][0] = $this->sql->fullAttributeName($arraySelect['tableName'] . $arraySelect['kernelInformation']['uniqueId'], $values[0]);
				}
				$criteria = $this->getCriteria($arraySelect['criteria']);
			}
			if ($parentId) {
				$parentElementName = $arraySelect['kernelInformation']['parentTableName'];
				$currentElementName = $arraySelect['tableName'];
				$parentElementDataObjectId = $parentId;
				$parentElementUniqueId = $arraySelect['kernelInformation']['parentUniqueId'];
				$parentElementAlias = $parentElementName . $parentElementUniqueId;
				$parentElementTableWithAlias = $this->sql->tableWithAlias($this->sql->tableName($parentElementName), $parentElementAlias);
				$currentUniqueId = $arraySelect['kernelInformation']['uniqueId'];
				$currentElementAlias = $currentElementName . $currentUniqueId;
				$currentTableFullName = $this->sql->tableWithAlias($this->sql->tableName($currentElementName), $currentElementAlias);
				$parentLevelLinkTableName = $this->formDBDataClassLinkName($parentElementName , $currentElementName);
				$parentLevelLinkTableAlias = $parentLevelLinkTableName . $currentUniqueId ;
				$parentLevelFullLinkTableName = $this->sql->tableWithAlias($this->sql->tableName($parentLevelLinkTableName), $parentLevelLinkTableAlias);
				$parentElementCriteria = $this->sql->logicAnd(
					$this->sql->equal(
						$this->sql->fullAttributeName($parentLevelLinkTableAlias, $parentElementName . '_id'), 
						$this->sql->fullAttributeName($parentElementAlias, 'id')),
					$this->sql->equal(
						$this->sql->fullAttributeName($parentElementAlias , 'id'), 
						$parentId ));
				$currentElementCriteria = $this->sql->equal(
					$this->sql->fullAttributeName($parentLevelLinkTableAlias , $currentElementName . '_id'),
					$this->sql->fullAttributeName($currentElementAlias , 'id' ));
				$source = $this->sql->straightJoin(
					$this->sql->straightJoin(
						$currentTableFullName,
						$parentLevelFullLinkTableName,
						$currentElementCriteria),
					$parentElementTableWithAlias,
					$parentElementCriteria);
			} 
			else {
				$source = $this->sql->tableWithAlias(
					$this->sql->tableName($arraySelect['tableName']),
					$arraySelect['tableName'] . $arraySelect['kernelInformation']['uniqueId' ]);
			}
			$sqlGen->distinct();
			$sqlGen->target(implode(',', $attributes));
			if (isset($arraySelect['countAll'])) {
				$sqlGen->calcFoundRows();
			}
			if (isset($arraySelect['group'])) {
				foreach ($arraySelect['group'] as $group) {
					if ($group['type'] === 'ASC') {
						$sqlGen->groupAsc($group['attribute']);
					} elseif ($group['type'] === 'DESC') {
						$sqlGen->groupDesc($group['attribute']);
					} else {
						throw new Exception('Unknown group type');
					}
				}
			}
			if (isset($arraySelect['order'])) {
				foreach ($arraySelect['order'] as $order) {
					if ($order['type'] === 'ASC') {
						$sqlGen->orderAsc($order['attribute']);
					} elseif ($order['type'] === 'DESC') {
						$sqlGen->orderDesc($order['attribute']);
					} else {
						throw new Exception('Unknown order type');
					}
				}
			}
			if (isset($arraySelect['having'])) {
				$sqlGen->having($arraySelect['having']);
			}
			if (!$arraySelect['kernelInformation']['numberOfChilds']) {
				if (isset($criteria)) {
					$sqlGen->where($criteria);
				}
			} else {
				foreach($arraySelect['links'] as $keys => $values) {
					$childName = $values['tableName'];
					$childUniqueId = $values['kernelInformation']['uniqueId'];
					$childAlias = $childName . $childUniqueId;
					$parentName = $arraySelect['tableName'];
					$parentUniqueId = $arraySelect['kernelInformation']['uniqueId'];
					$parentAlias = $parentName . $parentUniqueId;
					$linkTableName = $this->formDBDataClassLinkName($childName, $parentName);
					$linkTableAlias = $linkTableName . $childUniqueId;
					if (isset($criteria)) {
						$criteriaCurrent = $this->sql->logicAnd(
							$criteria,
							$this->sql->equal(
								$this->sql->fullAttributeName($linkTableAlias , $parentName . '_id'),
								$this->sql->fullAttributeName($parentAlias , 'id')
							));
					}
					else {
						$criteriaCurrent = $this->sql->equal(
							$this->sql->fullAttributeName($linkTableAlias, $parentName . '_id'),
							$this->sql->fullAttributeName($parentAlias, 'id'));
					}			
					$nextLevelCriteria = $this->sql->equal(
						$this->sql->fullAttributeName($linkTableAlias, $childName . '_id'),
						$this->sql->fullAttributeName($childAlias, 'id'));
					$nextCriteria = $this->getNextLevelCriteria($values);
					$nextCriteria = $nextCriteria ? $this->sql->logicAnd($nextLevelCriteria, $nextCriteria) : $nextLevelCriteria;
					$source = $this->sql->straightJoin(
						$this->sql->straightJoin(
							$source, 
							$this->sql->tableWithAlias($this->sql->tableName($linkTableName), $linkTableAlias),
							$criteriaCurrent),
						$this->sql->tableWithAlias($this->sql->tableName($childName), $childName . $childUniqueId),
						$nextCriteria);
				}
			}
			$sqlGen->source($source);
			if (isset($arraySelect['limit'])) {
				$sqlGen->limit($arraySelect['limit']);
				if (isset($arraySelect['offset'])) { 
					$sqlGen->offset($arraySelect['offset']);
				}
			}
			return $sqlGen->generate();
		}
	}
