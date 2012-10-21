<?php

require_once('db.php');
require_once('pgdb.php');
require_once('logger.php');

require_once('msg.php');

require_once('utils.php');

echo "Connecting to database...";
$db = new PostgresDatabase('localhost', 'register', 'register', 'register_i', Logger::new_dummy());
echo "OK<br />\n";

$msg = new Message();

$msg->recipient('janden@kth.se');
$msg->subject('Hej {student.first_name}!!');
$msg->message(
	"Herr/fru {student.last_name},\n\n" .
	"I och med att du började på Fysik {student.class_year} och tog " .
	"examen {student.graduation}, så har vi tänkt skicka dej " .
	"ett gratulationspaket. Adressen registrerad hos oss är: \n" .
	"\t{student.street_address}\n" .
	"\t{student.postal_address}\n" .
	"Vi ber dig därför bekräfta denna adress snarast möjligt.\n\n" .
	"I övrigt så har du betalat din senioravgift fram till " .
	"{student.paid_until}.\n\n" .
	"Din inbjudnanskod till seniorregistret är {invite.invite_code} " .
	"och ditt användarnamn är {user.username}.\n\n" .
	"Mvh,\n\n" .
	"Seniorregistret\n\n" .
	"P.S. Om din e-postadress inte är {student.email}, säg till!\n");

$tmpl = MessageTemplate::new_from_message($db, $msg);

//print_r($tmpl->fill_template(338));
//print_r($tmpl->fill_template(296));
$msgs = $tmpl->fill_template(new AndCriterion(new WantsForceCriterion(), new WantsEmailCriterion()));

print_r($msgs);

/*foreach($msgs as $msg)
	$db->insert('"message_queue"', 
		array('"when"' => new QueryExpression("NOW()"),
			'"sending_user_id"' => 500,
			'"recipient"' => $msg->recipient(),
			'"subject"' => $msg->subject(),
			'"message"' => $msg->message(),
			'"headers"' => $msg->header_string()));*/

$db->close();

?>