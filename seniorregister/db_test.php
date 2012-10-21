<?php

echo "<html>\n<body>\n<table>\n<tr>\n";

$conn = pg_connect("host=localhost dbname=postgres user=fsekt_admin password=lalle");

$res = pg_query($conn, 'select * from pg_language');

for($i = 0; $i < pg_num_fields($res); $i++) 
{ 
	$fieldName = pg_field_name($res, $i); 
	echo "<th>$fieldName</th>\n"; 
} 
echo "</tr>\n"; 

while ($row = pg_fetch_row($res)) 
{
	echo "<tr>\n"; 
	foreach($row as $value)
	{
		echo "<td>$value</td>\n"; 
	} 
	echo "</tr>\n"; 
}

pg_free_result($res); 

pg_close($conn);

echo "</table>\n</body>\n</html>\n";

?>