<?php

echo("MESSAGE: ".$_POST["MSG"]);
$msg=$_POST["MSG"];


function push2phone($server,$phone,$data) 
{

	$xml = "xml=".$data;
	$post = "POST / HTTP/1.1\r\n";
	$post .= "Host: $phone\r\n";
	$post .= "Referer: $server\r\n";
	$post .= "Connection: Keep-Alive\r\n";
	$post .= "Content-Type: text/xml\r\n";
	$post .= "Content-Length: ".strlen($xml)."\r\n\r\n";

	$fp = @fsockopen ( $phone, 80, $errno, $errstr, 5);

	if($fp)
	{
		fputs($fp, $post.$xml);
		echo("Message posted.");
	}else{
		echo("Nothing pushed, sorry.");
	}
}


$txt='<?xml version="1.0" encoding="ISO-8859-1"?>';
$txt.='<YealinkIPPhoneStatus Beep = "yes" Timeout = "30">';
$txt.='<Message Icon= "Message" Size="large" Align="center">';
$txt.=$msg;
$txt.='</Message>';
$txt.='</YealinkIPPhoneStatus>';


push2phone("Moria/Masterbit","192.168.0.10",$txt);
?>
