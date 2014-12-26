<!DOCTYPE html>
<!-- Alexis Giraudet -->
<html>
    <head>
        <meta charset="utf-8" />
        <style>
            table
            {
                border-collapse: collapse;
            }
            td, th
            {
                border: 1px solid black;
            }
            .success
            {
                color: green;
            }
            .failure
            {
                color: red;
            }
        </style>
        <title>FTP Checker</title>
    </head>
    <body>
        <form method="post" action="<?= basename(__FILE__) ?>">
            <fieldset>
                <legend>Check FTP</legend>
                <label for="host">Host = </label>
                <input type="text" name="host" id="host" required />
                <br />
                <label for="user">User = </label>
                <input type="text" name="user" id="user" />
                <br />
                <label for="pass">Password = </label>
                <input type="text" name="pass" id="pass" />
                <br />
                <label for="port">Port = </label>
                <input type="number" name="port" id="port" min="0" max="65536" value="21" />
                <br />
                <label for="protocol">Protocol = </label>
                <select name="protocol" id="protocol">
                    <option value="ftp" selected>ftp</option>
                    <option value="sftp">sftp</option>
                </select>
                <br />
                <input type="submit" />
            </fieldset>
        </form>
        <form method="post" action="<?= basename(__FILE__) ?>">
            <fieldset>
                <legend>Check FTP</legend>
                <label for="filezillaxml">filezilla.xml/recentservers.xml = </label>
                <textarea name="filezillaxml" id="filezillaxml" required></textarea>
                <br />
                <input type="submit" />
            </fieldset>
        </form>
        <form method="post" action="<?= basename(__FILE__) ?>">
            <fieldset>
                <legend>Check FTP</legend>
                <label for="filezillaurl">filezilla.xml/recentservers.xml URL = </label>
                <input type="text" name="filezillaurl" id="filezillaurl" required />
                <br />
                <input type="submit" />
            </fieldset>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Host</th>
                    <th>User</th>
                    <th>Password</th>
                    <th>Port</th>
                    <th>Protocol</th>
                    <th>Status</th>
                </tr>
            </thead>
<?php
function check_ftp($host, $user, $pass, $port, $protocol, $timeout=5)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $protocol.'://'.$host);
    curl_setopt($curl, CURLOPT_USERPWD, $user.':'.$pass);
    curl_setopt($curl, CURLOPT_PORT, $port);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    $reply = curl_exec($curl);
    echo "<tbody>\n";
    echo "<tr>\n";
    echo "<td>".$host."</td>\n";
    echo "<td>".$user."</td>\n";
    echo "<td>".$pass."</td>\n";
    echo "<td>".$port."</td>\n";
    echo "<td>".$protocol."</td>\n";
    if(curl_errno($curl))
    {
        echo "<td class=\"failure\">Failure: ".curl_error($curl)."</td>\n";
    }
    else
    {
        echo "<td class=\"success\">Success</td>\n";
    }
    echo "</tr>\n";
    echo "</tbody>\n";
    curl_close($curl);
}

function check_filezillaxml($xml)
{
    $domdoc = new DOMDocument();
    if($domdoc->loadXML($xml))
    {
        $servers = $domdoc->getElementsByTagName('Server');
        foreach($servers as $server)
        {
            $hosts = $server->getElementsByTagName('Host');
            $host = $hosts->item(0)->nodeValue;
            $users = $server->getElementsByTagName('User');
            $user = $users->item(0)->nodeValue;
            $passs = $server->getElementsByTagName('Pass');
            $pass = $passs->item(0)->nodeValue;
            $ports = $server->getElementsByTagName('Port');
            $port = $ports->item(0)->nodeValue;
            $protocols = $server->getElementsByTagName('Protocol');
            switch ($protocols->item(0)->nodeValue)
            {
                case '1': $protocol = 'sftp';
                    break;
                default: $protocol = 'ftp';
            }
            check_ftp($host, $user, $pass, $port, $protocol);
        }
    }
    else
    {
        echo "<p class=\"failure\">Failure: Cannot load XML</p>\n";
        echo "<br  />\n";
        echo "<pre>\n";
        echo htmlentities($xml);
        echo "</pre>\n";
    }
}

if(function_exists('curl_init'))
{
    if(isset($_POST['host']) and isset($_POST['user']) and isset($_POST['pass']) and isset($_POST['port']) and isset($_POST['protocol']))
    {
        check_ftp($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['port'], $_POST['protocol']);
    }
    else if(isset($_POST['filezillaxml']))
    {
        check_filezillaxml($_POST['filezillaxml']);
        //check_filezillaxml(stripslashes($_POST['filezillaxml']));
    }
    else if(isset($_POST['filezillaurl']))
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $_POST['filezillaurl']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $reply = curl_exec($curl);
        curl_close($curl);
        if(curl_errno($curl))
        {
            echo "<p class=\"failure\">Failure: ".curl_error($curl)."</p>\n";
        }
        else
        {
            check_filezillaxml($reply);
        }
    }
}
else
{
    echo "<p class=\"failure\">Failure: cURL library required</p>\n";
}
?>
        </table>
    </body>
</html>
