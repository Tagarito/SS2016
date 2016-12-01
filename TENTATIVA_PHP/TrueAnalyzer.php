<?php

require 'php-parser/lib/bootstrap.php';
require 'PatternDetector.php';
ini_set('xdebug.max_nesting_level', 3000);

use PhpParser\Error;
use PhpParser\ParserFactory;

class TrueAnalyzer {

  private $PatternsIdentifier;

  function __construct ($patternsIdentifier) {
	  $this->PatternsIdentifier = $patternsIdentifier;
  }

  private $entryPoints = array('_POST', '_GET', '_COOKIE');
  private $vulnerables = array(); //variables that are vulnerable

  public function analyzeFile($file){
    if(($stmts = $this->parseFile($file)) == -1){
      exit -1;
    }
    // debug
    print_r($stmts);
    //echo "\n\n";

    foreach($stmts as $stmt){
      //echo $stmt->getType();
      //echo "\n";

      $this->verifyStatement($stmt);
    }
    $this->PatternsIdentifier->report();

    //if($stmts[$i]->hasAttribute('endLine')){
    //     echo "xD123";
    //     echo $stmts[$i]->getAttribute('endLine');
    //   }
  }

  private function parseFile($file){
      if(($code = file_get_contents($file)) == false){
        exit(-1);
      }
    // debug
    // print_r($code);
    // echo "\n\n";

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
      echo "\n\tfodeu";
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
    }
    else{
      echo "nonono\n";
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
    if(is_array($argsfun[0])){
      $fun = array_merge($fun, $argsfun);
    }else{
      array_push($fun, $argsfun);
    }
    // if(sizeof($fun[1][0])>1){
    //   for($i=0; $i<sizeof($fun[1]); $i++){
    //     echo $fun[0] . " " . $fun[1][$i][0] . " " . $fun[1][$i][1] . "\n";
    //   }
    // }elseif(sizeof($fun[1][0]) == 1){
    //   if(sizeof($fun[1])== 1){
    //   echo $fun[0] . " " . $fun[1] . " funcall\n";
    // }else{
    //   echo $fun[0] . " " . $fun[1][0] . " " . $fun[1][1] ."\n";
    // }
    // }else{
    //   echo "rip\n";
    // }
    //var_dump($fun);
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
    echo $firstElem."\n";
    var_dump($finalStuff);

    $this->PatternsIdentifier->assign($firstElem, $finalStuff);
    //var_dump($finalStuff);
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
      $argsfun = array_merge($argsfun, $this->verifyStatement($arg));
    }

    if($argsfun == null){
      return array($stmt->name->parts[0]);
    }
    //echo "xd\n";
    //var_dump($argsfun);

    $fun = array_merge($fun, $argsfun);

    // for($j=1; $j<sizeof($fun); $j++){
    //   if(sizeof($fun[$j][0][0]) > 1){
    //     for($i=0; $i<sizeof($fun[$j][0]); $i++){
    //       echo $fun[0][0] .  " " . $fun[$j][0][$i][0] . " " . $fun[$j][0][$i][1] . "\n";
    //     }
    //   }else{
    //     for($i=0; $i<sizeof($fun[$j]); $i++){
    //       echo $fun[0][0] .  " " . $fun[$j][$i][0] . " " . $fun[$j][$i][1] . "\n";
    //   }
    //   }
    // }
    //var_dump($fun);
    // $firstElem = $fun[0];
    // for($i=1; $i<sizeof($fun); $i++){
    //   echo $firstElem . " " . $fun[$i][0] . " " . $fun[$i][1] . "\n";
    // }
    //var_dump($fun);
    if(is_array($fun[1][0])){
      $fun=$this->treatConcates($fun);
    }
    $firstElem = $fun[0];

    //var_dump($fun);

    for($i=1; $i<sizeof($fun); $i++){
      if($fun[$i] != null){
        $secondElem = $fun[$i][0];
        if(array_key_exists(1,$fun[$i])){
          $thirdElem = $fun[$i][1];
        }else{
          $thirdElem = "funcall";
        }
      }
      echo $firstElem . " ". $secondElem . " ". $thirdElem;
      echo "\n";
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
    // if(is_array($fun)){
    //   return $fun;
    // }

    return array($fun);
  }

  private function verifyExprVariable($stmt){
    return array($stmt->name, "var");
  }

  private function verifyStmtEcho($stmt){
    //var_dump($stmt->exprs[0]);
    // $fun = array("echo", $this->verifyStatement($stmt->exprs[0]));
    // var_dump($fun);
    // if(sizeof($fun[1][0])>1){
    //   for($i=0; $i<sizeof($fun[1]); $i++){
    //     echo $fun[0] . " " . $fun[1][$i][0] . " " . $fun[1][$i][1] . "\n";
    //   }
    // }elseif(sizeof($fun[1][0]) == 1){
    //   if(sizeof($fun[1])== 1){
    //   echo $fun[0] . " " . $fun[1] . " funcall\n";
    // }else{
    //   echo $fun[0] . " " . $fun[1][0] . " " . $fun[1][1] ."\n";
    // }
    // }else{
    //   echo "rip\n";
    // }
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
      echo $firstElem.$secondElem.$thirdElem."\n";
      $this->PatternsIdentifier->funcall($firstElem, $secondElem, $thirdElem);
    }

  }

  private function verifyBinaryOpConcat($stmt){
    $left = $this->verifyStatement($stmt->left);
    $right = $this->verifyStatement($stmt->right);
    // if(!(is_array($left))){
    //   $left = array($left);
    // }
    // if(!(is_array($right))){
    //   $right = array($right);
    // }
    //var_dump($left);

    $concat=array();
    if(is_array($left[0])){
      foreach($left as $part){
        if($left != null){
          array_push($concat, $part);
        }
      }
    }else{
      if($left != null){
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
