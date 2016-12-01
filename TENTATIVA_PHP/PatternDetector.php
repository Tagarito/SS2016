<?php
require 'colours.php';
class Pattern {
  private $vulnName;
  private $entryPoints;
  private $sanitizations;
  private $sinkPoints;



  function __construct($vulnerability,$entry,$sanitiz,$sink) {
	$this->vulnName      = $vulnerability;
	$this->entryPoints   = explode(",", $entry);
	$this->sanitizations = explode(",", $sanitiz);
	$this->sinkPoints    = explode(",", $sink);
  }

  public function getVulnName()		 { return $this->vulnName; }

  public function hasEntry($specialValue) {
	return in_array($specialValue, $this->entryPoints, TRUE);
  }
  public function hasSanitization($specialValue) {
	return in_array($specialValue, $this->sanitizations, TRUE);
  }
  public function hasSink($specialValue) {
	return in_array($specialValue, $this->sinkPoints, TRUE);
  }
}

class Vulnerability {
	/*private $line,*/
	private $isFixed;
	private $vulnName;
	private $badVariable;
	private $sinkPoint;
	private $entryPoint;
	private $sanitization;
	private $colours ;

	public function __construct(/*$line,*/$isFixed,$vulnName,$badVariable,$entryPoint,$sinkPoint,$sanitization ) {
		/*$this->line			= $line;*/
		$this->isFixed 		= $isFixed;
		$this->vulnName 	= $vulnName;
		$this->badVariable 	= $badVariable;
		$this->sinkPoint 	= $sinkPoint;
		$this->entryPoint 	= $entryPoint;
		$this->sanitization = $sanitization;
	}

	private function commonPrint() {
		echo /*"On line: $this->line ".*/"There is a vulnerability: ".Colours::YELLOW()."$this->vulnName".Colours::RESET()."\n";
		echo "The variable: ".Colours::YELLOW()."$this->badVariable".Colours::RESET()." has the entryPoint: ".Colours::YELLOW()."$this->entryPoint".Colours::RESET()."\n";
	}

	public function print() {
		if($this->isFixed) {
			$this->commonPrint();

			echo Colours::GREEN();
			echo "The sink point would be: ".Colours::YELLOW()."$this->sinkPoint".Colours::GREEN().", fortunatly was sanitized with ".Colours::YELLOW()."$this->sanitization".Colours::RESET()."\n";
		} else {
			$this->commonPrint();

			echo Colours::RED();
			echo "The sink point is ".Colours::YELLOW()."$this->sinkPoint".Colours::RESET()."\n";
		}
		echo "\n";
		echo Colours::RESET();
	}
}

class PatternsIdentifier {
	////list<nomeVar,indexPattern>
	// private $goodVariables;
	////list<nomeVar,indexPattern>
	// private $badVariables;

	//list<indexPattern(nomeVar,sanitezed)>
	private $variables = array();
	private $vulnerabilities = array();
	private $patterns = array();
	private $logging = False;


	public function assign($leftVar,$array) {
		foreach ($this->patterns as $patternIndex => $pattern) {
			$array_each = array();
			$whatIsIt = NULL;
			foreach ($array as $key => $element) {
				$rValue = $element[0];
				$type = $element[1];
				//if($rValue == NULL || $type == NULL) {
				//	echo Colours::RED()."Bug Detected on Parser Side\n".Colours::RESET();
				//	continue;
				//}
				switch ($type) {
					case 'fetch':
						$whatIsIt = $this->assignEntry($leftVar,$rValue  ,$patternIndex);
						break;
					case 'var':
						$whatIsIt = $this->assignVar($leftVar,$rValue    ,$patternIndex);
						break;
					case 'funcall':
						$whatIsIt = $this->assignFuncall($leftVar,$rValue,$patternIndex);
						break;
					default:
						echo Colours::RED()."Damn @Tagarito ASSIGN Dont want to point fingers to no one but you should've predicted this crap..\n".Colours::RESET();
						break;
				}
				if($whatIsIt == "bad") break; //if it is bad we can ignore the rest..
				$pairToPush = array('0' => $whatIsIt,'1' => $type, '2' => $rValue );
				array_push($array_each,$pairToPush);
			}
			if($whatIsIt == "bad") continue; //if it is bad we can ignore the rest..
			//if(contains good)
			foreach ($array_each as $key => $value) {
				if($value[0] == "good") {
					$rValue = $value[2];
					switch ($value[1]) {
						case 'fetch':
							$this->assignEntry($leftVar,$rValue  ,$patternIndex);
							break;
						case 'var':
							$this->assignVar($leftVar,$rValue    ,$patternIndex);
							break;
						case 'funcall':
							$this->assignFuncall($leftVar,$rValue,$patternIndex);
							break;
						default:
							echo Colours::RED()."Damn @miguel-amaral not again".Colours::RESET();
							break;
					}
					break;
				}
			}
		}
	}

