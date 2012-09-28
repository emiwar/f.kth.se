<?php
// För att läsa enkäterna:
if($_GET['filename'])
{ 	
   require("../enkat/" . $_GET['filename']);
}


function radio($name, $value)
{
    global $enkat;
	
    if($enkat[$name] == $value)
	echo("checked=\"checked\" ");
}

function check($name)
{
    global $enkat;
	
    if($enkat[$name] == "on")
	echo("checked=\"checked\" ");
}

function text($name)
{
    global $enkat;
		
    if($enkat[$name])
	echo("value=\"" . $enkat[$name] . "\" ");
}

function textarea($name)
{
    global $enkat;
		
    if($enkat[$name])
	echo($enkat[$name]);
}
?>