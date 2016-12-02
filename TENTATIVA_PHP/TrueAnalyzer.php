<?php

require 'php-parser/lib/bootstrap.php';
require 'PatternDetector.php';
ini_set('xdebug.max_nesting_level', 3000);

use PhpParser\Error;
use PhpParser\ParserFactory;

class TrueAnalyzer {

  private $PatternsIdentifier;
  private $tree = false;
  function __construct ($patternsIdentifier) {
	  $this->PatternsIdentifier = $patternsIdentifier;
  }

  private $entryPoints = array('_POST', '_GET', '_COOKIE');

  public function analyzeFile($file){
    if(($stmts = $this->parseFile($file)) == -1){
      exit -1;
    }
	   if($this->tree) {
		     print_r($stmts);
	      }

    foreach($stmts as $stmt){
      $this->verifyStatement($stmt);
    }
    $this->PatternsIdentifier->report();

  }

  private function parseFile($file){
      if(($code = file_get_contents($file)) == false){
        exit(-1);
      }

    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    try{
      $stmts = $parser->parse($code);
      return $stmts;
    } catch (PhpParser\Error $exception){
      echo "Error in parseFile: ", $exception->getMessage();
      exit(-1);
    }
  }

  private function verifyStatement($stmt){
    if(!($stmt instanceof PhpParser\Node)){
	  echo Colours::Brown()."Statement is not a not a PHP\Node it will be ignored\n".Colours::RESET();
	  return false;
    }
    $type = $stmt->getType();
    //print_r($type);
    //echo "\n";
    if($type == "Expr_Assign"){
      //echo "\n\tIt's an Assign\n";
      return $this->verifyAssignment($stmt);
    }elseif ($type == 'Expr_FuncCall') {
      //echo "\n\tIt's a FuncCall\n";
      return $this->verifyFuncall($stmt);
    }elseif ($type == 'Expr_ArrayDimFetch') {
      //echo "\n\tIt's a ArrayDimFetch\n";
      return $this->verifyArrayDimFetch($stmt);
    }elseif ($type == 'Arg'){
      //echo "\n\tIt's a Arg\n";
      return $this->verifyArg($stmt);
    }elseif ($type == 'Scalar_Encapsed'){
      //echo "\n\tIt's a Scalar Encapsed\n";
      return $this->verifyScalarEncapsed($stmt);
    }elseif ($type == 'Expr_Variable'){
      //echo "\n\tIt's a Expr Variable\n";
      return $this->verifyExprVariable($stmt);
    }elseif ($type == 'Scalar_String'){
      //echo "\n\tIt's a Scalar String\n";
      //do nothing
    }elseif ($type == 'Stmt_Echo') {
      //echo "\n\tIt's a Stmt Echo\n";
      return $this->verifyStmtEcho($stmt);
    }elseif ($type == 'Stmt_InlineHTML'){
      //echo "\n\tIt's a Stmt InlineHTML";
      //do nothing
    }elseif ($type == 'Expr_BinaryOp_Concat'){
      //echo "\n\tIt's a Expr BinaryOp Concat\n";
      return $this->verifyBinaryOpConcat($stmt);
    }elseif ($type == 'Expr_StaticCall') {
      //echo "\n\tIt's a Expr Static Call";
      return $this->verifyExprStaticCall($stmt);
    }elseif ($type == 'Stmt_Nop') {
      //ignore
    }elseif ($type == 'Stmt_If') {
      return $this->verifyIfStatement($stmt);
    }elseif ($type == 'Stmt_While') {
      return $this->verifyWhileStatement($stmt);
    }elseif ($type == 'Stmt_For') {
      return $this->verifyForStatement($stmt);
    }elseif ($type == 'Stmt_Else') {
      return $this->verifyElseStatement($stmt);
    }elseif ($type == 'Stmt_ElseIf') {
       return $this->verifyElseIfStatement($stmt);
     }elseif ($type == 'Scalar_LNumber'){
       return $this->verifyScalarLNumber($stmt);
     }elseif ($type == 'Stmt_Switch'){
       return $this->verifySwitch($stmt);
     }elseif ($type == 'Stmt_Case'){
       return $this->verifyCase($stmt);
     }elseif ($type == 'Stmt_Function'){
       return $this->verifyFunction($stmt);
     }

    else{
	  echo Colours::Brown()."Node $type not processed, potentially there is a problem on this ignored lines\n".Colours::RESET();
    }

  }

