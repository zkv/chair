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
final class Chair_Execute extends Chair_Filter
{
	private $plan;
	private $context;
	private $outputType;

	public function __construct($request, $response)
	{
		parent::__construct($request, $response);
		$this->context = Chair_Context_Manager::getContext(__CLASS__);
		$this->outputType = $this->request->getAttribute('output');
	}
	public function executeBefore()
	{
		$this->plan = array();
		$this->parsePlanConfig();	
		try {
			$this->executeSteps();
			$this->executeViews();
		} catch (Chair_Replace_Plan_Exception $e) {
			$this->request->setAttribute('plan', $e->getPath());
			$this->executeBefore();
		}
	}
	private function parsePlanConfig()
	{
		if (!is_file($path = $this->request->getAttribute('plan'))) {
			throw new Exception("Exectution plan is not found on {$path} path.");
		}
		foreach (simplexml_load_file($path) as $class) {
			$className = (string) $class['name'];
			$filters = array();
			$output = array();
			foreach ($class->class as $nestedClass) {
				$nestedClassParents = class_parents($class = (string)$nestedClass['name']);
				if (in_array('Chair_Filter', $nestedClassParents)) {
					$filters[] = $class;
				} elseif (in_array('Chair_Action', $nestedClassParents)) {
					$output[] = $class;
				} elseif (in_array('Chair_View', $nestedClassParents)) {
					$output[] = $class;
				} else {
					throw new Exception("Execution failed. Class {$class} should be extended from Action, Filter or View.");
				}
			}
			$this->plan[] = array('class' => $className, 'filters' => $filters, 'output' => $output);
		}
	}
	private function executeSteps()
	{
		$count = count($this->plan);
		for ($key = 0; $key < $count; $key++) {
			try {
				$filter = $this->context->getFilter($this->context->getMemory(), $response = $this->context->getMemory());
				$classParents = class_parents($class = $this->plan[$key]['class']);
				if (in_array('Chair_View', $classParents)) {
					$view = call_user_func_array(array($filter, 'execute'), $this->plan[$key]['filters']);
					if (isset($view)) {
						$this->plan[$key]['view'] = $view;
					} else {
						$this->plan[$key]['view'] = $class;
					}
					$this->plan[$key]['classResponse'] = $response;
				} elseif (in_array('Chair_Action', $classParents)) {
					$view = call_user_func_array(array($filter, 'execute'), array_merge($this->plan[$key]['filters'], array($this->plan[$key]['class'])));
					if (isset($view)) {
						$this->plan[$key]['view'] = $view;
						$this->plan[$key]['classResponse'] = $response;
					} else {
						unset($this->plan[$key]);
					}
				} else {
					throw new Exception("Execution failed. Class {$class} should be extended from Action or View");
				}
			} catch (Chair_Replace_Action_Exception $e) {
				$this->replaceAction($this->plan[$key]['class'], $e->getAction());
				$key--;
			} 
		}
	}
	private function executeViews()
	{
		$slots = array();
		foreach ($this->plan as $key => $step) {
			if (!in_array('Chair_View', class_parents($viewClass = $step['view']))) {
				throw new Exception("Execution failed. Class {$viewClass} should be extended from View");
			}
			$view = new $viewClass($this->context->getMemory($step['classResponse']->getAttributes()), $response = $this->context->getMemory(), isset($slots[$class = $step['class']]) ? $slots[$class] : array());
			$view->{"render{$this->outputType}"}();
			foreach ($step['output'] as $output) {
				$slots[$output] = !isset($slots[$output]) ? $response->getAttributes() : array_merge_recursive($slots[$output], $response->getAttributes());
				foreach ($slots[$output] as $name => $value) {
					if (!is_array($value)) {
						$slots[$output][$name] = array($value);
					}
				}
			}
		}
	}
	private function replaceAction($oldAction, $newAction)
	{
		foreach ($this->plan as $key => $step) {
			if ($step['class'] === $oldAction) {
				$this->plan[$key]['class'] = $newAction;
			}
			foreach ($step['output'] as $outputKey => $outputValue) {
				if ($outputValue === $oldAction) {
					$this->plan[$key]['output'][$outputKey] = $newAction;
				}
			}
		}
	}
}
