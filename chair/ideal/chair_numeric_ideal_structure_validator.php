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
class Chair_Numeric_Ideal_Structure_Validator 
{
	private $moreThen, $lessThen, $regexp, $in, $notIn, $number, $validatorFunctionName;
	public function setMoreThen($moreThen) 
	{
            	$this->moreThen = $moreThen;
        }
	public function setLessThen($lessThen) 
	{
            	$this->lessThen = $lessThen;
        }
	public function setRegexp($regexp) 
	{
            	$this->regexp = $regexp;
        }
	public function setIn($in) 
	{
            	$this->in = $in;
        }
	public function setNotIn($notIn) 
	{
            	$this->notIn = $notIn;
        }
	public function setNumber($number) 
	{
            	$this->number = $number;
        }
	public function setValidatorFunctionName($validatorFunctionName) 
	{
            	$this->validatorFunctionName = $validatorFunctionName;
        }
	private function validateMoreThen() 
	{
		if (!isset($this->moreThen)) {
			return true;
		}
            	return ($this->number > $this->moreThen);
        }
	private function validateLessThen() 
	{
		if (!isset($this->lessThen)) {
			return true;
		}
            	return ($this->number < $this->lessThen);
        }
	private function validateRegexp() 
	{
            	if (!isset($this->regexp)) {
			return true;
		}
            	preg_match($this->regexp, (string)$this->number, $matches);
            	return isset($matches[0]);
        }
	private function validateIn() 
	{
            	if (!isset($this->in)) {
			return true;
		}
            	return in_array($this->number, $this->in);
        }
	private function validateNotIn() 
	{
		if (!isset($this->notIn)) {
			return true;
		}
            	return (!in_array($this->number, $this->notIn));
        }
	private function validateWithValidator() 
	{
		if (!isset($this->validatorFunctionName)) {
			return true;
		}
            	$validationFunction = $this->validatorFunctionName;
            	return $validation_function($this->number);
        }
	private function isNumeric() 
	{
            	return is_numeric($this->number);
        }
	public function validate() 
	{
		return (
			$this->isNumeric() && 
			$this->validateWithValidator() &&
                	$this->validateMoreThen() &&
                	$this->validateLessThen() &&
                	$this->validateRegexp() &&
                	$this->validateIn() &&
                	$this->validateNotIn()
            	);
        }
}
