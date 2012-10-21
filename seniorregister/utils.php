<?php

function replace_if($var, $from, $to)
{
	return ($var !== $from) ? $var : $to;
}

function replace_empty($var, $to)
{
	return ($var ? $var : $to);
}

function set_equals($a, $b)
{
	sort($a);
	sort($b);
	
	return ($a == $b);
}

function set_equalsi($a, $b)
{
	sort($a);
	sort($b);
	
	if(count($a) != count($b))
		return false;
		
	for($i = 0; $i < count($a); $i++)
		if(strtolower($a[$i]) != strtolower($b[$i]))
			return false;
	
	return true;
}

function fetch_rows($res)
{
	$rows = array();
	
	while(($arr = $res->fetch_array()) !== false)
	{
		// this is not so much fun, there should be a fetch_row in db...
		$row = array();
		$i = 0;
		while(isset($arr[$i]))
			$row[] = $arr[$i++];
		$rows[] = $row;
	}
		
	return $rows;
}

// stolen from php.net/preg_replace_callback
function curry($func, $arity) {
    return create_function('', "
        \$args = func_get_args();
        if(count(\$args) >= $arity)
            return call_user_func_array('$func', \$args);
        \$args = var_export(\$args, 1);
        return create_function('','
            \$a = func_get_args();
            \$z = ' . \$args . ';
            \$a = array_merge(\$z,\$a);
            return call_user_func_array(\'$func\', \$a);
        ');
    ");
}

?>