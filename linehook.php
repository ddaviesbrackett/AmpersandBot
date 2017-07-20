<?php
require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/config.php');
$channelAccessToken = $CONF['AMPERBOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['AMPERBOT_CHANNEL_SECRET'];


$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    if(strpos($message['text'],'robo&:') == 0) {
                        $client->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => 'I used to do commands, but now I just send things automatically ;)'
                                ]
                            ]
                        ]);
                        if($message['text'] == 'robo&:debug') {
                            error_log('event details: ' . print_r($event, true));
                        }
                    }
                    if(true /*isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['PVE_ROOM_ID']*/)
                    {
                        var $matches = [];
                        if(preg_match('/(.* )?(\d+k) (s[12345\?])( update)?$/', $message['text'], $matches) === true)
                        {
                            $command = 'php ' . __DIR__ . '/sheetclient.php !pveupdate ';
                            $name = NULL;
                            if(isset($matches[1]))
                            {
                                $name = $matches[1];
                            }
                            else if( isset($event['source']['userId']))
                            {
                                $response = $client->getProfile($event['source']['userId']);
                                if ($response->isSucceeded()) {
                                    $profile = $response->getJSONDecodedBody();
                                    $name = $profile['displayName'];
                                }
                            }
                            if($name == '')
                            {
                                $client->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'Sorry, didn\'t catch that - I don\'t know who you are :( friend me and try again please, or put your name in the message'
                                    ]
                                ]
                            ]);
                            }

                            $score = $matches[2];
                            $slice = $matches[3];
                            $resp = shell_exec($command . ' ' . $name . ' ' . $score . ' ' . $slice);
                            $out = $resp == "got it" ? 'Score recorded, @' . $name : 'something went wrong, go find Serrated';
                            $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Score recorded, @' . $name
                                        ]
                                    ]
                                ]);
                        }
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