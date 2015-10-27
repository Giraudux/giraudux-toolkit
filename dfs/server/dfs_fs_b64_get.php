<?php

$hash_algo = "sha512";
$fs_filename = "data";

try {
  if(isset($_GET["id"])) {
    $filename = $fs_filename."/".$_GET["id"];
    if(is_file($filename)) {
      $result = file_get_contents($filename);
      if($result !== false) {
        echo $result;
      }
    }
  }
} catch(Exception $e) {
  //echo $e;
}

?>
