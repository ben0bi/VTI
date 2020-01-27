<?php
// show the whole inventory
$projectid = -1;
// get the project id to show the inventory from.
if(isset($_GET['projectid']))
	$projectid=intval($_GET['projectid']);

$func='inv';
if(isset($_GET['func']))
	$func=$_GET['func'];

// open and convert the inventory file.
if($func=='inv')
	$datafile = "DB/db_inventory.gml";
if($func=='dek')
	$datafile="DB/db_deckels.gml";

$jdata = file_get_contents($datafile);
$json = json_decode($jdata, true);

// put xml header to the phone.
echo('<?xml version="1.0" encoding="ISO-8859-1"?>');

// just show a text screen.
echo('<YealinkIPPhoneFormattedTextScreen destroyOnExit="yes" Beep="no" Timeout="30" LockIn="yes">');

// title line.
$items=[];
if($func=='inv')
{
	echo('<Line Size="large" Align="center">Shop Inventar</Line>');
	$items=$json["INVENTORY"];
}
if($func=='dek')
{
	echo('<Line Size="large" Align="center">Bierdeckel Institut</Line>');
	$items=$json["DECKELS"];
}
// list items.
echo('<Scroll>');

$all=0;
$prod=0;
$deck=[];
$deckn=[];
for($i=0;$i<sizeof($items);$i++)
{
	$in = $items[$i];
	if($func=='dek' || $projectid<0 || $projectid==intval($in['PROJECTID']))
	{
		// show inventory
		if($func=='inv')
		{
			echo('<Line Size="normal" Align="left">');
			echo('* '.$in['AMOUNT'].'x '.$in['PRICE'].'$ '.$in['NAME']);
			if($projectid<0)
				echo(' [P'.$in['PROJECTID'].']');
			$all+=$in['AMOUNT'];
			echo('</Line>');
		}
		// or create deckels array
		if($func=='dek')
		{
			$n = strtolower($in['NAME']);
			if(!isset($deck[$n]))
				$deck[$n]=0;
			$deck[$n]+=$in['SUMME'];
			$deckn[$n]=$in['NAME'];
			$all+=$in['SUMME'];
		}
		$prod+=1;
	}
}

// show deckel summs
if($func=='dek')
{
	$adn = array_values($deckn);
	$ad= array_values($deck);
	for($i=0;$i<sizeof($ad);$i++)
	{
		echo('<Line Size="normal" Align="left">* '.$adn[$i].' = '.$ad[$i].' CHF</Line>');
	}
	if(sizeof($ad)<=0)
	{
		echo('<Line Size="large" Align="center">Der Deckel ist blank!</Line>');
	}
}

// show there are no products.
if($prod<=0 && $func=='inv')
{
	echo('<Line Size="large" Align="center" Color="red">Keine Produkte gefunden!</Line>');
	echo('<Line Size="small" Align="center">(Projekt ID '.$projectid.')</Line>');
}

echo('</Scroll>');

// show bottom line
if($func=='inv')
	echo('<Line Size="small" Align="right">Gesamt: '.$all.' Einheiten / '.$prod.' Produkte</Line>');
if($func=='dek')
	echo('<Line Size="small" Align="right">Gesamt: '.$all.' CHF / '.sizeof($deck).' Einträge</Line>');

echo('</YealinkIPPhoneFormattedTextScreen>');
?>
