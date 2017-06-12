<?php
require_once('./vendor/LINEBotTiny.php');
require_once('./config.php');
$channelAccessToken = $CONF['AMPERBOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['AMPERBOT_CHANNEL_SECRET'];


$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    if($message['text'][0] == '!') {
                        $client->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => 'I used to do commands, but now I just send things automatically ;)'
                                     //'text' => shell_exec("php sheetclient.php \\" . $message['text'])
                                ]
                            ]
                        ]);
                    }
                    break;
                default:
                    break;
            }
            break;
        case 'join':
            $source = $event['source'];
            error_log('joined somewhere, type: '.$source['type'].', groupId:'.$source['groupId'].'');
            break;
        default:
            break;
    }
};