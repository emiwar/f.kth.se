<?php 	require_once('kalender.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="sv-SE">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="http://f.kth.se/templates/fadderiet/utskrift.css" type="text/css" />
<title>Schema Mottagningen 2011</title>
<style type="text/css">
.fotlama {
	background: url('<?php echo $here; ?>fotlama.png') no-repeat scroll right bottom transparent;
    bottom: 0;
    color: #A23C25;
    font-size: 15px;
    height: 92px;
    margin-bottom: -2px;
    padding: 6px 86px 0 0;
    position: absolute;
    right: -44px;
}
.tg-markercell {
    height: <?php echo $kal['helh']; ?>px;
}
.tg-dualmarker {
    height: <?php echo $kal['halvh']; ?>px;
    margin-bottom: <?php echo $kal['halvh']; ?>px;
}
.tg-time-pri, .tg-time-sec {
    border-bottom: 1px solid #DDDDDD;
    padding-right: 2px;
    height:<?php echo ($kal['helh'] - 1); ?>px;
}
.tg-col-eventwrapper {
    cursor: default;
    position: relative;
    height:<?php echo $kal['helh']*$kal['perdag']; ?>px;
    margin-bottom:-<?php echo $kal['helh']*$kal['perdag']; ?>px;
}
.tim-matare{
	height:<?php echo $kal['helh']; ?>px;
	}
#enkat form {
	text-align: left;
	font-size: 13px;
}
#enkat fieldset {
	margin-bottom: 10px;
}
#enkat th{
	height: 0;
}
</style>
</head>
<body>
<?php 
	require_once('kalender.php');
	if(is_page('utskrift-grafiskt')){
	build_calendar('utskrift-grafiskt');
	exit();
	}elseif(is_page('utskrift-text')){
	build_calendar('utskrift-text');
	exit();
	} 
?>
</body>
</html>