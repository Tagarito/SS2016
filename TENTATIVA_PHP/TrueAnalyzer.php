<?php

require 'php-parser/lib/bootstrap.php';

ini_set('xdebug.max_nesting_level', 3000);

use PhpParser\Error;
use PhpParser\ParserFactory;

class TrueAnalyzer {

  public function analyzeFile($file){
    $stmts = $this->parseFile($file);
    // debug
    print_r($stmts);
    echo "\n\n";

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
    //print_r($stmt);
  }

}

//$code=file_get_contents("../../SS2016/examples/sqli_01.txt");
//
//
//echo $code;
//
//
//$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
//$prettyPrinter = new PrettyPrinter\Standard;
//
//try {
//    // parse
//    $stmts = $parser->parse($code);
//
//    // pretty print
//    //$code = $prettyPrinter->prettyPrint($stmts);
//
////    print_r($stmts);
//} catch (Error $e) {
//    echo 'Parse Error: ', $e->getMessage();
//}
//
