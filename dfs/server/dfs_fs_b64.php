<?php

$hash_algo = "sha512";
$path = "data";

$reply = array();
$info = array();

function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
  throw new Exception($errstr, $errno);
  return true;
}

function urlsafe_b64encode($data) {
  return str_replace(array("+", "/", "="), array("-", "_", ""), base64_encode($data));
}

function urlsafe_b64decode($data) {
  return base64_decode(str_replace(array("-", "_"), array("+", "/"), $data) . str_repeat("=", strlen($data) % 4));
}

set_error_handler("error_handler");

try {
  if(isset($_GET["id"])) {
    $data = file_get_contents($path.DIRECTORY_SEPARATOR.$_GET["id"]);
    if($data === false) {
      throw new Exception("file_get_contents returned false");
    }
    $info["data"] = urlsafe_b64encode($data);
  }
} catch(Exception $e) {
  array_push($info, $e->getMessage());
}

try {  
  if(isset($_POST["data"])) {
    $data = urlsafe_b64decode($_POST["data"]);
    $id = hash($hash_algo, $data);
    $len = file_put_contents($path.DIRECTORY_SEPARATOR.$id, $data);
    if($len === false || $len != strlen($data)) {
      throw new Exception("file_put_contents returned false");
    }
    $reply["id"] = $id;
  }
} catch(Exception $e) {
  array_push($info, $e->getMessage());
}

$reply["info"] = $info;
echo json_encode($reply);

?>
