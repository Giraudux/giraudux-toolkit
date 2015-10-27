<?php

$hash_algo = "sha512";
$pdo_dsn = "mysql:host=hostname;port=3306;dbname=database";
$pdo_username = "username";
$pdo_password = "password";
$pdo_options = array();
$sql_select = "SELECT user_data FROM dfs_data WHERE user_id = :id";

try {
  if(isset($_GET["id"])) {
    $pdo = new PDO($pdo_dsn, $pdo_username, $pdo_password, $pdo_options);
      $stmt = $pdo->prepare($sql_select);
      if($stmt !== false &&
         $stmt->execute(array(":id" => $_GET["id"]))) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result !== false) {
          echo $result["user_data"];
        }
      }
  }
} catch(Exception $e) {
  //echo $e;
}

?>
