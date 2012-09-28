<?php
include("fseek_fun.php");
?>

<h3>Sök en Fysiker</h3>
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