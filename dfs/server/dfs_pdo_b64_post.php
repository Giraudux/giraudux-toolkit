<?php

$hash_algo = "sha512";
$pdo_dsn = "mysql:host=hostname;port=3306;dbname=database";
$pdo_username = "username";
$pdo_password = "password";
$pdo_options = array();
$sql_create = "CREATE TABLE IF NOT EXISTS dfs_data(user_id CHAR(128) PRIMARY KEY NOT NULL, user_data MEDIUMTEXT NOT NULL)";
$sql_insert = "INSERT IGNORE INTO dfs_data (user_id,user_data) VALUES(:id,:data)";

try {
  if(isset($_POST["data"])) {
    $pdo = new PDO($pdo_dsn, $pdo_username, $pdo_password, $pdo_options);
    if($pdo->exec($sql_create) !== false) {
      $hash_sum = hash($hash_algo, $_POST["data"]);
      $stmt = $pdo->prepare($sql_insert);
      if($stmt !== false &&
         $stmt->execute(array(":id" => $hash_sum, ":data" => $_POST["data"]))) {
        echo $hash_sum;
      }
    }
  }
} catch(Exception $e) {
  //echo $e;
}

?>
