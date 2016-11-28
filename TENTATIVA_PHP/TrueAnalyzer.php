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
      $this->verifyStatement($stmt);
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
      return false;
    }
    $type = $stmt->getType();
    if($type == "Expr_Assign"){
      echo "\n\tIt's an Assign\n";
      $this->verifyAssignment($stmt);
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
    $type = $expr->getType();
    print_r($expr->getType());
    if($type == 'Expr_ArrayDimFetch'){
      //possible vulnerability if $expr->var is vulnerable
      if($this->isVulnerable($expr->var->name)){
        array_push($this->vulnerables, $stmt->var->name);
        return ; //meaning tf has a vulnerability
      }else{
        return ;
      }
    }elseif ($type == 'Scalar_Encapsed') {
      foreach ($expr->parts as $element) {
        if($element->getType() == 'Expr_Variable'){
          if($this->isVulnerable($element->name)){
            array_push($this->vulnerables, $stmt->var->name);
            break;
          }
        }
      }
    }
    //print_r($this->vulnerables);
    //print_r($stmt->expr->getType());
  }

}
