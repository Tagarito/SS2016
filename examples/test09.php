<?php
// SQL Injection
// $_GET,$_POST,$_COOKIE,$_REQUEST,HTTP_GET_VARS,HTTP_POST_VARS,HTTP_COOKIE_VARS,HTTP_REQUEST_VARS
// pg_escape_string,pg_escape_bytea,pg_escape_string,pg_escape_bytea
// pg_query,pg_send_query
//mysqli::escape_string,mysqli::real_escape_string,mysqli::real_escape_string

//$query = sprintf("SELECT * FROM users WHERE user='%s' AND password='%s'", $BAD , $bad2);
if(true){
$a = $_GET['ola'];
mysqli::query ($a);
//$query = pg_send_query($a,$b,$c,$d);
}
?>
