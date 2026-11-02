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
final class Chair_Response
{
	private $status;
	private $version;
	private $headers;
	private $cookies;
	private $content;
	
	private $statusCodes = array(
    		100 => '100 Continue',
    		101 => '101 Switching Protocols',
    		102 => '102 Processing',
    		200 => '200 OK',
    		201 => '201 Created',
    		202 => '202 Accepted',
    		203 => '203 Non-Authoritative Information',
    		204 => '204 No Content',
    		205 => '205 Reset Content',
		206 => '206 Partial Content',
		207 => '207 Multi Status',
		226 => '226 IM Used',
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',
		420 => '420 Policy Not Fulfilled',
		421 => '421 Bad Mapping',
		422 => '422 Unprocessable Entity',
		423 => '423 Locked',
		424 => '424 Failed Dependency',
		426 => '426 Upgrade Required',
		449 => '449 Retry With',
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported',
		506 => '506 Variant Also Varies',
		507 => '507 Insufficient Storage',
		509 => '509 Bandwidth Limit Exceeded',
		510 => '510 Not Extended'
	);		
	
	
	
	public function __construct()
	{
		$this->headers = array();
		$this->cookies = array();
	}
	public function setContent($content)
	{
		$this->content = $content;
	}
	public function setHeader($name, $value, $overrite = true)
	{
		if ($overrite or !$this->issetHeader($name)) {
			$this->headers[$name] = array($value);
		} else {
			$this->headers[$name][] = $value;
		}
	}
	public function issetHeader($name)
	{
		return isset($this->headers[$name]);
	}
	public function setCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = '', $httponly = '')
	{
		$this->cookies[] = array(
			'name' 		=> $name,
			'value' 	=> $value,
			'expire' 	=> $expire,
			'path' 		=> $path,
			'domain' 	=> $domain,
			'secure' 	=> $secure,
			'httponly' 	=> $httponly
		);
	}
	public function getContent()
	{
		if (!isset($this->content)) {
			throw new Exception('Content is not set.');
		}
		return $this->content;
	}
	public function getCookie($name)
	{
		if (!isset($this->cookies[$name])) {
			throw new Exception("Cookie {$name} is not set.");
		}
		return $this->cookies[$name];
	}
	public function issetContent()
	{
		return isset($this->content);
	}
	public function getHeader($name)
	{
		if (!isset($this->headers[$name])) {
			throw new Exception("Header {$name} is not found.");
		}
		return $this->headers[$name];
	}
	public function getCookies()
	{
		return $this->cookies;
	}
	public function getHeaders()
	{
		return $this->headers;
	}
	public function setStatus($status)
	{
		$this->status = $status;
	}
	public function issetStatus()
	{
		return isset($this->status);
	}
	public function getStatus()
	{
		return $this->status;
	}
	public function setVersion($version)
	{
		$this->version = $version;
	}
	public function getVersion()
	{
		return $this->version;
	}
	public function issetVersion()
	{
		return isset($this->version);
	}
	public function getStatusCode()
	{
		return $this->statusCodes[$this->getStatus()];
	}
	public function send()
	{
		if ($this->issetStatus() && $this->issetVersion()) {
			$phpSapiName = php_sapi_name();
			if (in_array($phpSapiName, array('cgi', 'cgi-fcgi'))) {
				header("Status: {$this->getStatusCode()}");
			} else {
				header("HTTP/{$this->getVersion()} {$this->getStatusCode()}");
			}
		}
		foreach ($this->headers as $name => $header) {
			foreach ($header as $value) {
				header("{$name}: {$value}");
			}
		}
		foreach ($this->cookies as $value) {
			call_user_func_array('setcookie', $value);
		}
		if (isset($this->content)) {
			echo $this->content;
		}
	}
}
