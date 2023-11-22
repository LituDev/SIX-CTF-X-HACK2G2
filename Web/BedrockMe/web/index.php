<?php

require __DIR__.'/vendor/autoload.php';

error_reporting(0);

const SESSION_ID = 2;
const TYPE_HANDSHAKE = 0x09;
const TYPE_STAT = 0x00;
const TIMEOUT = 3;

$defaultDriver = "Files";
$psr16 = new \Phpfastcache\Helper\Psr16Adapter($defaultDriver);

function getToken($socket, $type = TYPE_HANDSHAKE) {
    if (!$socket) {
        return false;
    }

    $packet = pack("c3N", 0xFE, 0xFD, TYPE_HANDSHAKE, SESSION_ID);
    if (fwrite($socket, $packet, strlen($packet)) === FALSE) {
        throw new \Exception("Unable to write to socket.");
    }

    $response = fread($socket, 2056);
    if (empty($response)) {
        throw new \Exception("Unable to authenticate connection.");
    }

    $response_data = unpack("c1type/N1id/a*token", $response);
    if (empty($response_data['token'])) {
        throw new \Exception("Unable to authenticate connection.");
    }

    return $response_data['token'];
}

function getInfo($host, $port) {
    $error = "";
    $socket = fsockopen('udp://' . $host, $port, $errno, $error, TIMEOUT);
    if (!$socket) {
        return;
    }
    stream_set_timeout($socket, TIMEOUT, 0);
    stream_set_blocking($socket, true);
    if (!$token = getToken($socket)) {
        return;
    }

    $packet = pack("c3N2", 0xFE, 0xFD, TYPE_STAT, SESSION_ID, $token);
    $packet = $packet . pack("c4", 0x00, 0x00, 0x00, 0x00);
    if (!fwrite($socket, $packet, strlen($packet))) {
        throw new \Exception("Unable to write to socket.");
    }

    fread($socket, 16);
    $response = fread($socket, 2056);

    $payload = explode("\x00\x01player_\x00\x00", $response);
    $info_raw = explode("\x00", rtrim($payload[0], "\x00"));
    if (count($info_raw) % 2) {
        throw new \Exception("Server returned malformed information.");
    }

    $info = [];
    foreach (array_chunk($info_raw, 2) as $pair) {
        if (!isset($pair[1])) {
            continue;
        }
        list($key, $value) = $pair;
        if ($key == "hostname") {
            $key = "motd";
            $value = preg_replace('/[\x00-\x1F\x80-\xFF]./', '', $value);
        }
        $info[$key] = $value;
    }

    $info['hostname'] = $host;
    $info['players'] = [];
    if (isset($payload[1])) { 
        $players_raw = rtrim($payload[1], "\x00");
        $players = [];
        if (!empty($players_raw)) {
            $players = explode("\x00", $players_raw);
        }
        $info['players'] = $players;
    }

    return $info;
}

function ping($host, $port)  {
    $socket = fsockopen('udp://' . $host, $port, $errno, $error, TIMEOUT);
    if (!$socket) {
        throw new \Exception("Unable to connect to server.");
    }
    stream_set_timeout($socket, TIMEOUT, 0);
    stream_set_blocking($socket, true);

    $packet = new \raklib\protocol\UnconnectedPing();
    $packet->sendPingTime = time();
    $packet->clientId = SESSION_ID;

    $pk = new \raklib\protocol\PacketSerializer();
    $packet->encode($pk);

    $packet = $pk->getBuffer();
    if (!fwrite($socket, $packet, strlen($packet))) {
        throw new \Exception("Unable to write to socket.");
    }

    $bf = fread($socket, 2056);
    if($bf === false){
        throw new \Exception("Unable to read from socket.");
    }

    $packet = new \raklib\protocol\UnconnectedPong();
    $packet->decode(new \raklib\protocol\PacketSerializer($bf));

    return $packet;
}

if(isset($_POST['server'])){
    try {
        $serverIp = explode(':', $_POST['server']);
        if(!isset($serverIp[1])){
            $serverIp[1] = 19132;
        }else{
            $serverIp[1] = intval($serverIp[1]);
        }

        $pong = ping($serverIp[0], $serverIp[1]);
        $ret = explode(';', $pong->serverName);
        if(isset($ret[6])){
            $serverId = $ret[6];
        }else{
            throw new \Exception("Unable to get server id.");
        }

        if(!$psr16->has("server_" . $serverId)){
            $psr16->set("server_" . $serverId, getInfo($serverIp[0], $serverIp[1]), 5 * 60);
        }

        $info = $psr16->get("server_" . $serverId);
        $error = false;
    } catch (\Exception $e) {
        $error = true;
    }
}else{
    $info = null;
    $error = false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Minecraft Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        html,
        body {
            height: 100%
        }
    </style>
</head>
<body class="bg-secondary">
    <div class="h-100 d-flex align-items-center justify-content-center">
        <div class="container text-center bg-light p-5 border-secondary rounded-3">
            <h2>Minecraft Status</h2>
            <form method="POST" action="#">
                <label for="server" class="form-label">Server IP</label>
                <input type="text" class="form-control" id="server" name="server" aria-describedby="ipHelp" placeholder="Exemple: localserver:19132" <?php if(isset($_POST['server'])) {?>value="<?= $_POST['server'] ?>"<?php } else {?> value="localserver:19132" <?php } ?>>
                <div id="ipHelp" class="form-text">Enter the IP of the server you want to check.</div>
                <button type="submit" class="btn btn-primary mt-3" id="submit">Check</button>
            </form>

            <br>
            <?php
            if($error){
                echo '<div class="alert alert-danger mt-3" role="alert">An error occurred while checking the server.</div>';
            }elseif($info === null){

            }else{ ?>
                <br>
                <div class="container d-flex justify-content-center">
                    <table class="table table-striped w-50">
                        <tbody>
                            <tr>
                                <th scope="row">MOTD</th>
                                <td class="text-start"><?= $info["motd"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Game type</th>
                                <td class="text-start"><?= $info["gametype"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Game ID</th>
                                <td class="text-start"><?= $info["game_id"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Version</th>
                                <td class="text-start"><?= $info["version"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Server engine</th>
                                <td class="text-start"><?= $info["server_engine"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Plugins</th>
                                <td class="text-start"><?= $info["plugins"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Map name</th>
                                <td class="text-start"><?= $info["map"] ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Whitelist</th>
                                <td class="text-start">
                                    <?php
                                    if($info["whitelist"] == "on") {
                                        echo '<span class="badge text-bg-success">Enabled</span>';
                                    }else{
                                        echo '<span class="badge text-bg-danger">Disabled</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Players</th>
                                <td class="text-start">
                                    <?= $info["numplayers"] ?> / <?= $info["maxplayers"] ?><br>
                                    <?php
                                    foreach ($info["players"] as $player){
                                        echo $player . "<br>";
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
