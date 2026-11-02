<?php
class Get_Information_About_System extends Chair_Action
{
	public function act()
	{
		$this->response->setAttribute('information', 'here is the content of the page...');

		return 'Get_Information_About_System_Success';
	}
}
