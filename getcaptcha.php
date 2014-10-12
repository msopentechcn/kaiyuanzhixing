<?php

session_start();
$_SESSION = array();
require_once("simple-php-captcha.php");
$captcha = simple_php_captcha();
echo json_encode($captcha);

