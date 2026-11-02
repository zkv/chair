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
	class Chair_Time_Profiler
	{
		private static $control_points = array ( ) ;
		
		private static $control_points_count = 0 ;
		
		private static $min_control_points_number = 2 ;
		
		private static function profile_time ( )
		{
 			$mtime = explode ( " " , microtime ( ) ) ;
 			return doubleval ( $mtime [ 1 ] ) + doubleval ( $mtime [ 0 ] ) ;
		}
		
		public static function add_control_point($control_point_name='')
		{
			self :: $control_points [ ] = array
			(
				"control_point_name" => $control_point_name ,
				"control_point_time" => self :: profile_time ( )
			);
			self :: $control_points_count += 1 ;
		}
		
		public static function getControlPointsInfo() {
			$controlPoints = array();
			for ( $i = 0 ; $i < self :: $control_points_count - 1 ; $i ++ ) {
				$info = array();
				$info["first_control_point_pair_name"]  = self :: get_control_point_name ( $i ) ;
				$info["second_control_point_pair_name"] = self :: get_control_point_name ( $i + 1 ) ;
				$first_control_point_pair_time  	= self :: get_control_point_time ( $i ) ;
				$second_control_point_pair_time 	= self :: get_control_point_time ( $i + 1 ) ;
				$info["time_interval"]			= $second_control_point_pair_time - $first_control_point_pair_time ;
				$controlPoints[] 			= $info;
			}
			return $controlPoints;
		}
		
		private static function getNormalizationBase($number) {
			return 1/$number;
		}
		private static function getVizualizationWidth($baseWidth, $maxValue, $interval) {
			return $baseWidth * self::getNormalizationBase($maxValue) * $interval;
		}
		public static function vizualize() {
			$controlPoints = self::getControlPointsInfo();
			$time = array();
			foreach ($controlPoints as $key => $value) {
				$time[] = $value["time_interval"];
			}
			$maxTime = max($time);
			$width = 400;
			$info = "
				<table border='1'>
					<thead>
						<tr>
							<td>Start control point</td>
							<td>Elapsed time in persents</td>
							<td>Elapsed time in seconds</td>
							<td>End control point</td>
						</tr>
					</thead>
					<tbody>
			";
			foreach ($controlPoints as $key => $value){
				$styleWidth = self::getVizualizationWidth($width, $maxTime, $value["time_interval"]);
				$value["first_control_point_pair_name"]{0} = strtoupper($value["first_control_point_pair_name"]{0});
				$value["second_control_point_pair_name"]{0} = strtoupper($value["second_control_point_pair_name"]{0});
				$info .=<<<EOF
				<tr>
					<td>{$value["first_control_point_pair_name"]}</td>
					<td style='width:{$width}px;'>
						<div style='width:{$styleWidth}px; background-color:green; color:#efefef;'>&nbsp;</div>
					</td>
					<td>{$value["time_interval"]}</td>
					<td>{$value["second_control_point_pair_name"]}</td>
				</tr>
EOF;
			}
			$info .= "</tbody></table>";
			echo <<<EOF
				<style type='text/css'>
					table {
						padding:10px;
					}
					table tr td{
						padding:5px;
						border:1px solid #eeeeee;
						font-family:Verdana;
						background-color:#efefef;
						font-size:14px;
					}
					table thead tr td{
						padding:5px;
						border:1px solid #eeeeee;
						font-family:Verdana;
						font-weight:bold;
						color:#555555;
						background-color:#efefef;
						font-size:14px;
					}
					table tbody tr td{
						padding:5px;
						border:1px solid #eeeeee;
						font-family:Verdana;
						background-color:#efefef;
						font-weight:bolder;
						color:#777777;
						font-size:13px;
					}
					div.profiling {
						border-bottom:3px solid #dddddd;
					}
					div.profiling h1 {
						padding-left:20px;
						margin-top:10px;
						font-size:23px;
						font-family:Verdana;
						color:#666666;
						background-color:#dddddd;
					}
				</style>
				<div class='profiling'>
					<h1>Profiling Results</h1>
					$info
				</div>
EOF;
		}
		
		public static function show_profiling_results()
		{
			self :: output_delimiter ( ) ;
			self :: output_header ( ) ;
			
			if ( self :: is_enough_control_points ( ) )
			{
				self :: output_control_points ( ) ;
			}
			else
			{
				self :: output_control_points_warning ( ) ;
			}
			self ::  output_delimiter ( ) ;
		}
		
		private static function output_control_points ( )
		{
			for ( $i = 0 ; $i < self :: $control_points_count - 1 ; $i ++ )
			{
				$first_control_point_pair_name  = self :: get_control_point_name ( $i ) ;
				$second_control_point_pair_name = self :: get_control_point_name ( $i + 1 ) ;
				$first_control_point_pair_time  = self :: get_control_point_time ( $i ) ;
				$second_control_point_pair_time = self :: get_control_point_time ( $i + 1 ) ;
				$time_interval			= $second_control_point_pair_time - $first_control_point_pair_time ;

				self :: output_pair_control_points(
					$first_control_point_pair_name ,
					$second_control_point_pair_name ,
					$time_interval
				);
			}
		}
		
		private static function output_pair_control_points ( $first_control_point_name , $second_control_point_name , $time_interval )
		{
			echo "Time spent between $first_control_point_name and $second_control_point_name: $time_interval seconds\n";
		}
		
		private static function get_control_point_name ( $control_point_number )
		{
			return self :: $control_points [ $control_point_number ] [ "control_point_name" ] ;
		}
		
		private static function get_control_point_time ( $control_point_number )
		{
			return self :: $control_points [ $control_point_number ] [ "control_point_time" ] ;
		}
		
		private static function output_control_points_warning ( )
		{
			echo "The number of control points should be more then 2.<br>" ;
			echo "Number of current points: $this->control_points_count" ;
		}
		
		private static function is_enough_control_points ( )
		{
			return self :: $control_points_count >= self :: $min_control_points_number ;
		}
		
		private static function output_header ( )
		{
			echo "--------------------------------- Profiling results -------------------------------\n\n";
		}
		
		private static function output_delimiter ( )
		{
			echo "-----------------------------------------------------------------------------------\n" ;
		}
	}
?>
