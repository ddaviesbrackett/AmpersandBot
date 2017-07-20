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
                    if(true /*isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['PVE_ROOM_ID']*/)
                    {
                        $matches = [];
                        if(preg_match('/(.* )?(\d+k) (s[12345\?])( update)?$/', $message['text'], $matches) == true)
                        {
                            $command = 'php ' . __DIR__ . '/sheetclient.php !pveupdate ';
                            $name = NULL;
                            if($matches[1] != '')
                            {
                                $name = $matches[1];
                            }
                            else if( isset($event['source']['userId']))
                            {
                                $profile = $client->profile($event['source']['userId']);
                                if (isset($profile['displayName'])) {
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
                                break;
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