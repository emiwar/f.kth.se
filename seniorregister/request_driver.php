<?php

require('request.php');

require_once('utils.php');

$get = array('alpha' => "one\r\nENO", 'bravo' => array('t', 'w', 'o'), 'charlie' => 'three');
$post = array('charlie' => 'FOUR', 'delta' => '5', 'echo' => '1');
$cookie = array('foxtrot' => 'svn');
$server = array('golf' => 'egt');

$r = new Request($get, $post, $cookie, $server);

echo "Verifying *array()...";

assert(set_equals($r->get_array() , $get));
assert(set_equals($r->post_array(), $post));
assert(set_equals($r->cookie_array(), $cookie));
assert(set_equals($r->server_array(), $server));
echo "OK<br />\n";

echo "Verifying get()...";
assert($r->get('alpha', 'NE') == "one\r\nENO");
assert($r->get('alphr', 'NE') == 'NE');
assert($r->get('alphr') === NULL);
echo "OK<br />\n";

echo "Verifying post()...";
assert($r->post('charlie', 'NI') == 'FOUR');
assert($r->post('charlir', 'NI') == 'NI');
assert($r->post('charlir') === NULL);
echo "OK<br />\n";

echo "Verifying cookie()...";
assert($r->cookie('foxtrot', 'NO') == 'svn');
assert($r->cookie('foxtror', 'NO') == 'NO');
assert($r->cookie('foxtror') === NULL);
echo "OK<br />\n";

echo "Verifying server()...";
assert($r->server('golf', 'NA') == 'egt');
assert($r->server('golr', 'NA') == 'NA');
assert($r->server('golr') === NULL);
echo "OK<br />\n";

echo "Verifying data_keys()...";

assert(set_equals($r->data_keys(), array('alpha', 'bravo', 'charlie', 'delta', 'echo')));
echo "OK<br />\n";

echo "Verifying data()...";
assert($r->data('bravo') == array('t', 'w', 'o'));
assert($r->data('bravr', 'NU') == 'NU');
assert($r->data('bravr') === NULL);
assert($r->data('delta') == '5');
assert($r->data('charlie') == 'FOUR');
echo "OK<br />\n";

echo "Verifying data_scalar()...";
assert($r->data_scalar('bravo', 'NONE') == 'NONE');
assert($r->data_scalar('bravo') === NULL);
assert($r->data_scalar('bravr', 'NONE') == 'NONE');
assert($r->data_scalar('bravr') === NULL);
assert($r->data_scalar('charlie') == 'FOUR');
echo "OK<br />\n";

echo "Verifying data_array()...";
assert($r->data_array('bravo', 'NONE') == array('t', 'w', 'o'));
assert($r->data_array('charlie') == array('FOUR'));
assert($r->data_array('charlir', array('h', 'a')) == array('h', 'a'));
assert($r->data_array('charlir', 'NONE') == array('NONE'));
echo "OK<br />\n";

echo "Verifying data_scalar_set()...";
assert(!$r->data_scalar_set('bravo'));
assert(!$r->data_scalar_set('bravr'));
assert($r->data_scalar_set('alpha'));
echo "OK<br />\n";

echo "Verifying data_numeric()...";
assert($r->data_numeric('delta'));
assert(!$r->data_numeric('charlie'));
assert(!$r->data_numeric('bravo'));
echo "OK<br />\n";

echo "Verifying data_numeric_range()...";
assert($r->data_numeric_range('delta', 1, 6));
assert(!$r->data_numeric_range('charlie', 1, 6));
assert(!$r->data_numeric_range('delta', 1, 4));
echo "OK<br />\n";

echo "Verifying data_in()...";
assert($r->data_in('delta', array(1, 3, 5)));
assert(!$r->data_in('delta', array(1, 3)));
echo "OK<br />\n";

echo "Verifying int_scalar()...";
assert($r->int_scalar('delta', 99) == 5);
assert($r->int_scalar('charlie', 99) === 0);
assert($r->int_scalar('charlir', 99) == 99);
assert($r->int_scalar('charlir') === 0);
assert($r->int_scalar('charlir') !== NULL);
echo "OK<br />\n";

echo "Verifying int_scalar_or_null()...";
assert($r->int_scalar_or_null('delta') == 5);
assert($r->int_scalar_or_null('charlie') === NULL);
assert($r->int_scalar_or_null('charlir') === NULL);
echo "OK<br />\n";

echo "Verifying boolean_scalar()...";
assert($r->boolean_scalar('echo') === true);
assert($r->boolean_scalar('alpha') === true);
assert($r->boolean_scalar('echr', true) === true);
assert($r->boolean_scalar('echr') === false);
echo "OK<br />\n";

echo "Verifying text_scalar()...";
assert($r->text_scalar('alpha', 'NONE') == "one\nENO");
assert($r->text_scalar('alphr', 'NONE') == 'NONE');
assert($r->text_scalar('alphr') === '');
echo "OK<br />\n";
?>