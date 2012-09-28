
<?php
$here = dirname(__FILE__);
//if(isset($_POST["submit"]) && !isset($_POST['mannska']))
if(isset($_POST["submit"]))
{
  include($here . "/server/list.php");
  $fd = fopen($here . '/server/list.php', "w");
  fwrite($fd, "<?php\n");
  fwrite($fd, "\$list = array( \n");
  foreach ($list as $id => $value)
  {

     fwrite($fd, "\"" . $id . "\" => \"" . $value . "\",\n"); 
     $run = true;	
  }
  if(!$run) $id = -1;
  //date_default_timezone_set('Europe/Stockholm');
  $filnamn = sanitize_title("enkat_" . $_POST['pi_surname'] . ',' . $_POST['pi_givenname'] . date("Y-m-d") . "_" . ($id + 1));
  if($_POST['mannska']){$filnamn = sanitize_title('SPAM'.date("Y-m-d") . "_" . ($id + 1)); }
  fwrite($fd, "\"" . ($id+1) . "\" => \"" . $filnamn . "\"\n");
  fwrite($fd, ");\n"); 
  fwrite($fd, "?>"); 
  fclose($fd);

  $fd = fopen($here . '/server/' . $filnamn, "w");
  fwrite($fd, "<?php\n");
  fwrite($fd, "\$enkat = array( \n");
  $first=true;
  foreach ($_POST as $id => $value) {
    if($first == false) fwrite($fd, ",\n"); else $first = false; 
    fwrite($fd, "\"" . $id . "\" => \"" . $value . "\"");
  }
  fwrite($fd, "\n"); 
  fwrite($fd, ");\n"); 
  fwrite($fd, "?>"); 
  fclose($fd);
  if(!file_exists($here.'/svar/'.$filnamn)){
  	try{
  		rename($here.'/server/'.$filnamn,$here.'/svar/'.$filnamn);
  	}catch(Exception $e){
  		var_dump($e);
  	}
  }
}
?>