	public function funcall($funName,$arg,$type) {

		switch ($type) {
			case 'fetch':
			var_dump("funcal: ".$funName." arg: ".$arg);
				$this->funcallWithFetch($funName,$arg);
				break;
			case 'var':
				$this->funcallWithVar($funName,$arg);
				break;
			case 'funcall':
				//No harm cames out of funcall of funcall;
				//echo Colours::PURPLE()."@Tagarito I am Ignoring this \\function: $funName, arg: $arg, type: $type\n".Colours::RESET();
				break;
			default:
				echo Colours::RED()."Damn @Tagarito FUNCALL Dont want to point fingers to no one but you should've predicted this crap..\n".Colours::RESET();
				break;
		}
		//$type -> can be fetch or var :(
		//if funBad -> check Arg is Good or bad -> register vulnerability
	}

	private function funcallWithFetch($funName,$fetchName) {
		foreach ($this->patterns as $patternIndex => $pattern) {
			if($pattern->hasEntry($fetchName) && $pattern->hasSink($funName)) {
				$this->log("sink point $funName used with fetch $fetchName\n");
				$vulnerability = new vulnerability(False,$pattern->getVulnName(),"(DIRECT USE/NO VARIABLE)",$fetchName,$funName,"");
				array_push($this->vulnerabilities,$vulnerability);
			}
		}
	}

	private function getPair($array,$name) {
		foreach ($array as $key => $value) {
			if($value[0] == $name) {
				return $value;
			}
		}
		return NULL;
	}

	private function funcallWithVar($funName,$varName) {
		//For All patterns
		foreach ($this->patterns as $patternIndex => $pattern) {
		 	//If pattern vuln to funcall
			if($pattern->hasSink($funName)) {
				//Check if var bad
				$this->log("variable: $varName looking with $funName on index: $patternIndex\n");
				// var_dump($this->patterns);
				$patternVariables 	= $this->variables[$patternIndex];
				$pair = $this->getPair($patternVariables,$varName);
				// var_dump($patternVariables);
				if($pair) {
					if($pair[1] == 'bad') {
						//Bad var -> vulnerability
						$this->log("variable: $varName entered sink point $funName NOT sanitezed\n");
						$vulnerability = new vulnerability(False,$pattern->getVulnName(),$pair[0],$pair[2],$funName,"");
						array_push($this->vulnerabilities,$vulnerability);
					} else if ($pair[1] == 'good') {
						//Good var -> vulnerability safe
						$this->log("variable: $varName entered sink point $funName OK\n");
						$vulnerability = new vulnerability(True,$pattern->getVulnName(),$pair[0],$pair[2],$funName,$pair[3]);
						array_push($this->vulnerabilities,$vulnerability);
					} else {
						echo Colours::RED()."Damn @miguel-amaral pair without bool.. on funcall with var".Colours::RESET();
					}
				}
			}
		}
	}

	public function report() {
		if($this->vulnerabilities) {
			foreach ($this->vulnerabilities as $key => $vuln) {
				$vuln->print();
			}
		} else {
			echo Colours::GREEN()."There is no problem with your code. GREAT DEVELOPER you are, but remember with great POWER comes great responsability\n".Colours::GREEN();
		}
		// var_dump ($this->variables);
	}

	private function assignEntry($varName,$entryName,$patternIndex) {
		//assing entry to var ; entry can be bad   -> varBecomesBad
		// $keys = array_keys($this->patterns);
		// $size = count($this->patterns);
		// //For all patterns
		// for ($i = 0; $i < $size; $i++) {
		// 	$patternIndex     = $keys[$i];
			$pattern = $this->patterns[$patternIndex];
			//if entry is bad
			if($pattern->hasEntry($entryName)) {
				//moveVarToBad..
				$this->log("variable: $varName is now bad due to $entryName\n");
				$this->moveToBad($patternIndex,$varName,$entryName);
				return "bad";
			}
			return "unknown";
		// }
	}

	//Function that receives arrays of type:
	//list<varName,Sanitized? >
	private function removeFromArrayOfVariables($array,$value) {
		$keys = array_keys($array);
		$size = count($array);
		for ($i = 0; $i < $size; $i++) {
			$key   = $keys[$i];
			$arrayValue = $array[$key];

			if($arrayValue[0] == $value) {
				$this->log("removing the variable: $value\n");
				array_splice($array,$key,1);
			}
		}
		return $array; //TODO maybe break after one occurence found..
	}

