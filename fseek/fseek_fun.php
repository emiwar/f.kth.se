<?php
$sektioner = 'BDEFKMTPVI'; // sektioner med färg definerad.

function comp($a,$b) {
  $a = explode(':',$a,4);
  $b = explode(':',$b,4);
  $al = strtolower($a[3]);
  $bl = strtolower($b[3]);
  if ($al == $bl) {
    return 0;
  }
  return ($al > $bl) ? +1 : -1;
}

function sekt($sekt_str) {
  $sekts = explode('/', $sekt_str);
  $teknsekt=array();
  global $sektioner;

  foreach($sekts as $sekt) {
    if($sekt=='ARKIT')
      $teknsekt[] = 'A';
    else if($sekt=='CBIOT')
      $teknsekt[] = 'B';
    else if($sekt=='CDATE')
      $teknsekt[] = 'D';
    else if($sekt=='CDEPR')
      $teknsekt[] = 'P';
    else if($sekt=='CELTE')
      $teknsekt[] = 'E';
    else if($sekt=='CFATE')
      $teknsekt[] = 'T';
    else if($sekt=='CINEK')
      $teknsekt[] = 'I';
    else if($sekt=='CINTE')
      $teknsekt[] = 'IT';
    else if($sekt=='CKEMV')
      $teknsekt[] = 'K';
    else if($sekt=='CMAST')
      $teknsekt[] = 'M';
    else if($sekt=='CMATD')
      $teknsekt[] = 'BD';
    else if($sekt=='CMETE')
      $teknsekt[] = 'Media';
    else if($sekt=='CSAMH')
      $teknsekt[] = 'V';
    else if($sekt=='CTFYS')
      $teknsekt[] = 'F';
    else if(ereg("F\d{2}",$sekt))
      $teknsekt[] = 'F';
    else if($sekt=='FYS')
      $teknsekt[] = 'F';
    else if($sekt=='COPEN')
      $teknsekt[] = 'OPEN';
    else if($sekt=='BIO')
      $teknsekt[] = 'K';
    else if($sekt == 'S' || $sekt == 'L' || ereg("[L,S,V][0-9][0-9]",$sekt))
      $teknsekt[] = 'V';
    else if(strlen($sekt) < 4 && ereg("[A-Z][0-9][0-9]",$sekt))
      $teknsekt[] = $sekt[0];
    else if(stristr($sekt,'media'))
      $teknsekt[] = 'Media';
    else if($sekt == "" || strpos($sektioner, $sekt) === false) 
      continue;
    else
      $teknsekt[] = $sekt;
  }
  return $teknsekt;
}

/*
 Formatterar en användare för utskrift
*/
function user_to_row($user,$x) { 
  global $sektioner; // vilka sektioner finns det färg definerad för.
  $user=explode(':',$user);
  if(substr($user[5],0,4)!="/afs")
    $user[5]="/afs/nada.kth.se/$user[5]";
  $namn = explode(',',$user[4]);
  $sekt = $namn[1] . "/" . $namn[4];
  $sekt = sekt($sekt);

  if(!in_array("F",$sekt) && !isset($_POST['findall'])) {
    return; // Sortera bort icke-fysiker (findall är inte implementerad)
  }
  else {
    $sekt="F";
  }

  $out = "<tr class=\"trli\">";
  $out .= "<td><a href=\"http://w5.nada.kth.se/fseek/userinfo.php?user=" . $user[0] . "\">" . $namn[0] . "</a></td>";
  $out.="<td>";
  if(isset($_POST['findall'])) {
    // Skriv ut sektion om sök alla, annars ...
    if($sekt && strstr($sektioner,$sekt))
      $out .= "<div class=\"sekt $sekt\" title=\"" . $namn[1] . "\">" . $sekt . "</div>";
    else if($sekt == 'Media') {
      $out .= "<img src=\"/modules/sok/media_rgb.gif\" class=\"sekt\" title=\"Media\"/>";
    }
  }
  else { 
    // ... skriver vi ut årskurs (kontoaktiveringsdatum)
    $arskurs=substr($namn[3],0,2);
    $out.="<div class=\"sekt\">F-" . $arskurs . "</div>";
  }
  $out.="</td>";

  $out .= "<td><a href=\"mailto:" . $user[0] . "@kth.se\">" . $user[0] . "@kth.se</a></td>";
  
  
  /* Fel i sökning, returnerar inte rätt användar mapp, denna funktion funkar inte, stänger av tillvidare. /Leo fidjeland
  $out .= "<td>" . ( file_exists($user[5]."/public_html" ) ? "<a href=\"http://www.f.kth.se/~" . $user[0] . "\" target=\"_blank\">Hemsida</a>":"<br/>" ) . "</td>";
  */
  
  
  $out .="</tr>\n";
  return $out;
}

