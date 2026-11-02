<?php

class Execute_Data_Storage_System extends Chair_Action
{
	public function act()
	{

		/*
		
		$exception = new Chair_Replace_Action_Exception();
		$exception->setAction('Generate_Information');
		throw $exception;

		*/

		$exception = new Chair_Replace_Plan_Exception();
		$exception->setPath('configuration/plans/start.xml');
		throw $exception;


		return 'Execute_Data_Storage_System_OK';
	}
}
