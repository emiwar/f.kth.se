<?php
header('Content-type: text/html; charset=utf-8');
if(!isset($_GET['filename']))
{ ?>
Klicka på den enkät du vill se!<br/>
<?php
	include("../server/list.php");
	foreach ($list as $id => $filename)
	{
		echo '<a href="visa.php?filename='.$filename.'">'.$filename.'</a><br/>';	
	}
}
if (isset($_GET['filename'])) { 
  include $_GET['filename'];
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script type="text/javascript">
   function omglol() {
   var elems=new Array();
<?php
  foreach($enkat as $name=>$val) { 
    $val=mysql_escape_string($val);
    print "elems.push(new Array(\"" . $name . "\",\"" . $val . "\"));\n"; 
  }
  print <<<HEREDOC
    for(i=0; i<elems.length; i++) {
      var ename=elems[i][0];
      var evalue=elems[i][1];
      var elem=document.getElementById(ename)
      if(elem==null){
	if(ename!='submit')
	  alert('elem ' + ename + '=null!');
	continue;
      }
      switch(elem.tagName.toLowerCase()) {
	case "input":
	  switch(elem.type) {
	  case "text":
            elem.value=evalue;
	  case "checkbox":
	    elem.checked=evalue;
	    break;
	  case "radio":
  	    radios=document.getElementsByName(ename);
	    for(j=0; j<radios.length; j++) {
	      if(radios[j].value==evalue) {
	        radios[j].checked='on';
	        break;
	      }
	    }
	    break;
	  default:
   	    alert('whoops... ' + elemname);
	  }
	break;
	case "textarea":
          elem.value=evalue;
	  break;
	}
    }
  }
HEREDOC;
 

?>
</script>
</head>
    <body onload="omglol('done');">
<?php
  include '../enkat_base.php';
?>
</body>
<?php
    }
?>
</html>