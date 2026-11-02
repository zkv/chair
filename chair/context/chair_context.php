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
final class Chair_Context
{
	private $name;
	private $commands;
	private static $objects = array();
	public function __construct($name, $commands)
	{
		$this->name = $name;
		$this->commands = $commands;
	}
	private function getObject($class)
	{
		if (!isset(self::$objects[$class])) {
			self::$objects[$class] = new $class;
		}
		return self::$objects[$class];
	}
	public function __call($method, $arguments)
	{
		if (!isset($this->commands[$method])) {
			throw new Exception("{$method} method is not defined in the {$this->name} context");
		}

		$commandClass = $this->commands[$method]['class'];
		$commandMethod = $this->commands[$method]['method'];
		
		return $commandMethod === '__construct' ? (empty($arguments) ? new $commandClass : call_user_func_array(array(new ReflectionClass($commandClass), 'newInstance'), $arguments))
							: (empty($arguments) ? $this->getObject($commandClass)->{$commandMethod}() : call_user_func_array(array($this->getObject($commandClass), $commandMethod), $arguments));
	}
}
