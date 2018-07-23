<?php

require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

use Carbon\Carbon;
$channelAccessToken = $CONF['AMPERBOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['AMPERBOT_CHANNEL_SECRET'];

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

function sendMessage($destination, $msg) {
	global $client;
	$message = ['to' =>$destination, 'messages' => [['type' => 'text', 'text' => $msg]]];
	$client->pushMessage($message);
}

$messageText = "";

if(php_sapi_name() == 'cli' && $argv[1] == 'pve') {
	$end = shell_exec("php " . __DIR__ . "/sheetclient.php !pveend");
	$endDate = Carbon::createFromFormat('d/m/Y h:i:s', trim($end) . ' 06:00:00', new DateTimeZone('UTC'));

	$diffInHours = $endDate->diffInHours(null/*diff against now*/, false/*give negatives when diff is negative*/);
	$roomId = "";

	if( $diffInHours == -36)
	{
		$messageText = shell_exec("php " . __DIR__ . "/sheetclient.php !pvecall"); //36h: time for the call
		$roomId = $CONF['PVE_ROOM_ID'];
	}
	else if ($diffInHours > -36 && $diffInHours < -12)
	{
		$messageText = shell_exec("php " . __DIR__ . "/sheetclient.php !pvelist"); //inside 36h, outside 12h, publish the tally
		$messageText = $messageText . "\n\nMove list will be published about " . $endDate->subHours(12)->diffForHumans() . '.'
									. "\nUpdate your score whenever you want!";
		$roomId = $CONF['PVE_ROOM_ID'];

		if($diffInHours == -15)
		{
			$nagMessageText = "PVE move call in 3 hours - time to get those scores in!!";
			sendMessage( $CONF['ALPHA_ROOM_ID'], $nagMessageText);
			sendMessage( $CONF['BETA_ROOM_ID'], $nagMessageText);
			sendMessage( $CONF['GAMMA_ROOM_ID'], $nagMessageText);
		}
	}
	else if ($diffInHours == -12)
	{
		$messageText = shell_exec("php " . __DIR__ . "/sheetclient.php !pvemovelist"); //12h: time for the move list
		$roomId = $CONF['PVE_ROOM_ID'];
	}
	else if ($diffInHours == 3)
	{
		$messageText = "PVE rewards are all in (as of 3h ago).  Time to head home, everyone! :)";
		$roomId = $CONF['PVE_ROOM_ID'];
	}
	else if ($diffInHours == 12)
	{
		$messageText = "Commanders, time to clean up the spreadsheet and google forms responses for next pve :)";
		$roomId = $CONF['COMMANDER_ROOM_ID'];
	}

	if(!empty($messageText))
	{
		sendMessage($roomId, $messageText);
	}
}

if(php_sapi_name() == 'cli' && $argv[1] == 'pvpscreens') {
	//nag for screenshots
	$nagMessageText = 'Screenshots in the albums please folks :) (please ignore if you\'ve already put them in, I\'m not smart enough to know that)';
	sendMessage( $CONF['COMMANDER_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['ALPHA_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['BETA_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['GAMMA_ROOM_ID'], $nagMessageText);
}

if(php_sapi_name() == 'cli' && $argv[1] == 'pvplateclimbers') {
	//nag for screenshots
	$nagMessageText = 'PVP ends in 4 hours - late climbers get ready! :)';
	sendMessage( $CONF['ALPHA_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['BETA_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['GAMMA_ROOM_ID'], $nagMessageText);
	sendMessage( $CONF['LATECLIMBER_ROOM_ID'], $nagMessageText);
}
?>
