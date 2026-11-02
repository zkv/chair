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
class Chair_String_Ideal_Structure_Validator 
{
	public function setMinLength($minLength) 
	{
            $this->minLength = $minLength;
        }
	public function setMaxLength($maxLenght) 
	{
            $this->maxLenght = $maxLenght;
        }
	public function setRegexp($regexp) 
	{
            $this->regexp = $regexp;
        }
	public function setString($string) 
	{
            $this->string = $string;
        }
	public function setValidatorFunctionName($functionName) 
	{
            $this->validatorFunctionName = $functionName;
        }
	private function validateMinlength() 
	{
		if (!isset($this->minLength)) {
			return true;
		}
            	return (strlen($this->string) > $this->minLength);
        }
	private function validateMaxLength() 
	{
		if (!isset($this->max_length)) {
			return true;
		}
            	return (strlen($this->string) < $this->maxLength);
        }
	private function validateRegexp() 
	{
		if (!isset($this->regexp)) {
			return true;
		}
            	preg_match($this->regexp, $this->string, $matches);
            	return isset($matches[0]);
        }
	private function validateWithValidator() 
	{
		if (!isset($this->validatorFunctionName)) {
			return true;
		}
            	$validationFunction = $this->validatorFunctionName;
            	return $validationFunction($this->string);
        }
	public function validate() 
	{
            return (
              	is_string($this->string) &&
                $this->validateWithValidator() &&
                $this->validateMinLength() &&
                $this->validateMaxLength() &&
                $this->validateRegexp()
            );
        }
    }
?>
