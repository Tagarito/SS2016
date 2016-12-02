<?php
// SQL Injection
// $_GET,$_POST,$_COOKIE,$_REQUEST,HTTP_GET_VARS,HTTP_POST_VARS,HTTP_COOKIE_VARS,HTTP_REQUEST_VARS
// pg_escape_string,pg_escape_bytea,pg_escape_string,pg_escape_bytea
// pg_query,pg_send_query
;
pg_query($ola = $_GET['fa']);
//pg_query($_GET['fa']);
//
//echo $_GET['A'];
//pg_send_query (pg_escape_string($_GET['A']),NULL);
?>