function userInfo($user) {
  $passwdfile = '/etc/passwd';
  $handle = popen("grep -i \"^{$user}:\" " . $passwdfile, 'r');
  $read = utf8_encode(fgets($handle));
  if(!$read) {
    print "Fel vid sökning!";
    return;
  }
  pclose($handle);
  $user = explode(':',$read);
  if(substr($user[5],0,4)!="/afs")
    $user[5]="/afs/nada.kth.se/$user[5]";
  if(substr($user[5],0,6)=="/home/") {
      $user[5]=str_replace("home","afs/nada.kth.se/home",$user[5]);
    }
  
  $home = explode('/',$user[5]);
  $offset = count($home);
  
  if(file_exists($user[5].'/.bild'))
    $mrl = $user[5].'/.bild';
  else if(file_exists($user[5].'/Public/.bild') && !is_dir($user[5].'/Public/.bild'))
    $mrl = $user[5].'/Public/.bild';
  else {
    $file = "/afs/nada.kth.se/misc/hacks/graphic/bitmaps/xfinger/".$home[$offset-2].'/'.$home[$offset-1];
    if(strlen($home[5])>5 && file_exists($file))
      $mrl = $file;
  }  
  $userinfo = explode(',',$user[4]);
  for($i=0; $i<count($userinfo); $i++) {
    $userinfo[$i]=$userinfo[$i];
  }
  echo "<h2>" . $userinfo[0] . " (" .$user[0] . ")</h2>";
  if($mrl){
    echo "<img class=\"userimage\" src=\"http://cgi.student.nada.kth.se/cgi-bin/fsekt/xfinger-adj?" . $mrl . "\">";
	}else{
	echo '<img class="userimage" src="ingen_bild.png" alt="Ingen bild" />';
}
  echo "<div class='info'>";
  if(file_exists($user[5].'/public_html'))
    echo "<a href=\"http://www.f.kth.se/~" .$user[0] ."\" target=\"_blank\">Personlig hemsida</a><br>";
  echo "<a href=\"mailto:" . $user[0] . "@kth.se\">" . $user[0] . "@kth.se</a><br>";
  echo "<br>";
  
  if(strlen($userinfo[2])>5||strlen($userinfo[3]>5)) {
    echo "<dl><dt>Telefon: </dt><dd>";	
    if(strlen($userinfo[3])>5)
      echo "Hem: " .$userinfo[3] . "<br>";
    if(strlen($userinfo[2])>5)
      echo "Mobil: " .$userinfo[2];
    echo "</dd>";
    echo "</dl>";
  }
  if(file_exists($user[5].'/Public/.plan')) {
    echo "Plan:<br>\n<pre>";
    readfile($user[5] ."/Public/.plan");
    echo '</pre>';
  }
  echo "</div>";
}


function userSearch($user) {
	/*Den här sökningen är troligen onödigt ineffektiv eftersom den returnerar
	alla användade och inte bara folk i fysiksektionen. De sorteras bort senare.
	Den klipper även bort fel information när det gäller användarens mapp och skulle
	behöva lite optimering. Men hela systemet är troligtvis ganska gammalt så det får
	vara tills vidare.
	
	/leo fidjeland */

  $passwdfile = '/etc/passwd';
  $search = utf8_decode(rawurldecode($_POST['search']));
  if(isset($_POST['search']) && $_POST['search']) {
    $search = strtolower($search);
    
    $search = str_replace('Ã…','å',$search);
    $search = str_replace('Ã„','ä',$search);
    $search = str_replace('Ã–','ö',$search);
    $search = str_replace('Ã‰','Ã©',$search);
    
    $search = str_replace('å','[åÃ…]',$search);
    $search = str_replace('ä','[äÃ„]',$search);
    $search = str_replace('ö','[öÃ–]',$search);
    $search = str_replace('Ã©','e',$search);
    $search = str_replace('e','[Ã©eÃ‰]',$search);
    if(isset($_POST['page']) && $_POST['page']) {
      $page = $_POST['page'];
      $limit = ($page-1)*30;
    }
    else {
      $page = 1;
      $limit = 0;
    }
    $search = trim($search);
    $search = "|grep -i \"".str_replace(' ',"\"|grep -i \"",$search)."\"";
    $search = $search . str_replace('-i "','-iv ",',$search);
    $run = "cut -d, -f 1,2,5,6 " . $passwdfile . $search . "|grep \":30:\" ";
    $handle = popen($run, 'r');
    for($x=0;$read[$x] = utf8_encode(fgets($handle));$x++) {
      if(substr($read[$x],0,strlen($_POST['search'])+1)==$_POST['search'].":") {
	// Vi har hittat en exakt matchning. Hurra.
	$exakt = $read[$x];
	$x--;
      }
    }
    pclose($handle);
    
    usort($read,"comp");

    echo '<table class="matches">'; 
    if(isset($exakt)) {
      echo '<tr><th class="namn">Namn</th><th class="year">Årskurs</th><th class="mail">Email</th>';
	  /* Denna funktion funkar inte för tillfället, sökning trasig, se notering under user_to_row /leo fidjeland
	  echo "<th>Hemsida</th>";
	  */
	  echo "</tr>";
      echo "<tr><td colspan=4>Exakt träff:<br></td></tr>";
      echo user_to_row($exakt,0);
      if(count($read)>1)
	echo "<tr><td colspan=4>Andra träffar:<br></td></tr>";
    } 
    else
      echo '<tr><th class="namn">Namn</th><th class="year">Årskurs</th><th class="mail">Email</th>';
	  /* Denna funktion funkar inte för tillfället, sökning trasig, se notering under user_to_row /leo fidjeland
	  echo "<th>Hemsida</th>";
	  */
	  echo "</tr>";
    for($x=1; $x<count($read); $x++) {
      print user_to_row($read[$x],$x);
    }
    echo "</table>";
  }
} 

?>
