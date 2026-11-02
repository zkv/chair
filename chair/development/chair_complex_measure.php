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
    class Chair_Complex_Mesure {
        private $needInfo = array(
            'T_CLASS',
            'T_FUNCTION',
            'T_IF',
            'T_ELSE',
            'T_ELSEIF',
            'T_CASE',
            'T_STRING',
            'T_FOREACH',
            'T_FOR',
            'T_TRY',
            'T_CATCH'
        );
        private $file;
        private $complexity = array();
        private $content;
        private $tokens;
        private $state = '';
        private $waiter = '';
        private $currentClass = '';
        private $currentMethod = '';

        public function __construct($fileName) {
            $this->file = $fileName;
        }
        private function getContent() {
            $this->content = file_get_contents($this->file);
        }
        
        private function getTokens() {
            $this->tokens = token_get_all($this->content);
        }
        
        private function getTokenName($code) {
            return token_name($code);
        }
        
        private function incrementComplexity($tokenName) {
            if (!isset($this->complexity[$this->currentClass][$this->currentMethod][$tokenName])) {
                $this->complexity[$this->currentClass][$this->currentMethod][$tokenName] = 0;
            }
            $this->complexity[$this->currentClass][$this->currentMethod][$tokenName]++;
        }
        
        private function toHTML() {
            if (!count($this->complexity)) return '';
            $result = '';
            foreach ($this->complexity as $class => $classInfo ) {
                $result .= "<h1>Class $class </h1>";
                foreach ($classInfo as $method => $methodInfo) {
                    $totalComplex = 0;
                    $tableInfo = '';
                    foreach ($methodInfo as $name => $number) {
                        $totalComplex += $number;
                        $tableInfo .= "<tr><td>$name</td><td>$number</td></tr>";
                    }
		    if ($totalComplex < 11) {
			    $class = 'green';
		    }
		    elseif ($totalComplex < 21) {
			    $class = 'yellow';
		    }
                    else $class = 'red';
                    $result .= "<h2 class='$class'>$method [Total Complex: $totalComplex]</h2>";
                    if ($tableInfo) $result .= "<table>$tableInfo</table>";
                }
            }
            return $result;
        }
        
        public function mesure() {
            $this->getContent();
            $this->getTokens();
            foreach ($this->tokens as $key => $value) {
                if (is_array($value)) {
                    $tokenName = $this->getTokenName($value[0]);
                    if (in_array($tokenName, $this->needInfo)) {
                        if ($tokenName === 'T_STRING') {
                            if ($this->state === $tokenName) {
                                $this->state = '';
                                if ($this->waiter === 'T_CLASS') {
                                    $this->complexity[$value[1]] = array();
                                    $this->currentClass = $value[1];
                                    $this->currentMethod = "";
                                } else {
                                    $this->complexity[$this->currentClass][$value[1]] = array();
                                    $this->currentMethod = $value[1];
                                }
                                $this->waiter = "";
                            }
                        } elseif (in_array($tokenName, array( 'T_CLASS', 'T_FUNCTION'))) {
                            $this->state =  'T_STRING';
                            $this->waiter = $tokenName;
                        } else {
                            $this->incrementComplexity($tokenName);
                        }
                    }
                }
            }
            return $this->toHTML();
        }
    }
?>
