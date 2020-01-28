<?php
$server = "http://shop.masterbit.net/phone/";

// get function
$func='inv';
if(isset($_GET['func']))
	$func=$_GET['func'];

// put xml header to the phone.
echo('<?xml version="1.0" encoding="ISO-8859-1"?>');

switch($func)
{
	case "inventory": // show inventory
	case "inv": showInventory(); break;
	case "deckels": // show all deckels
	case "dek": showDeckels(); break;
	case "deckel": // show single deckel
	case "sdk": showSingleDeckel(); break;
	case "createDeckel":
	case "cdk": createDeckel_INPUT(); break;
	default:
		break;
}

// show inventory items, how many and their price.
function showInventory()
{
	$items=[];
	$json=getJSONArray("../DB/db_inventory.gml");
	$items=$json["INVENTORY"];

	// show the whole inventory
	$projectid = -1;
	// get the project id to show the inventory from.
	if(isset($_GET['projectid']))
		$projectid=intval($_GET['projectid']);


	// just show a text screen.
	echo('<YealinkIPPhoneFormattedTextScreen destroyOnExit="yes" Beep="no" Timeout="30" LockIn="yes">');
	echo('<Line Size="large" Align="center">Shop Inventar</Line>');

	echo('<Scroll>');


	$all=0;
	$prod=0;
	$deck=[];
	$deckn=[];
	for($i=0;$i<sizeof($items);$i++)
	{
		$in = $items[$i];
		if($projectid<0 || $projectid==intval($in['PROJECTID']))
		{
			// show inventory
			$amt = $in['AMOUNT'];
			$a="left";
			$e = "";
			if(intval($amt)<=0)
			{
				$amt="# !!! 0x";
				$a="right";
				$e = " !!! # *";
			}else{
				$amt="* ".$amt."x";
			}
			echo('<Line Size="normal" Align="'.$a.'">');
			echo($amt.$in['PRICE'].'$ '.$in['NAME'].$e);
			if($projectid<0)
				echo(' [P'.$in['PROJECTID'].']');
			$all+=$in['AMOUNT'];
			echo('</Line>');
			$prod+=1;
		}
	}

	// show there are no products.
	if($prod<=0)
	{
		echo('<Line Size="large" Align="center" Color="red">Keine Produkte gefunden!</Line>');
		echo('<Line Size="small" Align="center">(Projekt ID '.$projectid.')</Line>');
	}

	echo('</Scroll>');

	// show bottom line
	echo('<Line Size="small" Align="right">Gesamt: '.$all.' Einheiten / '.$prod.' Produkte</Line>');

	// soft keys
	echo('<SoftKey index="1">');
	echo('<Label>Zurück</Label>');
	echo('<URI>SoftKey:Exit</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneFormattedTextScreen>');
}

// show deckels combined for each name.
function showDeckels()
{
	// WARNING: max 30 Menu Items.

	global $server;
	$items=[];
	$json=getJSONArray("../DB/db_deckels.gml");
	$items=$json["DECKELS"];

	echo('<YealinkIPPhoneTextMenu destroyOnExit="no" Beep="no" Timeout="30" LockIn="yes">');

	$all=0;
	$prod=0;
	$deck=[];
	$deckn=[];
	$deckid=[];	// id of the first occuring deckel.
	// combine the deckels by name
	for($i=0;$i<sizeof($items);$i++)
	{
		$in = $items[$i];
		$n = strtolower($in['NAME']);
		if(!isset($deck[$n]))
			$deck[$n]=0;
		$deck[$n]+=$in['SUMME'];
		$deckn[$n]=$in['NAME'];
		if(!isset($deckid[$n]))
			$deckid[$n]=$in['ID'];
		$all+=$in['SUMME'];
		$prod+=1;
	}

	// show the combined results.
	$adn = array_values($deckn);
	$ad= array_values($deck);
	$aid=array_values($deckid);

	for($i=0;$i<sizeof($ad);$i++)
	{
		echo('<MenuItem>');
		echo('<Prompt>'.$adn[$i].' = '.$ad[$i].' CHF</Prompt>');
		echo('<URI>'.$server.'phone.php?func=sdk&deckelid='.$aid[$i].'</URI>');
		echo('</MenuItem>');
	}
//	if(sizeof($ad)<=0)
//	{
//		echo('<Line Size="large" Align="center">Der Deckel ist blank!</Line>');
//	}

	echo('<Title wrap="yes" Style="numbered">Deckel ('.$all.' CHF / '.sizeof($deck).' Einträge)</Title>');

	// softkeys
	echo('<SoftKey index="1">');
	echo('<Label>Zurück</Label>');
	echo('<URI>SoftKey:Exit</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="4">');
	echo('<Label>Details</Label>');
	echo('<URI>SoftKey:Select</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneTextMenu>');
}

// show all deckels which have the same name as the given deckel.
function showSingleDeckel()
{
	// WARNING: Max 30 Menu Items!
	global $server;

	// show the whole inventory
	$deckelid = -1;
	// get the project id to show the inventory from.
	if(isset($_GET['deckelid']))
		$deckelid=intval($_GET['deckelid']);

	$items=[];
	$json=getJSONArray("../DB/db_deckels.gml");
	$items=$json["DECKELS"];

	// get the deckel with that id.
	$deckel = [];
	$name="";
	for($i=0;$i<sizeof($items);$i++)
	{
		$in = $items[$i];
		if(intval($in['ID'])==$deckelid)
			$name=$in['NAME'];
	}

	// show text display.
	echo('<YealinkIPPhoneFormattedTextScreen cancelAction="'.$server.'phone.php?func=dek" destroyOnExit="yes" Beep="no" Timeout="30" LockIn="yes">');

	echo('<Line Size="large" Align="center">Deckel von '.$name.'</Line>');

	echo('<Scroll>');

	$all=0;
	$prod=0;
	// combine the deckels by name
	for($i=0;$i<sizeof($items);$i++)
	{
		$in = $items[$i];
		$n = strtolower($in['NAME']);
		if($n==strtolower($name))
		{
			$all+=$in['SUMME'];
			$prod+=1;
			echo('<Line Size="normal" Align="left">* '.$in['PRODUKT'].' = '.$in['SUMME'].' CHF</Line>');
		}
	}

	if($prod<=0)
	{
		echo('<Line Size="large" Align="center">Der Deckel ist blank!</Line>');
	}

	echo('</Scroll>');

	// show bottom line
	echo('<Line Size="small" Align="right">Gesamt: '.$all.' CHF / '.$prod.' Einträge</Line>');

	echo('<SoftKey index="1">');
	echo('<Label>Zurück</Label>');
	echo('<URI>SoftKey:Exit</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneFormattedTextScreen>');
}

// create the input xml for creating a deckel directly from the phone.
function createDeckel_INPUT()
{
	echo('');
}

// Retrieve a file as JSON Array.
function getJSONArray($datafilename)
{
	$jdata = file_get_contents($datafilename);
	$json = json_decode($jdata, true);
	return $json;
}

?>
