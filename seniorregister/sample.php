<?php

header("Content-type: text/html; charset=utf-8");

?>
<html>
<head><title>Seniorregister</title></head>
<body>
<h3>Seniorregister</h3><br />
<form>
<h2>Personuppgifter</h2><br />
<table width="100%">
<tr><td><strong>Förnamn:</strong></td><td><input type="text" name="first_name" /></td></tr>
<tr><td><strong>Efternamn:</strong></td><td><input type="text" name="last_name" /></td></tr>
<tr><td><strong>Adress:</strong></td><td><input type="text" name="address" /></td></tr>
<tr><td><strong>Telefon:</strong></td><td><input type="text" name="telephone" /></td></tr>
<tr><td><strong>Arbete:</strong></td><td><input type="text" name="work" /></td></tr>
<tr><td><strong>Födelseår:</strong></td><td><input type="text" name="birth_year" /></td></tr>
<tr><td><strong>Årskurs:</strong></td><td><input type="text" name="start_year" /></td></tr>
<tr><td><strong>Examensår:</strong></td><td><input type="text" name="graduation_year" /></td></tr>
</table>
<h2>Emeritus</h2><br />
<input type="checkbox" name="ordf" /> Ordförande<br />
<input type="checkbox" name="vordf" /> Vice ordförande<br />
<input type="checkbox" name="kassor" /> Kassör<br />
...<br />
<input type="checkbox" name="fadderiet" /> Fadderiet<br />
<input type="checkbox" name="overfohs" /> Överföhs<br />
<input type="checkbox" name="fohs" /> Annan föhs<br />
<input type="checkbox" name="dumvast" /> Dumväst<br />
<input type="checkbox" name="integral" /> Integralorden<br />
<input type="checkbox" name="heders" /> Hedersmedlem<br />
<h2>Medlemskap</h2><br />
<input type="checkbox" name="senior" /> Jag vill vara seniormedlem<br />
<input type="checkbox" name="force" /> Jag vill ha hem Force<br />
<input type="checkbox" name="inte_senior" /> Jag vill inte vara seniormedlem just nu<br />
</form>
</body>
</html>