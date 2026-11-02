<?php

class Execute_Data_Storage_System_OK extends Chair_View
{
	public function renderHTML()
	{
		$this->response->setAttribute('title', 'Here is one more title');
		$this->response->setAttribute('data', strtoupper(isset($this->slots['content'])) ? implode(',', $this->slots['content']) : '');
	}
}
