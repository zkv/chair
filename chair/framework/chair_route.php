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
class Chair_Route extends Chair_Filter
{
	private $route;
	private $outputType;
	private $context;
	public function __construct($request, $response) 
	{
		parent::__construct($request, $response);
		$this->context = Chair_Context_Manager::getContext(__CLASS__);
		$configuration = $request->getAttribute('configuration');
		foreach (simplexml_load_file($configuration['routes']) as $route) {
			foreach ($route->path as $path) {
				if (preg_match("#{$path}#", $this->request->getAttribute('string'), $matches)) {
					$this->request->setAttribute('attributes', $this->context->getGlobalArray($matches));
					return $this->route = $route;

				}
			}
		}
		if (!isset($this->route)) {
			throw new Exception('Route is not found');
		}
	}
	public function executeBefore()
	{
		$this->request->setAttribute('output', $this->outputType = $this->getOutputType());
		$this->request->setAttribute('plan', (string)$this->route->output->{$this->outputType}['href']);
	}
	private $outputTypes = array(
		'*/*' 				=> '*'		,
		'text/*' 			=> 'text'	,
		'application/*' 		=> 'xml'	,
		'image/*' 			=> 'png'	,
		'text/xml' 			=> 'xml'	,
		'text/html' 			=> 'html'	,
		'text/plain' 			=> 'text'	,
		'application/xml' 		=> 'xml'	,
		'application/xhtml+xml' 	=> 'xml'	,
		'image/png' 			=> 'png'	,
	);
	private function getAcceptableMediaTypes()
	{
		# In RPC 2616 said that if Accept header is not presented it mean that application accept all media types
		# All media types can be written as */*

		$serverVariables = $this->getServerVariables();
		return isset($serverVariables->HTTP_ACCEPT) ? $serverVariables->HTTP_ACCEPT : '*/*';
	}
	private function getServerVariables()
	{
		return $this->request->getAttribute('server');
	}
	private function getAcceptableMediaTypesArray()
	{
		return array_map('trim', explode(',', preg_replace('#;q\=[0-9]{1}\.[0-9]{1}#', '', $this->getAcceptableMediaTypes())));
	}
	private function getAcceptableOutputTypes()
	{
		$outputTypes = array();
		foreach ($this->getAcceptableMediaTypesArray() as $mediaType) {
			if (isset($this->outputTypes[$mediaType])) {
				$outputTypes[] = $this->outputTypes[$mediaType];
			}
		}
		return array_unique($outputTypes);
	}
	public function getOutputType()
	{
		foreach ($types = $this->getAcceptableOutputTypes() as $type) {
			if (isset($this->route->output->{$type}['href'])) {
				return $type;
			}
		}
		if (in_array('*', $types) && isset($this->route->output['default'])) {
			return (string)$this->route->output['default'];
		}
		throw new Chair_Output_Type_Exception();
	}
}
