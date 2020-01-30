<?php
include("../generals.php");

$server = "http://shop.masterbit.net/phone/";

// get function
$func='inv';
if(isset($_GET['func']))
	$func=$_GET['func'];

// put xml header to the phone.
echo('<?xml version="1.0" encoding="ISO-8859-1"?>');

// get the function and do something associated to it.
switch($func)
{
	case "inventory": // show inventory
	case "inv": showInventory(); break;
	case "deckels": // show all deckels
	case "dek": showDeckels(); break;
	case "deckel": // show single deckel
	case "sdk": showSingleDeckel(); break;
	case "createDeckel": // create a deckel.
	case "cdk": createDeckel_INPUT_Name(); break;
	case "cdk2": createDeckel_INPUT_Summe(); break;
	case "cdk3": createDeckel(); break;
	case "dkstatus": dkstatus(); break; // show a deckel status.
	case "dkerr": status("!INTERNER FEHLER!: Deckel wurde nicht gespeichert.");
	case "ledwait":
		$t = 5;
		if(isset($_GET['time']))
			$t = floatval($_GET['time']);
		// start the turn-off-leds-function.
		ledwait($t);
		break;
	default:break;
}

// show inventory items, how many and their price.
function showInventory()
{
	global $server;

	$items=[];
	$json=getJSONFile("../DB/db_inventory.gml");
	$items=$json["INVENTORY"];

	// show the whole inventory
	$projectid = -1;
	// get the project id to show the inventory from.
	if(isset($_GET['projectid']))
		$projectid=intval($_GET['projectid']);

	// just show a text screen.
	echo('<YealinkIPPhoneTextMenu destroyOnExit="yes" Beep="no" Timeout="42" LockIn="yes">');

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
				$amt="# !!! 0x ";
				$a="right";
				$e = " !!! # *";
			}else{
				$amt=" ".$amt."x ";
			}

			$all+=$in['AMOUNT'];

			echo('<MenuItem>');
			echo('<Prompt Align="'.$a.'">');
			echo($amt.$in["PRICE"].'$ '.$in['NAME'].$e);
			if($projectid<0)
				echo(' [P'.$in['PROJECTID'].']');
			echo('</Prompt>');
			echo('<URI>'.$server.'phone.php?func=sellinv&inventoryid='.$in["ID"].'</URI>');
			echo('</MenuItem>');

			$prod+=1;
		}
	}

	$txt='Shop Inventar ('.$all.' Einh. / '.$prod.' Prod.)';

	// show there are no products.
	if($prod<=0)
		$txt="Shop Inventar: Keine Produkte gefunden!";

	echo('<Title wrap="yes">'.$txt.'</Title>');

	// soft keys
//	echo('<SoftKey index="1">');
//	echo('<Label>Zurück</Label>');
//	echo('<URI>SoftKey:Exit</URI>');
//	echo('</SoftKey>');

	echo('</YealinkIPPhoneTextMenu>');
}

// show deckels combined for each name.
function showDeckels()
{
	// WARNING: max 30 Menu Items.
	global $server;

	$items=[];
	$json=getJSONFile("../DB/db_deckels.gml");
	$items=$json["DECKELS"];

	echo('<YealinkIPPhoneTextMenu destroyOnExit="yes" Beep="no" Timeout="42" LockIn="yes">');

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

	echo('<Title wrap="yes">Deckel ('.$all.' CHF / '.sizeof($deck).' Einträge)</Title>');

	// softkeys
	echo('<SoftKey index="1">');
	echo('<Label>Zurück</Label>');
	echo('<URI>SoftKey:Exit</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="2">');
	echo('<Label>+ NEU +</Label>');
	echo('<URI>'.$server.'phone.php?func=cdk</URI>');
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
	$json=getJSONFile("../DB/db_deckels.gml");
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
	echo('<YealinkIPPhoneFormattedTextScreen cancelAction="'.$server.'phone.php?func=dek" destroyOnExit="yes" Beep="no" Timeout="42" LockIn="yes">');

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
	echo('<URI>'.$server.'phone.php?func=dek</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="2">');
	echo('<Label>+ NEU +</Label>');
	echo('<URI>'.$server.'phone.php?func=cdk&name='.$name.'</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneFormattedTextScreen>');
}

// create the input xml for creating a deckel directly from the phone.
function createDeckel_INPUT_Name()
{
	$name="";
	if(isset($_GET['name']))
		$name=$_GET['name'];

	$produkt="";
	if(isset($_GET['produkt']))
		$produkt=$_GET['produkt'];

	global $server;
	echo('<YealinkIPPhoneInputScreen Timeout="0" destroyOnExit="yes" Beep="no" type="string" LockIn="no" cancelAction="'.$server.'phone.php?func=dek">');
	echo('<Title>Deckel erstellen... (Name/Produkt)</Title>');
	echo('<URL>'.$server.'phone.php?func=cdk2</URL>');

	echo('<InputField>');
	echo('<Prompt>Name:</Prompt>');
	echo('<Parameter>name</Parameter>');
	echo('<Default>'.$name.'</Default>');
	echo('<Selection>1</Selection>');
	echo('</InputField>');

	echo('<InputField>');
	echo('<Prompt>Produkt:</Prompt>');
	echo('<Parameter>produkt</Parameter>');
	echo('<Default>'.$produkt.'</Default>');
	echo('<Selection>2</Selection>');
	echo('</InputField>');

	// softkeys
	echo('<SoftKey index="1">');
	echo('<Label>DEL</Label>');
	echo('<URI>SoftKey:BackSpace</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="2">');
	echo('<Label>Abc</Label>');
	echo('<URI>SoftKey:ChangeMode</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="3">');
	echo('<Label>Weiter..</Label>');
	echo('<URI>SoftKey:Submit</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="6">');
	echo('<Label>.</Label>');
	echo('<URI>SoftKey:Dot</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="4">');
	echo('<Label>Abbrechen</Label>');
	echo('<URI>'.$server.'phone.php?func=dek</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneInputScreen>');
}

