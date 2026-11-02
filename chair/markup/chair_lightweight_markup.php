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
class Chair_Lightweight_Markup
{
        private function __construct()
        {
        }
        public static function getInstance()
        {
                static $instance;
                return isset($instance) ? $instance : $instance = new self;
        }
        private function filterHeaders($text)
        {
                $text = preg_replace('/^##[ ]*(.+)/', '<h2>$1</h2>', $text);
                return preg_replace('/^#[ ]*(.+)/', '<h1>$1</h1>', $text);
        }
        public function filterLinks($text)
        {
                return preg_replace('/\[([^]]*)\][ ]*\(([^)]*)\)/', '<a href=\'$2\'>$1</a>', $text);
        }
        public function filterImages($text)
        {
                return preg_replace('/\+\[([^]]*)\][ ]*\(([^)]*)\)/', '<img alt=\'$1\' title=\'$1\' src=\'$2\'/>', $text);
        }
        public function filterStrong($text)
        {
                return preg_replace('#[^/]{1}\*([^*\\\\]+?)\*[^/]{1}#', ' <strong>$1</strong> ',$text);
        }
        public function filterEm($text)
        {
                return preg_replace('#[^/]{1}\~([^~]+?)\~#', ' <em>$1</em> ', $text);
        }
	private function getType($line)
	{
		if (preg_match('/^[\n\r]+/', $line) === 1) {
			return 'paragraph';
		}
		if (preg_match('/^[ ]{8}/', $line) === 1) {
			return 'code';
		}
		if (preg_match('/^\-/', $line) === 1) {
			return 'list';
		}
		if (preg_match('/^#/', $line) === 1) {
			return 'header';
		}
		return 'text';
	}
	private function codeProcessing($content) {
		return htmlspecialchars($content);
	}
	private function textProcessing($content) {
		return $this->filterStrong($this->filterEm($this->filterLinks($this->filterImages($this->filterHeaders($this->codeProcessing($content))))));
	}
	private function listProcessing($content) {
		$content = preg_replace('#^\-(.*)#', '$1', $content);
		return "<li>{$this->textProcessing($content)}</li>";
	}
        public function processing($textArray)
	{
		$state = array();
		foreach ($textArray as $line) {
			$state[] = array('content' => $line, 'type' => $this->getType($line));
		}
		$result = '';
		$currentType = '';
		foreach ($state as $lineNumber => $stateLine) {
			if ($stateLine['type'] === 'code') {
				if ($currentType !== 'code') {
					$result .= '<pre class="code"><code>';
				}
				$result .= $this->codeProcessing($stateLine['content']);
			}
			if ($stateLine['type'] === 'list') {
				if ($currentType !== 'list') {
					$result .= '<ul>';
				}
				$result .= $this->listProcessing($stateLine['content']);
			}
			if (in_array($stateLine['type'], array('text', 'paragraph', 'header'))) {
				$result .= $this->textProcessing($stateLine['content']);
				if ($currentType === 'code') {
					$result .= '</code></pre>';
				}
				if ($currentType === 'list') {
					$result .= '</ul>';
				}
			}
			if ($stateLine['type'] === 'paragraph') {
				if (!in_array($currentType, array('', 'header', 'list', 'code'))) {
					$result .= '</p>';
				}
				$nextLineNumber = $lineNumber + 1;
				if (isset($state[$nextLineNumber]) && !in_array($state[$nextLineNumber]['type'], array('header', 'list', 'code'))) {
					$result .= '<p>';
				}
			}
			$currentType = $stateLine['type'];
		}
		$lastStateKey = count($state) - 1;
		if (isset($state[$lastStateKey])) {
			$lastStateType = $state[$lastStateKey]['type'];
			if ($lastStateType === 'text') {
				$result .= '</p>';
			} else if ($lastStateType === 'code') {
				$result .= '</code></pre>';
			} else if ($lastStateType === 'paragraph') {
				$result .= '</p>';
			} else if ($lastStateType === 'list') {
				$result .= '</ul>';
			}
		}

		return $result;
        }
}
