<?php
ini_set('display_errors','1');
error_reporting(E_ALL);
$_ignore_template = true;

class Code_Index extends Controller {
  public $inputLanguage = null;
  public $outputLanguage = null;

  public function initialize() {
    unset($_SESSION['variables']);
    unset($_SESSION['converted']);

    $lines = explode("\n" , $_POST['code']);
    print_r($lines); echo '<br/>';
    $data['syntax'] = $this->getData('syntax/getAllUnderLanguage', array(), $_POST);
    $inlang = $this->getData('language/getByID', array(), $_POST);
    $this->inputLanguage = $inlang[0];
    $outPost['lid'] = $_POST['target'];
    $outlang = $this->getData('language/getByID', array(), $outPost);
    $this->outputLanguage = $outlang[0];

    $found = "no statement found with this structure in this language";
    foreach ($lines as $line) {
      foreach ($data['syntax'] as $syn) {
        $tempFound = preg_replace( '/(\^)(.*?)(\^)/' , '(.*?)' , $syn->struct); // ^isvar^=^isequ/isvar/isnum^; // (.*?)=(.*?);
        // ^isfunc^\(^isparam^\); // (.*?)\((.*?))\);
        if (preg_match('/' . $tempFound . '/', $line)) {
          $found = $syn->name;
          $post['lid'] = $_POST['target'];
          $post['sid'] = $syn->sid;
          $tar = $this->getData('syntax/get', array(), $post);
          $tar = $tar[0];
          $this->errorChecking($syn, $line, $tempFound, $tar);
          break;
        }
      }
      $_SESSION['converted'][] = '\n';
    }

    echo '<br/>' . $found . '<br/>';
    print_r($_SESSION['converted']);
  }

  public function errorChecking( $syntax, $code, $translation, $target ) {

    preg_match_all( '/(\^)(.*?)(\^)/' , $syntax->struct, $matches);
    $patterns = $matches[0];
    preg_match_all( '/(\^)(.*?)(\^)/' , $target->struct, $tarMatches);
    $tarPatterns = $tarMatches[0];
    print_r($patterns);echo '<br/>';
    print_r($tarPatterns);echo '<br/>';

    $spliced = array();
    while(strlen($translation) > 0) {
      if (strpos($translation, '(.*?)') == 0) {
        $spliced[] = substr($translation, 0, 5);
        $translation = substr($translation, 5);
      } else {
        if (strpos($translation, '(.*?)') > -1) {
          $tempEnd = strpos($translation, '(.*?)');
          $spliced[] = substr($translation, 0, $tempEnd);
          $translation = substr($translation, $tempEnd);
        } else {
          $spliced[] = $translation;
          $translation = '';
        }
      }
    }
    print_r($spliced);echo '<br/>';

    $iLimit = sizeof($spliced);
    $patternCounter = 0;
    for ($i = 0;$i < $iLimit; $i++) { 
      $piecePattern = $spliced[$i];
      if ($spliced[$i] == '(.*?)') {
        if ($i > 0) {
          $piecePattern = $spliced[$i - 1] . $piecePattern;
        }
        if ($i < $iLimit - 1) {
          $piecePattern = $piecePattern . $spliced[$i + 1]; 
        }
      }
      echo 'piecePattern: ' . $piecePattern . '<br/>';
      preg_match('/' . $piecePattern . '/', $code, $codePiece); //$codePiece = 
      print_r($codePiece); echo '<br/>';

      if ($spliced[$i] == '(.*?)') $thePattern = $this->validate($codePiece[1], $patterns[$patternCounter]);
      if ($spliced[$i] == '(.*?)') $_SESSION['converted'][] = $this->convert($codePiece[1], $thePattern, $tarPatterns[$patternCounter++]);
      else $_SESSION['converted'][] = $piecePattern;

    }
    print_r($_SESSION['converted']);
  }

  public function validate($actual, $pattern) {
    $pattern = str_replace('^', '', $pattern);
    $pattern = explode("/", $pattern);
    print_r($pattern);
    echo '<br/>';

    $valid = false;
    $saveVal = '';
    foreach ($pattern as $val) {
      $valid = $this->$val($actual);
      if ($valid) {
        $saveVal = $val;
        break;
      }
    }

    if (!$valid) {
      echo $actual . " does not match any syntax" . '<br/>';
      exit;
    } else {
      echo $actual . " " . $saveVal . '<br/>';
    }

    return $saveVal;
  }

  public function isvar($target) {
    $option = $this->inputLanguage;
    print_r($option); echo '<br/>';
    $start = 0;
    if ($option->omniPrecedence) {
      if (substr($target, 0, 1) != $option->variablePrecedence) return false;
      $start++;
    }
    if ($this->isnum(substr($target, $start, 1))) return false;
    return true;
  }

  public function isope($target) {
    $operators = array("+", "-", "*", "/");
    if (!in_array($target, $operators)) {
      return false;
    }
    return true;
  }

  public function isnum($target) {
    if (!is_numeric($target)) return false;
    return true;
  }

  public function isequ($target) {
    
  }

  public function convert($actual, $targetPattern, $pattern) {
    echo "converting: <br/>";
    $pattern = str_replace('^', '', $pattern);
    $pattern = explode("/", $pattern);
    print_r($pattern);
    echo '<br/>';

    $valid = false;
    $saveVal = '';
    $settings = array();
    foreach ($pattern as $val) {
      if (strpos($val, '(') !== FALSE) {
        $setTemp = substr($val, strpos($val, '(') + 1, -1);
        $setTemp = str_replace('\(\)', '', $setTemp);
        echo "setemp: " . $setTemp . '<br/>';
        $settings = explode(",", $setTemp);
        $val = substr($val, 0, strpos($val, "("));
      }
      if ($val == $targetPattern) $valid = true;
      if ($valid) {
        $saveVal = 'convert' . $val;
        break;
      }
    }

    if (!$valid) {
      echo $actual . " does not have a coresponding syntax for output" . '<br/>';
      exit;
    } else {
      echo $actual . " converted to " . $saveVal . '<br/>';
    }
    print_r($settings); echo '<br/>';
    return $this->$saveVal($actual, $settings);
  }

  public function convertisnum($target, $settings = array()) {
    return $target;
  }

  public function convertisvar($target, $settings = array()) {
    $output = $target;
    if ($this->inputLanguage->omniPrecedence) {
      $output = str_replace($this->inputLanguage->variablePrecedence, '', $output);
    }

    if (in_array("d", $settings)) {
      if (!isset($_SESSION['variables'][$target])) {
        $_SESSION['variables'][$target] = true;
        $precedence = $this->outputLanguage->declarationPrecedence;
        if ($this->outputLanguage->spacedPrecedence) $precedence .= ' ';
        $output =  $precedence . $output;
      }
    } else if ($this->outputLanguage->omniPrecedence) {
      $precedence = $this->outputLanguage->declarationPrecedence;
      if ($this->outputLanguage->spacedPrecedence) $precedence .= ' ';
      $output =  $precedence . $output;
    }
    return $output;
  }
  //public function patternParser($)

}

$translate = new Code_Index();
$translate->initialize();
?>