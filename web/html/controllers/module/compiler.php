<?php
// ini_set('display_errors','1');
// error_reporting(E_ALL);
$_ignore_template = true;
class Module_Index extends Controller {

	public function initialize(){
		$erikaVars = '';
		$fullcode = $_POST['code'];
		if ($_POST['type'] == 'var') {
			$erikaSettings = '
				if(isset($erikaVars)) unset($erikaVars);
				$erikaVars = get_defined_vars();
				if ($inLoop) {
					if ($_POST["runTimes"] == 0 || !isset($_POST["runTimes"])) return;
					$_POST["runTimes"]--;
				} else {
					return;
				}
			';
			array_splice($fullcode, $_POST['num'] + 1, 0, $erikaSettings);

			$fh = fopen("compilerTemp2.php", "w") or die("Unable to open file!");
			fclose($fh);
			unlink('compilerTemp2.php');
			$tempFile = fopen("compilerTemp2.php", "w") or die("Unable to open file!");
			
			$tempContent = '';
			foreach ($fullcode as $modyval) {
				$tempText = $modyval;
				$pattern = '/^\s*(do|while|for).*/';
				if (preg_match($pattern, $modyval)) $tempText .= ' $inLoop = true;';
				$pattern = '/^\s*}.*/';
				if (preg_match($pattern, $modyval)) $tempText .= ' $inLoop = false; if(isset($erikaVars)) unset($erikaVars); $erikaVars = get_defined_vars();';
				$tempContent .= $tempText . PHP_EOL;
			}

			fwrite($tempFile, $tempContent);
			fclose($tempFile);

			ob_start();
			include "compilerTemp2.php";
			ob_end_clean();

			unset($erikaVars['fullcode']);
			unset($erikaVars['tempFile']);
			unset($erikaVars['tempContent']);
			unset($erikaVars['modyval']);
			unset($erikaVars['pattern']);
			unset($erikaVars['tempText']);
			unset($erikaVars['erikaSettings']);
			echo json_encode($erikaVars);
		} elseif ($_POST['type'] == 'output') {
			array_splice($fullcode, $_POST['num'] + 1, 0, 'return;');

			$fh = fopen("compilerTemp.php", "w") or die("Unable to open file!");
			fclose($fh);
			unlink('compilerTemp.php');
			$tempFile = fopen("compilerTemp.php", "w") or die("Unable to open file!");
			
			$tempContent = '';
			foreach ($fullcode as $modyval) {
				$tempText = $modyval;
				if (strpos($tempText, 'echo') !== false) {
					$tempText .= ' echo "</br>";';
				}
				$tempContent .= $tempText . PHP_EOL;
			}

			fwrite($tempFile, $tempContent);
			fclose($tempFile);

			include "compilerTemp.php";
		}
	}
	
}

$index = new Module_Index();
return $index->initialize();
?>
