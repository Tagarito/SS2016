<?php

require 'TrueAnalyzer.php';

$flags= getopt("f:");

if(empty($flags)) {
  echo "Usage: analyzer -f file\n";
  exit(-1);
}

$analyzer = new TrueAnalyzer();

//echo $flags['f'];
$analyzer->analyzeFile($flags['f']);
