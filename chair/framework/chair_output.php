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
class Chair_Output extends Chair_Filter
{
	public function executeBefore()
	{
		if (!$this->response->issetHeader('Content-type')) {
			$this->response->setHeader('Content-type', $this->getOutputMimeType());
		}
		if (!$this->response->issetVersion()) {
			if (preg_match('#^.*/(.*)$#', $this->request->getAttribute('server')->SERVER_PROTOCOL, $matches)) {
				$version = $matches[1];
			} else {
				throw new Exception('Server http version is unknown');
			}
			$this->response->setVersion($version);
		}
		$this->response->send();
	}
	private $mimeTypes = array(
		'xml' 		=> 	'text/xml'		,
		'html'		=> 	'text/html'		,
		'png'		=> 	'image/png'		,
		'text'		=> 	'text/plain'		,
	);
	private function getOutputMimeType()
	{
		if ($this->request->issetAttribute('mime')) {
			return $this->request->getAttribute('mime');
		}
		return $this->mimeTypes[$this->request->getAttribute('output')];
	}
}
