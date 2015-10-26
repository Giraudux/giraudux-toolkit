<?php

$hash_algo = "sha512";
$sqlite3_filename = "dfs.sqlite3";

try {
  if(isset($_POST["data"])) {
    $db = new SQLite3($sqlite3_filename, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $db->exec("CREATE TABLE IF NOT EXISTS dfs_data(id TEXT PRIMARY KEY NOT NULL, data TEXT NOT NULL)");
    $stmt = $db->prepare("INSERT OR IGNORE INTO dfs_data (id, data) VALUES (:id, :data)");
    $hash_sum = hash($hash_algo, $_POST["data"]);
    $stmt->bindValue(":id", $hash_sum, SQLITE3_TEXT);
    $stmt->bindValue(":data", $_POST["data"], SQLITE3_TEXT);
    if($stmt->execute() !== false) {
      echo $hash_sum;
    }
  }
} catch(Exception $e) {
  //echo $e;
}

if(isset($stmt)) {
  $stmt->close();
}

if(isset($db)) {
  $db->close();
}

?>
