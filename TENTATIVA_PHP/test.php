<?php


function test($cenas) {
	//var_dump($cenas);
	//echo "y\n";
	//echo NULL;
	//echo "\ny\n";
	return "was called";
}
$var1 = "putas";
$var =  $var1 . test() . $_GET['OLA'];
$var = array();
array_push($var, 2);
var_dump($var);
if($var) {
	echo "array full passou\n";
}
array_splice($var,0,1);
var_dump($var);
echo "x\n";
test($var[2]);
echo "x\n";

if(array()) {
	echo "array passou";
}
if(NULL) {
	echo "NULL passou";
}

//mys = var() ;
//$mys = $var ;
//$mys = $var[] ;
?>
