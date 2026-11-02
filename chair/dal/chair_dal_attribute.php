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
class Chair_DAL_Attribute
{
	private $name;
	private $type;
	private $required;
	private $key;
	private $extra;
	public function __construct($input)
	{
		$this->name = $input['Field'];
		$this->type = $input['Type'];
		if (($nullField = $input['Null']) === 'YES') {
			$this->required = true;
		} elseif ($nullField === 'NO') {
			$this->required = false;
		} else {
			throw new Exception('Null field in the attribute is not set');
		}
		if (($keyField = $input['Key']) === '') {
			$this->isKey = false;
		} else {
			$this->isKey = true;
			$this->keyType = $keyField;
		}
		$this->extraInformation = $input['Extra'];
	}
	public function getName() 
	{
		return $this->name;
	}
	public function getType() 
	{
		return $this->type;
	}
	public function isRequired() 
	{
		return $this->required;
	}
	public function isKey() 
	{
		return !($this->key === '');
	}
	public function getKey() 
	{
		return $this->key;
	}
	public function getExtra()
	{
		return $this->extra;
	}
}
