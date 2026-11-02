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
class Chair_Ideal_Structure_Validator 
{
	private function isValidKey($key) 
	{
		if ($key === '') {
			return false;
		}
		if (is_string($key) or is_numeric($key)) {
			return true;
		}
            	return false;
        }
	private function isIdealStructure($structure) 
	{
		if (is_numeric($structure) || is_string($structure)) {
			return true;
		}
		if (!is_array($structure)) {
			return false;
		}
		foreach($structure as $key => $value) {
			if (!$this->is_valid_key($key) || !$this->is_ideal_structure($value)) {
				return false;
			}
		}
        	return true;
	}
        
	private function validateHash($hash, $definition) 
	{
            	$hashValidator = new Chair_Hash_Ideal_Structure_Validator();
	    	if (!$hashValidator->validate($hash, $definition)) {
		    	return false;
	    	}
	    	foreach($hash as $keys => $values) {
		    	if (!$this->validateByType($values, $definition[$keys])) {
			    	return false;
		    	}
	    	}
            	return true;
        }
        
	private function validateString($string, $definition) 
	{
            	$stringValidator = new Chair_String_Ideal_Structure_Validator();
	    	if (isset($definition['min_length'])) {
		    	$stringValidator->setMinLength($definition['min_length']);
	    	}
	    	if (isset($definition['max_length'])) {
		    	$stringValidator->setMaxLength($definition['max_length']);
	    	}
	    	if (isset($definition['regexp'])) {
		    	$stringValidator->setRegexp($definition['regexp']);
	    	}
	    	if (isset($definition['validator_function_name'])) {
		    	$stringValidator->setValidatorFunctionName($definition['validator_function_name']);
	    	}
            	$stringValidator->setString($string);
            	return $stringValidator->validate();
        }
        
	private function validateNumeric($number, $definition) 
	{
            	$numericValidator = new Chair_Numeric_Ideal_Structure_Validator();
	    	if (isset($definition['more_then'])) {
		    	$numericValidator->setMoreThen($definition['more_then']);
	   	}
	    	if (isset($definition['less_then'])) {
		    	$numericValidator->setLessThen($definition['less_then']);
	    	}
	    	if (isset($definition['regexp'])) {
		    	$numericValidator->setRegexp($definition['regexp']);
	    	}
	    	if (isset($definition['in'])) {
		    	$numericValidator->setIn($definition['in']);
	    	}
	    	if (isset($definition['not_in'])) {
		    	$numericValidator->setNotIn($definition['not_in']);
	    	}
	    	if (isset($definition['validator_function_name'])) {
		    	$numericValidator->setValidatorFunctionName($definition['validator_function_name']);
	    	}
            	$numericValidator->setNumber($number);
            	return $numericValidator->validate();
        }
        
	private function validateCollection($collection, $definition) 
	{
            	$collectionValidator = new Chair_Collection_Ideal_Structure_Validator();
	    	if (isset($definition['min_number'])) {
		    	$collectionValidator->setMinNumber($definition['min_number']);
	    	}
	    	if (isset($definition['max_number'])) {
		    	$collectionValidator->setMaxNumber($definition['max_number']);
	    	}
	    	if (isset($definition['number_array'])) {
		    	$collectionValidator->setNumberArray($definition['number_array']);
	    	}
            	$collectionValidator->setCollection($collection);
	    	if (!$collectionValidator->validate()) {
		    	return false;
	    	}
	    	foreach ($collection as $key => $value) {
		    	if (!$this->validateByType($value, $definition['ideal_structure'])) {
			    	return false;
		    	}
	    	}
            	return true;
        }
        
        private function validateByType($structure, $definition) {
		if (isset($definition['hash'])) {
			return $this->validateHash($structure, $definition['hash']);
		}
		if (isset($definition['string'])) {
			return $this->validateString($structure, $definition['string']);
		}
            	if (isset($definition['numeric'])) {
			return $this->validateNumeric($structure, $definition['numeric']);
		}
		if (isset($definition['collection'])) {
			return $this->validateCollection($structure, $definition['collection']);
		}
            	return false; 
        }
        
	public function validate($structure, $pattern) 
	{
		if (!$this->isIdealStructure($structure)) {
			return false;
		}
            	return $this->validateByType($structure, $pattern);
        }
}
