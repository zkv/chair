<?php

class Profile extends Chair_Filter
{
	public function executeBefore()
	{
		echo 'in ' . __CLASS__ . ' before execution<br/>';
		return 'Generate_Information_OK';
	}
	public function executeAfter()
	{
		echo 'in ' . __CLASS__ . ' after execution<br/>';
	}
}
