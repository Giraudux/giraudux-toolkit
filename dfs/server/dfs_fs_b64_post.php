<?php

$hash_algo = "sha512";
$fs_filename = "data";

try {
  if(isset($_POST["data"])) {
    if(is_dir($fs_filename) || mkdir($fs_filename, 0755, true)) {
      $hash_sum = hash($hash_algo, $_POST["data"]);
      $filename = $fs_filename."/".$hash_sum;
      if((is_file($filename) ||
         file_put_contents($filename, $_POST["data"]) !== false) &&
         hash_file($hash_algo, $filename) == $hash_sum) {
        echo $hash_sum;
      }
    }
  }
} catch(Exception $e) {
  //echo $e;
}

?>