// create the input xml for creating a deckel directly from the phone.
// number part
function createDeckel_INPUT_Summe()
{
	$name="";
	if(isset($_GET['name']))
		$name=$_GET['name'];

	$produkt="";
	if(isset($_GET['produkt']))
		$produkt=$_GET['produkt'];

	global $server;
	echo('<YealinkIPPhoneInputScreen Timeout="0" destroyOnExit="yes" Beep="no" type="number" LockIn="no" cancelAction="'.$server.'phone.php?func=dek">');
	echo('<Title>Deckel für '.$name.' ('.$produkt.')</Title>');
	echo('<URL>'.$server.'phone.php?func=cdk3&name='.$name.'&produkt='.$produkt.'</URL>');

	echo('<InputField>');
	echo('<Prompt>Summe:</Prompt>');
	echo('<Parameter>summe</Parameter>');
	echo('<Default>1</Default>');
	echo('<Selection>1</Selection>');
	echo('</InputField>');

	// softkeys
	echo('<SoftKey index="1">');
	echo('<Label>DEL</Label>');
	echo('<URI>SoftKey:BackSpace</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="2">');
	echo('<Label>.</Label>');
	echo('<URI>SoftKey:Dot</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="3">');
	echo('<Label>Zurück..</Label>');
	echo('<URI>'.$server.'phone.php?func=cdk&name='.$name.'&produkt='.$produkt.'</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="4">');
	echo('<Label>Abbrechen</Label>');
	echo('<URI>'.$server.'phone.php?func=dek</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="6">');
	echo('<Label>!!SENDEN!!</Label>');
	echo('<URI>SoftKey:Submit</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneInputScreen>');
}

// now really create a deckel and show it on the phone.
function createDeckel()
{
	global $server;

	$whichtable="DECKELS";
	$datafile="../DB/db_deckels.gml";

	// load the json data.
	$json_data=getJSONFile($datafile);
	if(sizeof($json_data[$whichtable])<=0)
		$json_data[$whichtable]=[];

	$name="";
	if(isset($_GET['name']))
		$name=$_GET['name'];

	$produkt="";
	if(isset($_GET['produkt']))
		$produkt=$_GET['produkt'];

	$summe=0.0;
	if(isset($_GET['summe']))
		$summe=$_GET['summe'];

	// project id = nachtshop
	$projectID = 12;

	// create or update an entry.
	// 3.0.0 code: generic data

	$nen=[];

	// set deckel variables.
	$nen["NAME"]=$name;
	$nen["PRODUKT"]=$produkt;
	$nen["SUMME"]=floatval($summe);
	$nen["PROJECTID"]=$projectID;
	$nen["DATE"]=date(DATE_RSS);

	// set a new id.
	$nen["ID"] = get_Next_DBID($json_data, $whichtable);

	// add the entry
	$json_data[$whichtable][] = $nen;
	// save the data. returns false if something went wrong.
	echo('<YealinkIPPhoneExecute Beep="yes">');
	if(saveJsonData($datafile, $whichtable, $json_data))
	{
		// now put the stuff to the phone:

// Audio play does not seem to work. Maybe fiddle with the wavs. Turn off the beep then.

//		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/deckelcreated.wav"/>');
		echo('<ExecuteItem URI="Led:POWER=slowflash"/>');
		echo('<ExecuteItem URI="Led:LINE5_GREEN=on"/>');
		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/deckelcreated.wav"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=dek"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=dkstatus&name='.$name.'&summe='.$summe.'&produkt='.$produkt.'"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=ledwait&time=7"/>');
	}else{
//		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/error.wav"/>');
		echo('<ExecuteItem URI="Led:POWER=fastflash"/>');
		echo('<ExecuteItem URI="Led:LINE5_RED=fastflash"/>');
		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/error.wav"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=dkerr"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=ledwait&time=20"/>');
	}
	echo('</YealinkIPPhoneExecute>');
}

// create a deckel status message.
function dkstatus()
{
	$name="";
	if(isset($_GET['name']))
		$name=$_GET['name'];

	$produkt="";
	if(isset($_GET['produkt']))
		$produkt=$_GET['produkt'];

	$summe=0.0;
	if(isset($_GET['summe']))
		$summe=$_GET['summe'];

	status('Neuer Deckel für '.$name.': '.$summe.' für '.$produkt);
}

// show a status text on the phone.
function status($text)
{
	echo('<YealinkIPPhoneStatus Beep="no" SessionID="status" Timeout = "120" >');
	echo('<Message Icon="Message" Size="large" Align="center">');
	echo($text);
	echo('</Message>');
	echo('</YealinkIPPhoneStatus>');
}

// wait some time and then send turn of the LEDs.
function ledwait($waittime)
{
	sleep($waittime);
	echo('<YealinkIPPhoneExecute Beep="no">');
	echo('<ExecuteItem URI="Led:POWER=off"/>');
	echo('<ExecuteItem URI="Led:LINE5_GREEN=off"/>');
	echo('<ExecuteItem URI="Led:LINE5_RED=off"/>');
	echo('</YealinkIPPhoneExecute>');
}
?>