  private function verifyFunction($stmt){
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function verifyCase($stmt){
    if($stmt->cond != null){
        $this->verifyStatement($stmt->cond);
    }
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function verifySwitch($stmt){
    if($stmt->cond != null){
        $this->verifyStatement($stmt->cond);
    }
    if($stmt->cases != null){
      foreach($stmt->cases as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function verifyScalarLNumber($stmt){
    return array($stmt->value, "var");
  }

  private function verifyForStatement($stmt){
    if($stmt->init != null){
      foreach($stmt->init as $part){
        $this->verifyStatement($part);
      }
    }
    if($stmt->cond != null){
      foreach($stmt->cond as $part){
        $this->verifyStatement($part);
      }
    }
    if($stmt->loop != null){
      foreach($stmt->loop as $part){
        $this->verifyStatement($part);
      }
    }

    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }

  }
  private function verifyElseStatement($stmt){
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function verifyElseIfStatement($stmt){
    if($stmt->cond != null){
      $this->verifyStatement($stmt->cond);
    }
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function verifyIfStatement($stmt){
    if($stmt->cond != null){
      $this->verifyStatement($stmt->cond);
    }
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
    if($stmt->elseifs != null){
      foreach($stmt->elseifs as $part){
        var_dump($part->getType());
        $this->verifyStatement($part);
      }
    }
    if($stmt->else != null){
      $this->verifyStatement($stmt->else);
    }
  }

  private function verifyWhileStatement($stmt){
    if($stmt->cond != null){
      $this->verifyStatement($stmt->cond);
    }
    if($stmt->stmts != null){
      foreach($stmt->stmts as $part){
        $this->verifyStatement($part);
      }
    }
  }

  private function isVulnerable($name){
    return in_array($name, $this->vulnerables)
    || in_array($name, $this->entryPoints);
  }

  private function verifyAssignment($stmt){
    //print_r($stmt->var->name);
    $expr = $stmt->expr;
    $exprtype = $expr->getType();
    //print_r($expr->getType());
    $fun = array($stmt->var->name);
    $argsfun = $this->verifyStatement($stmt->expr);
    if($argsfun != null){
      if(is_array($argsfun[0])){
        $fun = array_merge($fun, $argsfun);
      }else{
        array_push($fun, $argsfun);
      }

    $firstElem = $fun[0];
    $finalStuff = array();
    for($i=1; $i<sizeof($fun); $i++){
      $partStuff =array();
      if($fun[$i] != null){
        array_push($partStuff, $fun[$i][0]);
        if(array_key_exists(1,$fun[$i])){
          array_push($partStuff,$fun[$i][1]);
        }else{
          array_push($partStuff, "funcall");
        }
        array_push($finalStuff, $partStuff);
      }

    }
    $this->PatternsIdentifier->assign($firstElem, $finalStuff);

  }else{
    $firstElem = $fun[0];
    $this->PatternsIdentifier->assign($firstElem, array(array(null, "var")));
  }
    // echo $firstElem."\n";
    // var_dump($finalStuff);

    return array($firstElem, "var");

  }

  private function treatConcates($fun){

    $temp = array();
    $j=0;
    for($h=1; $h<sizeof($fun);$h++){
      for($i=0; $i<sizeof($fun[$h]); $i++){
        if(!(is_array($fun[$h][$i]))){
          continue;
        }
        $temp[$j]=$fun[$h][$i];
        $j++;
      }
    }
    for($i=0; $i<sizeof($temp); $i++){
      if(is_array($temp[$i][0])){
        $fun[$i+1]=$temp[$i][0];
      }elseif(is_array($temp[$i])){
        $fun[$i+1]=$temp[$i];
      }
    }
    return $fun;
  }

  private function verifyFuncall($stmt){
    $fun = array($stmt->name->parts[0]);
    $args = $stmt->args;
    $argsfun = array();
    foreach ($args as $arg){
      if($arg != null ){
        $partArg = $this->verifyStatement($arg);
        if($partArg != null && is_array($partArg) && $partArg[0] != null){
          $argsfun = array_merge($argsfun, $partArg);
        }
      }

    }

    if($argsfun == null){
      return array($stmt->name->parts[0]);
    }


    $fun = array_merge($fun, $argsfun);


    if(is_array($fun[1][0])){
      $fun=$this->treatConcates($fun);
    }
    $firstElem = $fun[0];



    for($i=1; $i<sizeof($fun); $i++){
      if($fun[$i] != null){
        $secondElem = $fun[$i][0];
        if(array_key_exists(1,$fun[$i])){
          $thirdElem = $fun[$i][1];
        }else{
          $thirdElem = "funcall";
        }
      }
      // echo $firstElem . " ". $secondElem . " ". $thirdElem;
      // echo "\n";
      $this->PatternsIdentifier->funcall($firstElem, $secondElem, $thirdElem);
    }
    return array($stmt->name->parts[0]);
  }

  private function verifyScalarEncapsed($stmt){
    $parts = array();
    foreach ($stmt->parts as $element) {
      if($element instanceof PhpParser\Node\Expr\Variable){

        array_push($parts, $this->verifyStatement($element));

      }
    }

    return $parts;
  }

  private function verifyArrayDimFetch($stmt){
    if(!($stmt instanceof PhpParser\Node\Expr\ArrayDimFetch)){
      echo "Error: trying to parse something as ADF that isnt one.\n";
    }
    $this->verifyStatement($stmt->dim);
    return array($stmt->var->name, "fetch");
  }

  private function verifyArg($stmt){
    $fun = $this->verifyStatement($stmt->value);
    return array($fun);
  }

  private function verifyExprVariable($stmt){
    return array($stmt->name, "var");
  }

  private function verifyStmtEcho($stmt){
    $fun = array("echo");
    $argsfun = $this->verifyStatement($stmt->exprs[0]);
    if(is_array($argsfun[0])){
      $fun = array_merge($fun, $argsfun);
    }else{
      array_push($fun, $argsfun);
    }

    $firstElem = $fun[0];
    for($i=1; $i<sizeof($fun); $i++){
      if($fun[$i] != null){
        $secondElem = $fun[$i][0];
        if(array_key_exists(1,$fun[$i])){
          $thirdElem = $fun[$i][1];
        }else{
          $thirdElem = "funcall";
        }

      }
      //echo $firstElem.$secondElem.$thirdElem."\n";
      $this->PatternsIdentifier->funcall($firstElem, $secondElem, $thirdElem);
    }

  }

  private function verifyBinaryOpConcat($stmt){
    $left = $this->verifyStatement($stmt->left);
    $right = $this->verifyStatement($stmt->right);

    $concat=array();
    if($left != null){
      if(is_array($left[0])){
        foreach($left as $part){
            array_push($concat, $part);

        }
      }else{
          array_push($concat,$left);

      }
    }
    if($right != null){
      array_push($concat,$right);
    }

    return $concat;
  }

  private function verifyExprStaticCall($stmt){
    $fun = array($stmt->class->parts[0]."::".$stmt->name);
    $args = $stmt->args;
    $argsfun = array();
    foreach ($args as $arg){
      $argsfun = array_merge($argsfun, $this->verifyStatement($arg));
    }
    if($argsfun == null){
      return array($stmt->name->parts[0]);
    }

    $fun = array_merge($fun, $argsfun);
    $firstElem = $fun[0];
    $endFormat = "";
    if(is_array($fun[1][0])){
      $fun[1] = $fun[1][0];
    }

    $firstElem = $fun[0];
    if(is_array($fun[1][0])){
      $fun[1] = $fun[1][0];
    }

    for($i=1; $i<sizeof($fun); $i++){
      if($fun[$i] != null){
        $secondElem = $fun[$i][0];
        if(array_key_exists(1,$fun[$i])){
          $thirdElem = $fun[$i][1];
        }else{
          $thirdElem = "funcall";
        }
      }
      echo $firstElem . $secondElem . $thirdElem;
      echo "\n";
      $this->PatternsIdentifier->funcall($firstElem, $secondElem, $thirdElem);

    }
    return array($firstElem);
  }
}
