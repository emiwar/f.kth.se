<?php
include("fseek_fun.php");

if(!isset($_GET['user'])) {
  print "Ingen anv�ndare vald!";
}
else {
  userInfo($_GET['user']);
}

?>
<a href="javascript:history.back(-1)">Tillbaka</a>