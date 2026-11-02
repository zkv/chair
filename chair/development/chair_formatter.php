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
	class chair_formatter {
		public function format_file($input_file_name, $output_file_name) {
			$string = file_get_contents ( $input_file_name);
			$tokens = token_get_all ( $string );
			$result = '';
			$spaces = '    ';
			$counter = 0;
			foreach ( $tokens as $keys => $values ) {
				if ( is_array ( $values ) ) {
					$token_name = token_name ( $values [ 0 ] );
					switch ( $token_name ) {
						case "T_CONCAT_EQUAL" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_SWITCH" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_STATIC" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_FOR" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_LOGICAL_OR" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_AS" : {
							$result .= ' ' . $values [ 1 ] . ' ';
							break;
						}
						case "T_IS_GREATER_OR_EQUAL" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_IS_IDENTICAL" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_IS_NOT_IDENTICAL" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_CLASS" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_DOUBLE_ARROW" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_VAR" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_EXTENDS" : {
							$result .= ' ' . $values[ 1 ] . ' ';
							break;
						}
						case "T_GLOBAL" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_IS_EQUAL" : {
							$result .= ' ' .$values[ 1 ] . ' ';
							break;
						}
						case "T_RETURN" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_FUNCTION" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_IF" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_ELSE" : {
							$result .= $values[ 1 ];
							break;
						}
						case "T_NEW" : {
							$result .= $values[ 1 ] . " ";
							break;
						}
						case "T_ELSEIF" : {
							$result .= $values[ 1 ] . ' ';
							break;
						}
						case "T_OPEN_TAG" : {
							$result .= "<?php" . "\r\n" ;
							break;
						}
						case "T_PUBLIC" : {
							$result .= "public ";
							break;
						}
						case "T_PRIVATE" : {
							$result .= "private ";
							break;
						}
						case "T_PROTECTED" : {
							$result .= "protected ";
							break;
						}
						case "T_COMMENT" : {
							break;
						}
						case "T_DOC_COMMENT" : {
							break;
						}
						case "T_WHITESPACE" : {
							$result .= "";
							break;
						}
						case "T_CATCH" : {
							$result .= $values[ 1 ] . " ";
							break;
						}
						case "T_BOOLEAN_AND" : {
							$result .= " " . $values[ 1 ] . " ";
							break;
						}
						case "T_THROW" : {
							$result .= $values[ 1 ] . " ";
							break;
						}
						case "T_CASE" : {
							$result .= $values[ 1 ] . " ";
							break;
						}
						case "T_ECHO" : {
							$result .= $values[ 1 ] . " ";
							break;
						}
						case "T_IS_SMALLER_OR_EQUAL" : {
							$result .= " " . $values[ 1 ] . " ";
							break;
						}
						case "T_IS_NOT_EQUAL" : {
							$result .= " " . $values[ 1 ] . " ";
							break;
						}
						case "T_FOREACH" : {
							$result .= "foreach ";
							break;
						}
						default : {
							$result .= $values [ 1 ] ;
							break;
						}
					}
				} else {
					switch ( $values ) {
						case "{" : {
							$result .= " " . $values . "\r\n";
							$counter ++;
							for ( $i = 0 ; $i < $counter ; $i++ ) {
								$result .= $spaces;
							}
							break;
						}
						case "." : {
							$result .= " " . $values . " ";
							break;
						}
						case ">" : {
							$result .= " > ";
							break;
						}
						case "<" : {
							$result .= " < ";
							break;
						}
						case "}" : {
							$counter --;
							$result .= "\r\n";
							for ( $i = 0 ; $i < $counter ; $i++ ){
								$result .= $spaces;
							}
							$result .= $values . "\r\n";
							for ( $i = 0 ; $i < $counter ; $i++ ){
								$result .= $spaces;
							}
							break;
						}
						case ";" : {
							$result .= $values . "\r\n" ;
							for ( $i = 0 ; $i < $counter ; $i++ ) {
								$result .= $spaces ;
							}
							break;
						}
						case "(" : {
							$result .= $values;
							break;
						}
						case ")" : {
							$result .= $values;
							break;
						}
						case "=" : {
							$result .= " " . $values . " ";
							break;
						}
						case "," : {
							$result .= ", ";
							break;
						}
						case "!" : {
							$result .= $values;
							break;
						}
						case "@" : {
							$result .= $values;
							break;
						}
						case "?" : {
							$result .= " " . $values . " ";
							break;
						}
						case ":" : {
							$result .= " " . $values . " ";
							break;
						}
						case "[" : {
							$result .= $values;
							break;
						}
						case "]" : {
							$result .= $values;
							break;
						}
						case "-" : {
							$result .= " " . $values . " ";
							break;
						}
						default : {
							$result .= $values;
							break;
						}
					}
				}
			}
			
			$result = preg_replace ( 
				array ( 
					'/^ *\r\n/m',
					'/(catch *\( *[^)]+)\$/m',
					'/else([^ i]+)/m',
					'/\)([^ ;\r]+)/m',
					'/(for [^\r\n]+)\r\n +([^\r\n]+)\r\n +([^\r\n]+)/m',
					'/\[ *\]/m',
					'/ ->/m',
					'/\r\n *catch/m',
					'/}\r\n *else/m',
					'/\r\n *elseif/m',
					'/\:  \{/m',
					'/ \)/m',
					'/\) ,/m'
				),
				array (
					'',
					'$1 ' . '$',
					'else $1',
					') ' . '$1',
					'$1' . ' ' . '$2' . ' ' . '$3',
					'[]',
					'->',
					' catch',
					'} else' ,
					' elseif',
					': {',
					')',
					'),'
				),
				$result
			);
			print("<pre>");
			highlight_string($string);
			print("</pre>");
			print("<pre>");
			highlight_string($result);
			print("</pre>");
			file_put_contents ( $output_file_name , $result );
		}
	}
?>
