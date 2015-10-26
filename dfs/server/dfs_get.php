<?php

$hash_algo = "sha512";
$sqlite3_filename = "dfs.sqlite3";

try {
  if(isset($_GET["id"])) {
    $db = new SQLite3($sqlite3_filename, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT data FROM dfs_data WHERE id=:id");
    $stmt->bindValue(":id", $_GET["id"], SQLITE3_TEXT);
    $result = $stmt->execute();
    if($result !== false) {
      $result = $result->fetchArray(SQLITE3_ASSOC);
      if($result !== false) {
        foreach($result as $data) {
          echo $data;
        }
      }
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
