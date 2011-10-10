<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('form_input_by_type'))
{
	function form_input_by_type($dataType, $data = '', $value = '', $extra = '')
	{
		if($data == 'id')
			return form_hidden($data, $value, $extra);
		else
			switch($dataType)
			{
				case 'tinytext':
				case 'mediumtext':	
				case 'text': 
				case 'longtext':
					return form_textarea($data, $value, $extra);
				default:
					return form_input($data, $value, $extra);
			}
	}
}