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
try {

	ob_start();

	/* initialize parts of library that we plan to use */
	require_once('/var/www/chair/init.php');
	Chair_Init::load('autoload', 'error', 'framework', 'context', 'development', 'markup');
	
	date_default_timezone_set('Europe/Minsk');

	/* addition of the example module classes to autoload */
	Chair_Autoload::watch('modules/example/configuration/autoload/main.xml');

	Chair_Context_Manager::load('configuration/context.xml');
	Chair_Context_Manager::getContext(__CLASS__)->execute();

	ob_end_flush();

} catch (Exception $e) {

	ob_end_clean();
	
	switch (true) {
		case ($e instanceof Chair_Output_Type_Exception):

			header('Status: 406 - Not Acceptable', true, 406); 
			break;

		case ($e instanceof Exception):

			header('Status: 500 - Internal Server Error', true, 500); 
			break;
	}
}
