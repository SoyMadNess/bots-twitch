<?php
$server = 'irc.chat.twitch.tv';
$port = 6667;
$username = 'ccm'; // sin @
$oauth = 'oauth:chsyqwcr76onruektts2gqhjx6jbk7'; // Copia aquí tu token completo
$channel = '#soymadness1'; // Incluye el #

$socket = fsockopen($server, $port);
fputs($socket, "PASS $oauth\r\n");
fputs($socket, "NICK $username\r\n");
fputs($socket, "JOIN $channel\r\n");

echo "Bot conectado a $channel\n";

while (!feof($socket)) {
    $data = fgets($socket, 128);
    echo $data;

    if (strpos($data, 'PING') === 0) {
        fputs($socket, "PONG :tmi.twitch.tv\r\n");
        continue;
    }

    if (strpos($data, 'PRIVMSG') !== false && strpos($data, '!randomaccion') !== false) {
        preg_match('/:([^!]+)!/', $data, $matches);
        $user = $matches[1];

        $channelName = ltrim($channel, '#');
        $json = file_get_contents("https://tmi.twitch.tv/group/user/$channelName/chatters");
        $chatters = json_decode($json, true);

        $viewers = array_merge(
            $chatters['chatters']['viewers'] ?? [],
            $chatters['chatters']['vips'] ?? [],
            $chatters['chatters']['moderators'] ?? []
        );

        // Filtra al usuario que ejecutó el comando (opcional)
        $viewers = array_filter($viewers, fn($v) => $v !== $user);
        $viewers = array_values($viewers); // Reindexar

        if (count($viewers) < 3) {
            $message = "Muy poca gente en el chat para la acción.";
        } else {
            shuffle($viewers);
            $casado = $viewers[0];
            $cogido = $viewers[1];
            $matado = $viewers[2];

            $message = "$user se casó con $casado, se cogió a $cogido y mató a $matado";
        }

        fputs($socket, "PRIVMSG $channel :$message\r\n");
    }

    sleep(1);
}
fclose($socket);
