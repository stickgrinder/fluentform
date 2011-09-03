<?php

function fluentform_form_input($data = '', $value = '', $extra = '', $type = '')
{
	if ( is_array($data) AND isset($data['type']) )
	{
		$type = $data['type'];
	}
	elseif ( empty($type) )
	{
		$type = 'text';
	}
	
	$defaults = array('type' => $type, 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);	
	return "<input "._parse_form_attributes($data, $defaults).$extra." />";
}
