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
class Chair_DAL_Select_Query 
{
	public $state = array();
	public function calcFoundRows() 
	{
		$this->state['calc_found_rows'] = true;
		return $this;
	}
	public function distinct() 
	{
		$this->state['distinct'] = true;
		return $this;
	}
	public function target($target) 
	{
		if (!isset($target) || !is_string($target)) {
			throw new Exception('Target should be a string');
		}
		$this->state['target'] = $target;
		return $this;
	}
	public function source($table)
	{
		if (!isset($table) || !is_string($table)) {
			throw new Exception('Source should be a string');
		}
		$this->state['source'] = $table;
		return $this;
	}
	public function where($criteria) 
	{
		if (!isset($criteria) || !is_string($criteria)) {
			throw new Exception('Criteria should be a string');
		}
		$this->state['where_criteria'] = $criteria;
		return $this;
	}
	public function groupAsc($group)
	{
		if (!isset($group) || !is_string($group)) {
			throw new Exception('Group should be a string');
		}
		$this->state['group'][] = array('attribute' => $group, 'type' => 'ASC');
		return $this;
	}
	public function groupDesc($group)
	{
		if (!isset($group) || !is_string($group)) {
			throw new Exception('Group should be a string');
		}
		$this->state['group'][] = array('attribute' => $group, 'type' => 'DESC');
		return $this;
	}
	public function having($having) 
	{
		if (!isset($having) || !is_string($having)) {
			throw new Exception('Having should be a string');
		}
		$this->state['having'] = $having;
		return $this;
	}
	public function orderAsc($order)
	{
		if (!isset($order) || !is_string($order)) {
			throw new Exception('Order shuld be a string');
		}
		$this->state['order'][] = array('attribute' => $order, 'type' => 'ASC');
		return $this;
	}
	public function orderDesc($order)
	{
		if (!isset($order) || !is_string($order)) {
			throw new Exception('Order should be a string');
		}
		$this->state['order'][]  = array('attribute' => $order, 'type' => 'DESC');
		return $this;
	}
	public function limit($limit) 
	{
		if (!isset($limit) || !is_scalar($limit)) {
			throw new Exception('Limit should be scalar');
		}
		$this->state['limit'] = round((int)$limit);
		return $this;
	}
	public function offset($offset) 
	{
		if (!isset($offset) || !is_scalar($offset)) {
			throw new Exception('Limit should be scalar');
		}
		$this->state['offset'] = round((int)$offset);
		return $this;
	}
	private function checkTarget()
	{
		if (!isset($this->state['target'])) {
			throw new Exception('Cannot generate valid query without target');
		}
	}
	private function checkLimitAndOffset()
	{
		if (!isset($this->state['limit']) && isset($this->state['offset'])) {
			throw new Exception('Cannot generate valid query with offset only. You need to have limit set in order to use offset.');
		}
	}
	public function getSelect()
	{
		return 'SELECT' . (isset($this->state['calc_found_rows']) ? ' SQL_CALC_FOUND_ROWS' : '') . (isset($this->state['distinct']) ? ' DISTINCT' : '');
	}
	public function getAttributes()
	{
		$this->checkTarget();
		return " {$this->state['target']}";
	}
	public function getFrom()
	{
		return isset($this->state['source']) ? " FROM {$this->state['source']}" : '';
	}
	public function getWhere()
	{
		return isset($this->state['where_criteria']) ? " WHERE {$this->state['where_criteria']}" : '';
	}
	public function getGroup() 
	{
		$groupArray = array();
		if (!isset($this->state['group'])) {
			return '';
		}
		foreach ($this->state['group'] as $group) {
			if (!isset($group['attribute'])) {
				throw new Exception('Group attribute is empty.');
			}
			$attribute = $group['attribute'];
			if (isset($group['type'])) {
				if (!is_string($group['type']) || !in_array($type = strtoupper($group['type']), array('ASC', 'DESC'))) {
			       		throw new Exception('Group method should be asc or desc');
				}
			} else {
				$type = 'ASC';
			}
			$groupArray[] = "{$attribute} {$type}";
		}
		$attributes = implode(',', $groupArray);
		return ($attributes !== '') ? " GROUP BY {$attributes}" : '';
	}
	public function getHaving()
	{
		return isset($this->state['having']) ? " HAVING {$this->state['having']}" : '';
	}
	public function getOrder() 
	{
		$orderArray = array();
		if (!isset($this->state['order'])) {
			return '';
		}
		foreach ($this->state['order'] as $order) {
			if (!isset($order['attribute'])) {
				throw new Exception('Order attribute is empty.');
			}
			$attribute = $order['attribute'];
			if (isset($order['type'])) {
			       	if (!is_string($order['type']) || !in_array($type = strtoupper($order['type']), array('ASC', 'DESC'))) {
				       throw new Exception('Order method should be asc or desc');
			       	} 
			} else {
				$type = 'ASC';	
			}
			$orderArray[] = "{$attribute} {$type}";
		}
		$attributes = implode(',', $orderArray);
		return ($attributes !== '') ? " ORDER BY {$attributes}" : '';
	}
	public function getLimit()
	{
		$this->checkLimitAndOffset();
		return (isset($this->state['limit']) ? " LIMIT {$this->state['limit']}" : '') . (isset($this->state['offset']) ? " OFFSET {$this->state['offset']}" : '');
	}
	public function generate()
	{
		return $this->getSelect() . $this->getAttributes() .  $this->getFrom() . $this->getWhere() . $this->getGroup() . $this->getHaving() . $this->getOrder() . $this->getLimit();
	}

}
