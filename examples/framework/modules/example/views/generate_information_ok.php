<?php

class Generate_Information_OK extends Chair_View
{
	public function renderHTML()
	{
		$this->getContext()->getHttpResponse()->setContent(
			(isset($this->slots['title']) ? implode('|', $this->slots['title']) : '') . 
			(isset($this->slots['data']) ? implode('', $this->slots['data']) : ''));
	}	
}
