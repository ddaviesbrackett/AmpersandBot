<?php
require_once('./vendor/LINEBotTiny.php');
require_once('./config.php');
$channelAccessToken = $CONF['AMPERBOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['AMPERBOT_CHANNEL_SECRET'];

$client = new LINEBotTiny($channelAccessToken, $channelSecret);


$message = ['to' => $argv[1], 'messages' => [['type' => 'image', 'originalContentUrl' => $argv[2], 'previewImageUrl' => $argv[2]]]];
$client->pushMessage($message);
