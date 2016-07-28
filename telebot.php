<?php

$token = trim(file_get_contents('token.txt'));
$url = 'https://api.telegram.org/bot';

$offset = -1;

while(true) {
    $updates = getUpdates();
    foreach ($updates->result as $update) {
        if (isset($update->message)) {
            processMessage($update->message);
        }
        $offset = max($offset, $update->update_id);
    }
    usleep(300000);
}

function sendPost($url, $data) {
    $options = array('http' => array(
            'header'  => "Content-type: application/json\r\nAccept: application/json\r\n",
            'method'  => 'POST',
            'content' => $data));
    $result = file_get_contents($url, false, stream_context_create($options));
    return $result;
}

function makeCall($method, $data = null) {
    global $url, $token;
    $fullUrl = "$url$token/$method";
    if ($data === null) {
        $response = file_get_contents($fullUrl);
    } else {
        $response = sendPost($fullUrl, $data);
    }
    return $response !== false ? json_decode($response) : false;
}

function getUpdates() {
    global $offset;
    $data = makeCall("getUpdates" . ($offset !== null ? '?offset=' . ($offset + 1) : ''));
    return $data;
}

function sendMessage($chatId, $text) {
    $res = makeCall('sendMessage', json_encode(array('chat_id'=>$chatId, 'text'=>$text)));
    echo "sending: $text - " . ($res ? 'ok' : 'fail') . "\n";
    return $res !== false;
}

function processMessage($message) {
    echo('got message: ' . $message->text . "\n");
    if ($message->text == '/start') {
        sendMessage($message->chat->id, 'Hello, I\'m ready!');
    } else if ($message->text == '/help') {
        sendMessage($message->chat->id, 'It is just a test bot!');
    } else {
        $text = iconv('utf-16be', 'utf-8', strrev(iconv('utf-8', 'utf-16le', $message->text)));
        sendMessage($message->chat->id, $text);
    }
}

