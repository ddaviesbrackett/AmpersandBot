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
                    if(isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['PVE_ROOM_ID'])
                    {
                        $matches = [];
                        if(preg_match('/^(.* )?(\d+[Kk]) *((\+( +)?|plus )grind)? +(s[12345\?])$/', trim($message['text']), $matches) == true)
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
                                if (isset($profile->displayName)) {
                                    $name = $profile->displayName;
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

                            $score = mb_strtolower($matches[2]);
                            $slice = $matches[6];
                            $grind = $matches[3] != ""?"y":"n"; # 2                3               4                  5
                            $resp = shell_exec($command . ' "' . $name . '" "' . $score . '" "' . $slice . '" "' . $grind . '"');
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
            $source = $event['source'];
            if(isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['DEBUG_ROOM_ID'])
            {
                $profile = $client->profile($event['source']['userId']);
                error_log('got a message from user ID ' . $event['source']['userId'] . ', displayName '.$profile->displayName.' message '.$message['text']);

                $client->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => 'from user ID:'. $event['source']['userId'] . '
                                echoing: '.$message['text'] .'
                                displayName: '. $profile->displayName
                            ]
                        ]
                    ]);

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