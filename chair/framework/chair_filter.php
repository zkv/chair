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
class Chair_Filter
{
	protected $request;
	protected $response;
	public function __construct($request, $response)
	{
		$this->request = $request;
		$this->response = $response;
	}
	public final static function getInstance($request, $response)
	{
		return new self($request, $response);
	}
	public final function execute()
	{
		$beforeResult = $this->executeBefore();
		if (isset($beforeResult)) {
			return $beforeResult;
		}
		$filters = func_get_args();
		if (isset($filters[0])) {
			$filterName = array_shift($filters);
			$result = call_user_func_array(array(new $filterName($this->request, $this->response), 'execute'), $filters);
		} 
		$afterResult = $this->executeAfter();
		if (isset($afterResult)) {
			return $afterResult;
		}
		if (isset($result)) {
			return $result;
		}
	}
	protected function executeBefore()
	{
		
	}
	protected function executeAfter()
	{
		
	}
	protected function getContext()
	{
		static $context;
		return isset($context) ? $context : $context = Chair_Context_Manager::getContext(__CLASS__);
	}
}
