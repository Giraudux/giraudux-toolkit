<?php

$hash_algo = "sha512";
$fs_filename = "data";

try {
  if(isset($_GET["id"])) {
    if(is_file($fs_filename."/".$_GET["id"])) {
      $result = file_get_contents($fs_filename."/".$_GET["id"]);
      if($result !== false) {
        echo $result;
      }
    }
  }
} catch(Exception $e) {
  //echo $e;
}

?>
