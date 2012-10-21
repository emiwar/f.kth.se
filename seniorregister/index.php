<?php

session_start();

require_once('register.php');

$reg = new RegisterPage();

$reg->process();

?>