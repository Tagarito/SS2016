<?php
// SQL Injection
// $_GET,$_POST,$_COOKIE,$_REQUEST,HTTP_GET_VARS,HTTP_POST_VARS,HTTP_COOKIE_VARS,HTTP_REQUEST_VARS
// pg_escape_string,pg_escape_bytea,pg_escape_string,pg_escape_bytea
// pg_query,pg_send_query

$var =  $var1 . $_GET['OLA'] . "so para te chatear mais um bocado";
$var =  test() . $var1 . pg_escape_bytea() . $_GET['OLA'] . pg_escape_bytea()."so para te chatear mais um bocado";

$ola2 = pg_escape_string($var);
$b = pg_query ( $var  );
$b = pg_query ( $ola2 );

?>