	//function that returns if a variable is sanitezed or not from an array of <varName,isSanitezed>
	private function getIsSanitized($array,$varName) {
		$keys = array_keys($array);
		$size = count($array);
		for ($i = 0; $i < $size; $i++) {
			$key   = $keys[$i];
			$arrayValue = $array[$key];

			if($arrayValue[0] == $varName) {
				return $arrayValue[1];
			}
		}
		return "Not_Found"; //TODO maybe break after one occurence found..
	}

	private function moveToGood($patternIndex,$varName,$sanitization) {
		$this->makeVarBool($patternIndex,$varName,"good",NULL,$sanitization);
	}

	private function moveToBad($patternIndex,$varName,$entryName) {
		$this->makeVarBool($patternIndex,$varName,"bad",$entryName,NULL);
	}

	private function makeVarBool($patternIndex,$varName,$bool,$entryName,$sanitization) {
		$patternVariables 		= $this->variables[$patternIndex];

		$keys = array_keys($patternVariables);
		$size = count($patternVariables);
		for ($i = 0; $i < $size; $i++) {
			$key   = $keys[$i];
			$pair = $patternVariables[$key];
			if($pair[0] == $varName) {
				$this->variables[$patternIndex][$key][1] = $bool;
				if($entryName) {
					$this->variables[$patternIndex][$key][2] = $entryName;
				}
				if($sanitization) {
					$this->variables[$patternIndex][$key][3] = $sanitization;
				}
				return; // because otherwise we would be adding another variable
			}
		}
		$newPair = array('0' => $varName, '1' => $bool);
		if($entryName) {
			$newPair[2] = $entryName;
		}
		if($sanitization) {
			$newPair[3] = $sanitization;
		}
		array_push($this->variables[$patternIndex],$newPair);
	}

	private function assignVar($receiverName,$valueName,$patternIndex) {
		//list<indexPattern(nomeVar,good)>
		// $keys = array_keys($this->variables);
		// $size = count($this->variables);
		// for ($i = 0; $i < $size; $i++) {
		// 	$patternIndex   	= $keys[$i];
			$patternVariables 	= $this->variables[$patternIndex];

			if($valueName == NULL) {
				//Remove from wherever it is
				$this->variables[$patternIndex] = $this->removeFromArrayOfVariables($patternVariables,$receiverName);
				return "unknown";
			} else {
				$result = $this->getPair($patternVariables,$valueName);
				switch ($result[1]) {
					case 'good':
						$this->log("variable: $valueName was good $receiverName is good too now\n");
						$this->moveToGood($patternIndex,$receiverName,$result[3]);
						return "good";
					case 'bad':
						$this->log("variable: $valueName was bad $receiverName is bad too now\n");
						$this->moveToBad($patternIndex,$receiverName,$result[2]);
						return "bad";
						//break;
					default: //can be null  //TODO check with @Tagarito // Case where the other variable is not on the pattern
						$this->log("variable: $valueName not in database removing $receiverName from database too\n");
						$this->variables[$patternIndex] = $this->removeFromArrayOfVariables($patternVariables,$receiverName);
						//echo Colours::RED()."Damn @miguel-amaral Dont want to point fingers to no one but you should've predicted this crap..\n".Colours::RESET();
						return "unknown";
				}
			}

		//$value -> good	//Register sanitized
			//move if var existed
		//		 -> bad		//add to bads
			//move if var existed
		//		 -> nothing	//remove from wherever it is
	}

	private function assignFuncall($varName,$funName,$patternIndex) {
		//$type can be funcall -> if funcall sanitizes then goodVar else ignore
		// $keys = array_keys($this->patterns);
		// $size = count($this->patterns);
		// //For all patterns
		// for ($i = 0; $i < $size; $i++) {
		// 	$patternIndex     = $keys[$i];
			$pattern = $this->patterns[$patternIndex];
			// if funcall sanitizes
			if($pattern->hasSanitization($funName)) {
				//then goodVar else ignore
				$this->log("variable: $varName sanitizedBy $funName\n");
				$this->moveToGood($patternIndex,$varName,$funName);
				return 'good';
			}
			return 'unknown';
		// }
	}

	public function addVulnerability($vuln) {
		array_push($this->vulnerabilities, $vuln);
	}

	public function addPattern($pattern) {
		array_push($this->patterns, $pattern);
		array_push($this->variables, array()); //Making sure no nullptr
	}

	private function log($string) {
		if($this->logging) {
			echo Colours::LOGGING()."[ Logging ] ";
			echo Colours::RESET();
			echo $string;
		}
	}
}
?>
