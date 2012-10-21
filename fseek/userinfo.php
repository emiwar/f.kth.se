<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="sv-SE">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="http://f.kth.se/fseek/local.css" type="text/css" media="screen" />
</head>
<body>
<?php
include("fseek_fun.php");

if(!isset($_GET['user'])) {
  print "Ingen användare vald!";
}
else {
  userInfo($_GET['user']);
}

?>
<a class="tillbaka" href="javascript:history.back(-1)">← Tillbaka</a>

</body>
</html>