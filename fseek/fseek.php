<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="sv-SE">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="http://f.kth.se/fseek/local.css" type="text/css" media="screen" />
</head>
<body><?php
include("fseek_fun.php");
?>
<?php
  if(!isset($_POST['search']) && !isset($_POST['user'])) {
    echo "<p>Här kan du söka på användarnamn, för- och/eller efternamn (eller delar av namn) och få fram vilka konton det matchar. Som resultat får du kontonamn (mailadress) och eventuellt en länk till en hemsida.</p>";
  }
?>

<p>
<form action=<?php print "\"" . $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'] . "\""; ?> method="POST">
<div class="sok">
<label for="search">Sök efter</label> <input value="<?php $_GET['search'] ?>" id="search" name="search"/>
<input type="submit" value="Sök"/>
</div>
</form>
</p>


<?php
		     if(isset($_POST['search'])) {
		       userSearch($_POST['search']);
		     }


?>

</body>
</html>