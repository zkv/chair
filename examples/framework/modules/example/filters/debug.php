<?php

class Debug extends Chair_Filter
{
	public function executeBefore()
	{
		echo 'in ' . __CLASS__ . ' before execution<br/>';
	}
	public function executeAfter()
	{
		echo 'in ' . __CLASS__ . ' after execution<br/>';
	}
}
