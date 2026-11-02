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
class Chair_Simple_ACL
{
	private $state = array();
	public function load($file)
	{
		if (!is_file($file)) {
			throw new Exception('Acess control list config is not found.');
		}
		foreach (simplexml_load_file($file) as $group) {
			foreach ($group as $target) {
				foreach ($target as $method) {
					$this->addRule((string)$group['name'], (string)$target['name'], (string)$method['name']);
				}
			}
		}
		return $this;
	}
	public function addRule($group, $target, $action)
	{
		$this->state[$group][$target][$action] = true;
		return $this;
	}
	public function deleteRule($group, $target, $action)
	{
		unset($this->state[$group][$target][$action]);
		return $this;
	}
	public function getGroupsByTargetAndAction($target, $action)
	{
		$groupNames = array();
		foreach ($this->state as $groupName => $group) {
			if (isset($group[$target][$action])) {
				$groupNames[] = $groupName;
			}
		}
		return $groupNames;
	}
}
