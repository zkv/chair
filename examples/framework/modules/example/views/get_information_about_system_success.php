<?php

class Get_Information_About_System_Success extends Chair_View
{
	public function renderHTML()
	{
		$this->response->setAttribute('title', 'Here is some title');
		$this->response->setAttribute('content', $this->request->getAttribute('information'));
	}
	public function renderXML()
	{
		$this->getContext()->getHttpResponse()->setContent("<?xml version='1.0'?><test>{$this->request->getAttribute('information')}</test>");
	}
	public function renderTEXT()
	{
		$this->getContext()->getHttpResponse()->setContent("Test.");
	}
}
