<?php

require 'TrueAnalyzer.php';

$debug = False;
$flags = getopt("f:p:");

$slice=$flags['f'];
if(!$slice) {
  echo "Usage: analyzer <-f file> [-p patternsFile]\n";
  exit(-1);
}

$patternsFile = $flags['p'];
if(!$patternsFile){
	$patternsFile = "patterns.txt";
}
if($debug) echo $patternsFile;
$PatternsIdentifier = new PatternsIdentifier();
$handle = fopen($patternsFile, "r");
if ($handle) {
	$lineNmbr = 0;
	while (($line = fgets($handle)) !== false) {
		$line = str_replace("\n", "", $line);
		$line = str_replace("\$", "", $line);

		$vulnName = $line;
		$lineNmbr = $lineNmbr +1;
		if(($line = fgets($handle)) !== false) {
			$line = str_replace("\n", "", $line);
			$line = str_replace("\$", "", $line);
			$entryPoints = $line;
			$lineNmbr = $lineNmbr +1;
		} else {
			echo "pattern file has an error on line:  $lineNmbr \n";
			echo "Ignoring the current pattern and the rest of the file\n";
			break;
		}
		if(($line = fgets($handle)) !== false) {
			$line = str_replace("\n", "", $line);
			$line = str_replace("\$", "", $line);
			$sanitizations = $line;
			$lineNmbr = $lineNmbr +1;
		} else {
			echo "pattern file has an error on line:  $lineNmbr \n";
			echo "Ignoring the current pattern and the rest of the file\n";
			break;
		}
		if(($line = fgets($handle)) !== false) {
			$line = str_replace("\n", "", $line);
			$line = str_replace("\$", "", $line);
			$sinkPoints = $line;
			$lineNmbr = $lineNmbr +1;
		} else {
			echo "pattern file has an error on line:  $lineNmbr \n";
			echo "Ignoring the current pattern and the rest of the file\n";
			break;
		}
		//Empty line at the end
		if(($line = fgets($handle)) !== false) {

			$lineNmbr = $lineNmbr +1;
			if($line != "\n") {
				echo "$line\n";
				echo "num non empty line: $lineNmbr \n";
				echo "Ignoring this line and the rest of the file\n";
				break;
			}
		}
		//Creating pattern
		if($debug){
			echo $vulnName;
			echo $entryPoints;
			echo $sanitizations;
			echo $sinkPoints;
			echo "\n";
		}
		$pattern = new Pattern($vulnName,$entryPoints,$sanitizations,$sinkPoints);
		$PatternsIdentifier->addPattern($pattern);
	}

	fclose($handle);
} else {
	echo "Error opening file:  $patternsFile\n";
	exit(-1);
}

//$PatternsIdentifier->assign("variavel_bad","_SERVERS","fetch");
//$PatternsIdentifier->assign("variavel_bad_2","variavel_bad","var");
//$PatternsIdentifier->assign("variavel_bad","htmlentities","funcall");
////$PatternsIdentifier->assign($leftVar,$rValue,$type);
//$PatternsIdentifier->funcall("file_get_contents","variavel_bad","var");
//$PatternsIdentifier->funcall("file_get_contents","variavel_bad_2","var");
//$PatternsIdentifier->assign("variavel_bad_2","htmlentities","funcall");
//$PatternsIdentifier->assign("variavel_bad_2","lixo","var");
//$PatternsIdentifier->assign("variavel_bad","_SERVERS","fetch");
//$PatternsIdentifier->funcall("file_get_contents","variavel_bad_2","var");
//$PatternsIdentifier->report();
//fread(STDIN,1);

//echo $flags['f'];
$analyzer = new TrueAnalyzer($PatternsIdentifier);
//$analyzer->addPatterns();
$analyzer->analyzeFile($slice);
