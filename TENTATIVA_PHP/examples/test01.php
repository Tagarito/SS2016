<html>
<?php
// SQL Injection
// $_GET,$_POST,$_COOKIE,$_REQUEST,HTTP_GET_VARS,HTTP_POST_VARS,HTTP_COOKIE_VARS,HTTP_REQUEST_VARS
// pg_escape_string,pg_escape_bytea,pg_escape_string,pg_escape_bytea
// pg_query,pg_send_query
$ola2 = $_GET['teste'];
$ola4 = $_POST['teste'];
$b = pg_query("$ola".$ola2 , "so mais uam cenas".fguh()."$ola4");
//$b = pg_query("$ola".$ola2 . "so mais uam cenas".fguh()."$ola4","so mais uam cenas".$ola4);
// $b = pg_query("$ola".$ola2 . "so mais uam cenas".fguh()."$ola4","so mais uam cenas"."$ola4");
//$b = pg_query("$ola".$ola2 . fguh()."$ola4","so mais uam cenas"."$ola4");
//$b = pg_query(fguh(),"so mais uam cenas"."$ola4");
//$b = pg_query($ola2 ,$ola4);
//$ola2 = pg_escape_string($ola2);
//$b = pg_query("$ola"."$ola2".fguh()."$ola4"."so mais uam cenas");

//Should outcome two vulns at least;
?>
</html>
