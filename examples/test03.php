<?php
// SQL Injection
// $_GET,$_POST,$_COOKIE,$_REQUEST,HTTP_GET_VARS,HTTP_POST_VARS,HTTP_COOKIE_VARS,HTTP_REQUEST_VARS
// pg_escape_string,pg_escape_bytea,pg_escape_string,pg_escape_bytea
// pg_query,pg_send_query
$ola = $_GET['fa'];
$ola = pg_escape_string($ola="ola"."fuck".""."$ola");
pg_query(funcall()."$ola fuck"."$VAR");
$ola = $_GET['fa'];
pg_query(funcall()."$ola fuck"."$VAR");

?>
