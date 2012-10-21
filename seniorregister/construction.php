<?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Seniorregister</title>
<link rel="stylesheet" type="text/css" href="main.css" />
</head>
<body>
<div id="outer">
<div id="header">
<h1>Seniorregister</h1>
</div>
<div id="wrapper"><div id="tabs">
<div class="tab selected" id="tab_0">Logga in</div>
</div>
<div id="noun">
<div id="inner">
<form method="get" action="index.php">
<table>
<tr class="field_row"><td class="field_caption">Användarnamn:</td><td class="field_value edited"><input type="text" name="username" /></td><td></td></tr>
<tr class="field_row"><td class="field_caption">Lösenord:</td><td class="field_value edited"><input type="password" name="password" /></td><td></td></tr>
</table>
<input type="hidden" name="verb" value="write" />
<input type="hidden" name="noun" value="auth/in" />
<input type="submit" name="submit" value="Logga in" disabled="disabled" />
</div>
</div>
<div id="navigation">
<h3>Navigation</h3>
Inställningar<br />
Logga ut<br />
</div>
</div>
</div>
</body>
</html>
