<?php

require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

use Carbon\Carbon;

if(php_sapi_name() == 'cli') {
	$end = shell_exec("php " . __DIR__ . "/sheetclient.php !pveend");
	$endDate = Carbon::createFromFormat('d/m/Y h:i:s', trim($end) . ' 06:00:00', new DateTimeZone('UTC'));

	$diffInHours = $endDate->diffInHours(null/*diff against now*/, false/*give negatives when diff is negative*/);
	$messageText = "";
	$roomId = "";

	if( $diffInHours == -36)
	{
		$messageText = shell_exec("php " . __DIR__ . "/sheetclient.php !pvecall"); //36h: time for the call
		$roomId = $CONF['PVE_ROOM_ID'];
	}
	else if ($diffInHours > -36 && $diffInHours < -12)
	{
		$messageText = shell_exec("php " . __DIR__ . "/sheetclient.php !pvelist"); //inside 36h, outside 12h, publish the tally
		$roomId = $CONF['PVE_ROOM_ID'];
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
		$channelAccessToken = $CONF['AMPERBOT_CHANNEL_ACCESS'];
		$channelSecret = $CONF['AMPERBOT_CHANNEL_SECRET'];

		$client = new LINEBotTiny($channelAccessToken, $channelSecret);


		$message = ['to' =>$roomId, 'messages' => [['type' => 'text', 'text' => $messageText]]];
		$client->pushMessage($message);
	}
}
?>
