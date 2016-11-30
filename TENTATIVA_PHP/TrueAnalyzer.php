<?php

require 'php-parser/lib/bootstrap.php';

ini_set('xdebug.max_nesting_level', 3000);

use PhpParser\Error;
use PhpParser\ParserFactory;

class TrueAnalyzer {

  private $entryPoints = array('_POST', '_GET', '_COOKIE');
  private $vulnerables = array(); //variables that are vulnerable

  public function analyzeFile($file){
    $stmts = $this->parseFile($file);
    // debug
    print_r($stmts);
    //echo "\n\n";

    foreach($stmts as $stmt){
      //echo $stmt->getType();
      //echo "\n";

      print_r($this->verifyStatement($stmt));
    }

    //if($stmts[$i]->hasAttribute('endLine')){
    //     echo "xD123";
    //     echo $stmts[$i]->getAttribute('endLine');
    //   }
  }

  private function parseFile($file){
    $code = file_get_contents($file);
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
    $fun = array($stmt->var->name, $this->verifyStatement($stmt->expr));
    //var_dump($fun);
    if(sizeof($fun[1][0])>1){
      for($i=0; $i<sizeof($fun[1]); $i++){
        echo $fun[0] . " " . $fun[1][$i][0] . " " . $fun[1][$i][1] . "\n";
      }
    }elseif(sizeof($fun[1][0]) == 1){
      if(sizeof($fun[1])== 1){
      echo $fun[0] . " " . $fun[1] . " funcall\n";
    }else{
      echo $fun[0] . " " . $fun[1][0] . " " . $fun[1][1] ."\n";
    }
    }else{
      echo "rip\n";
    }

  }

  private function verifyFuncall($stmt){
    $fun = array($stmt->name->parts);
    $args = $stmt->args;
    foreach ($args as $arg){
      array_push($fun, $this->verifyStatement($arg));
    }
    //var_dump($fun);
    for($j=1; $j<sizeof($fun); $j++){
      if(sizeof($fun[$j][0][0]) > 1){
        for($i=0; $i<sizeof($fun[$j][0]); $i++){
          echo $fun[0][0] .  " " . $fun[$j][0][$i][0] . " " . $fun[$j][0][$i][1] . "\n";
        }
      }else{
        for($i=0; $i<sizeof($fun[$j]); $i++){
          echo $fun[0][0] .  " " . $fun[$j][$i][0] . " " . $fun[$j][$i][1] . "\n";
      }
      }
    }

    return $stmt->name->parts[0];
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
    return array($this->verifyStatement($stmt->value));
  }

  private function verifyExprVariable($stmt){
    return array($stmt->name, "var");
  }

  private function verifyStmtEcho($stmt){
    //var_dump($stmt->exprs[0]);
    return array("echo", $this->verifyStatement($stmt->exprs[0]));
  }
}
