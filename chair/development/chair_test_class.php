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
	abstract class Chair_Test_Class 
	{
                private $passed = 0;
                private $failed = 0;
                private $assertions = 0;
		private function getClass() 
		{
                        return get_class($this);
                }
		private function getTestMethods() 
		{
                        $methods = array();
			foreach (get_class_methods($this->getClass()) as $methodName) { 
				if (preg_match('/^test.+$/', $methodName)) {
					$methods[] = $methodName;
				}
			}
                        return $methods;
                }
		public function getNumberOfAssertions() 
		{
                        return $this->assertions;
                }
		public function assertEquals($expected, $value , $comments) 
		{
                        $this->assertions++;
			if (!($expected === $value)) { 
				throw new Chair_Test_Assert_Exception($expected, $value, $comments);
			}
                }
		private function passed($method) 
		{
                        $this->passed++;
                        echo 'Passed ', $this->getClass(), '::', $method, "\n";
                }
		private function failed($failedParams) 
		{
                        $this->failed++;
                        echo 'Failed ', $this->getClass(), '::', $failedParams['testMethod'], ' on ', $failedParams['line'], ' line', "\n";
			echo $failedParams['comments'], "\n";
			echo 'Expected: '; var_dump($failedParams['expected']); echo "\n";
			echo 'Got: '; var_dump($failedParams['value']); echo "\n";
                }
		public function setUp()
		{
		}
		public function tearDown()
		{
		}
		public function getNumberOfFailedTests() 
		{
                        return $this->failed;
                }
		public function getNumberOfPassedTests() 
		{
                        return $this->passed;
                }
		public function run() 
		{
                        foreach ($this->getTestMethods() as $testMethod) {
                                $this->setUp();
                                try {
                                        $this->$testMethod();
                                        $this->passed($testMethod);
                                } catch (Chair_Test_Assert_Exception $exception) {
                                        $trace = $exception->getTrace();
                                        $failedParams = array(
                                        	'testMethod' 	=> $testMethod,
                                        	'function' 	=> $trace[0]['function'],
                                        	'line' 		=> $trace[0]['line'],
                                        	'value' 	=> $exception->getValue(),
                                        	'expected' 	=> $exception->getExpected(),
						'comments' 	=> $exception->getComments()
					);
                                        $this->failed($failedParams);
                                }
                                $this->tearDown();
                        }
                }
        }
