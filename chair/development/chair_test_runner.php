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
	class Chair_Test_Runner 
	{
                private $testClasses = array();
                private $passedTests = 0;
                private $failedTests = 0;
		private $numberOfAssertions = 0;
		public function addTestClass($testClass) 
		{
                        $this->testClasses[] = $testClass;
                }
		public function run() 
		{
                        Chair_Time_Profiler::add_control_point("Start testing process");
                        foreach ($this->testClasses as $testClass) {
                                $testObject = new $testClass;
                                $testObject->run();
                                $this->passedTests += $testObject->getNumberOfPassedTests();
                                $this->failedTests += $testObject->getNumberOfFailedTests();
                                $this->numberOfAssertions += $testObject->getNumberOfAssertions();
                        }
			Chair_Time_Profiler::add_control_point("End testing process");
                        $controlPoints = Chair_Time_Profiler::getControlPointsInfo();
                        $numberOfTests = $this->passedTests + $this->failedTests;
                        echo $this->passedTests, " passed / ", $this->failedTests, " failed.", "\n";
                	echo "Total number of tests: ", $numberOfTests, "\n";
			echo "Assertions made: ", $this->numberOfAssertions, "\n";
			echo "Tests execution time: ", $controlPoints[0]['time_interval'], "\n";
                }
        }
