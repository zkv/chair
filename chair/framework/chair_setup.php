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
class Chair_Setup extends Chair_Filter
{
	public function executeBefore()
	{
		$context = Chair_Context_Manager::getContext(__CLASS__);
		$request = $this->request;
		$request->setAttribute('get', $context->getGlobalArray($_GET));
		$request->setAttribute('post', $context->getGlobalArray($_POST));
		$request->setAttribute('env', $context->getGlobalArray($_ENV));
		$request->setAttribute('server', $context->getGlobalArray($_SERVER));
		$request->setAttribute('files', $context->getGlobalArray($_FILES));
		$request->setAttribute('cookies', $context->getGlobalArray($_COOKIE));
		$request->setAttribute('location', preg_replace('#index\.php$#', '', $request->getAttribute('server')->SCRIPT_NAME));
		$request->setAttribute('string', '/' . preg_replace('#^' . preg_quote($request->getAttribute('location')) . '#', '', $request->getAttribute('server')->REQUEST_URI));
		$request->setAttribute('array', ($trimmedString = trim($request->getAttribute('string'), '/')) ? explode('/', $trimmedString) : array());
	}
}
