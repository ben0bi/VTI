<?php
include("../generals.php");

$server = "http://shop.masterbit.net/phone/";

// get function
$func='inv';
if(isset($_GET['func']))
	$func=$_GET['func'];

// put xml header to the phone.
if($func!="ledwait")
	echo('<?xml version="1.0" encoding="ISO-8859-1"?>');

// get the function and do something associated to it.
$header = true;
switch($func)
{
	case "inventory": // show inventory
	case "inv": showInventory(); break;
	// sell inventory
	case "si":
	case "si1":
	case "sellinv": sellInventory_MENU(); break;
	case "si2": sellInventory(); break;
	// inventory error status.
	case "inverr": invErr(); break;
	// inventory status.
	case "invstatus": invStatus(); break;
	case "deckels": // show all deckels
	case "dek": showDeckels(); break;
	case "deckel": // show single deckel
	case "sdk": showSingleDeckel(); break;
	case "createDeckel": // create a deckel.
	case "cdk": createDeckel_INPUT_Name(); break;
	case "cdk2": createDeckel_INPUT_Summe(); break;
	case "cdk3": createDeckel(); break;
	case "dkstatus": dkstatus(); break; // show a deckel status.
	case "dkerr": status("!INTERNER FEHLER!: Deckel wurde nicht gespeichert.", true);
	case "ledwait":
		$t = 5;
		if(isset($_GET["time"]))
			$t = intval($_GET["time"]);
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
	if(isset($_GET["projectid"]))
		$projectid=intval($_GET["projectid"]);

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

// show the menu to sell an inventory item.
function sellInventory_MENU()
{
	global $server;

	$items=[];
	$json=getJSONFile("../DB/db_inventory.gml");
	$items=$json["INVENTORY"];

	$itemid=-1;
	if(isset($_GET["inventoryid"]))
		$itemid=intval($_GET["inventoryid"]);

	if($itemid==-1)
	{
		status("INTERNER FEHLER: Inventar Item wurde nicht definiert.", true);
		return;
	}

	// get the item with the given id.
	$item = -1;
	for($i=0;$i<sizeof($items);$i++)
	{
		$itm=$items[$i];
		if(intval($itm["ID"]) == $itemid)
		{
			$item = $itm;
			break;
		}
	}

	if($item==-1)
	{
		status("Item #$itemid nicht gefunden.", true);
		return;
	}

	$name = $item["NAME"];
	$price = $item["PRICE"];
	$projectid = intval($item["PROJECTID"]);

	// good, we got the item, now send the menu...
	echo('<YealinkIPPhoneInputScreen Timeout="0" destroyOnExit="yes" Beep="no" type="number" LockIn="no" cancelAction="'.$server.'phone.php?func=inv&projectid='.$projectid.'">');
	echo('<Title>Verkaufe '.$name.'...</Title>');
	echo('<URL>'.$server.'phone.php?func=si2&itemid='.$itemid.'</URL>');

	echo('<InputField>');
	echo('<Prompt>Anzahl:</Prompt>');
	echo('<Parameter>amount</Parameter>');
	echo('<Default>1</Default>');
	echo('<Selection>1</Selection>');
	echo('</InputField>');

	echo('<InputField>');
	echo('<Prompt>Stückpreis:</Prompt>');
	echo('<Parameter>price</Parameter>');
	echo('<Default>'.$price.'</Default>');
	echo('<Selection>2</Selection>');
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

	echo('<SoftKey index="4">');
	echo('<Label>Abbrechen</Label>');
	echo('<URI>'.$server.'phone.php?func=inv&projectid='.$projectid.'</URI>');
	echo('</SoftKey>');

	echo('<SoftKey index="6">');
	echo('<Label>!!SENDEN!!</Label>');
	echo('<URI>SoftKey:Submit</URI>');
	echo('</SoftKey>');

	echo('</YealinkIPPhoneInputScreen>');
}

// sell inventory items
function sellInventory()
{
	global $server;
	$error=-1;
	$projectID = -1;

	$itemid = -1;
	if(isset($_GET["itemid"]))
		$itemid=intval($_GET["itemid"]);

	if($itemid<=-1)
		$error = 1;

	$amount=0;
	if(isset($_GET["amount"]))
		$amount=intval($_GET["amount"]);

	if($amount<=0)
		$error = 2;

	$price=0.0;
	if(isset($_GET["price"]))
		$price=intval($_GET["price"]);

	$name = "Unbekanntes Produkt";

	// no error 'till now, continue...
	if($error==-1)
	{
		$whichtable="INVENTORY";
		$json=getJSONFile("../DB/db_inventory.gml");

		// get the right item index and set its new values.
		//$idx=-1;
		for($i = 0;$i < sizeof($json[$whichtable]); $i++)
		{
			if($json[$whichtable][$i]["ID"]==$itemid)
			{
				// item found, do something.
				$name=$json[$whichtable][$i]["NAME"];
				$projectID = $json[$whichtable][$i]["PROJECTID"];
				$amt=intval($json[$whichtable][$i]["AMOUNT"]);

				if($amt-$amount>=0)
				{
					$json[$whichtable][$i]["AMOUNT"] = $amt - $amount;

					// save the inventory.
					if(saveJsonData("../DB/db_inventory.gml", $whichtable, $json))
					{
						// create a new transaction for that one.
						$whichtable2="TRANSACTIONS";
						$json2=getJSONFile("../DB/db_transactions.gml");

						$nen=[];

						// set transaction variables.
						$nen["DESC"]="$amount Stück $name aus dem Inventar entfernt für $price/Stück.";
						$nen["PROJECTID"]=$projectID;
						$nen["REIN"]=floatval($amount*$price);
						$nen["RAUS"]=0.0;
						$nen["DATE"]=date(DATE_RSS);
						$nen["LINK"]="";

						// set a new id.
						$nen["ID"] = get_Next_DBID($json2, $whichtable2);

						$json2[$whichtable2][] = $nen;
						if(!saveJsonData("../DB/db_transactions.gml", $whichtable2, $json2))
							$error = 3;
					}else{
						$error = 4;
					}
				}else{
					$error = 5;
				}
				break;
			}
		}
	}

	echo('<YealinkIPPhoneExecute Beep="yes">');
	if($error==-1)
	{
		// now put the stuff to the phone:

// Audio play does not seem to work. Maybe fiddle with the wavs. Turn off the beep then.

//		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/deckelcreated.wav"/>');
		echo('<ExecuteItem URI="Led:POWER=slowflash"/>');
		echo('<ExecuteItem URI="Led:LINE4_GREEN=on"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=invstatus&amount='.$amount.'&name='.$name.'"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=inv&projectid='.$projectID.'"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=ledwait&time=7"/>');
	}else{
//		echo('<ExecuteItem URI="Wav.Play:'.$server.'audio/error.wav"/>');
		echo('<ExecuteItem URI="Led:POWER=fastflash"/>');
		echo('<ExecuteItem URI="Led:LINE4_RED=fastflash"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=inverr&errid='.$error.'"/>');
		echo('<ExecuteItem URI="'.$server.'phone.php?func=ledwait&time=7"/>');
	}
	echo('</YealinkIPPhoneExecute>');
}

// inventory status function.
function invStatus()
{
	$name="";
	if(isset($_GET["name"]))
		$name=$_GET["name"];

	$amount=0;
	if(isset($_GET["amount"]))
		$amount=$_GET["amount"];

	status($amount.' Stück '.$name.' verkauft!', false);
}

// inventory error status function.
function invErr()
{
	$which=-1;
	if(isset($_GET["errid"]))
		$which=intval($_GET["errid"]);

	switch($which)
	{
		case 1: status("INTERNER FEHLER: Item nicht gefunden.",true); break;
		case 2: status("Anzahl <= 0: Kein Verkauf!",true); break;
		case 3: status("Interner Fehler: Inventar gespeichert aber keine Transaktion dazu.",true); break;
		case 4: status("Interner Fehler: Inventar nicht gespeichert.",true); break;
		case 5: status("KEIN VERKAUF: Es sind nicht soviele Einheiten im Inventar!",true); break;
		default:
			status("Undefinierter Fehler im Inventar!",true); break;
	}
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
	if(isset($_GET["name"]))
		$name=$_GET["name"];

	$produkt="";
	if(isset($_GET["produkt"]))
		$produkt=$_GET["produkt"];

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
	global $server;

	// project id = nachtshop
	$projectID = 12;

	$name="";
	if(isset($_GET['name']))
		$name=$_GET['name'];

	$produkt="";
	if(isset($_GET['produkt']))
		$produkt=$_GET['produkt'];

	echo('<YealinkIPPhoneInputScreen Timeout="0" destroyOnExit="yes" Beep="no" type="number" LockIn="no" cancelAction="'.$server.'phone.php?func=dek">');
	echo('<Title>Deckel für '.$name.' ('.$produkt.')</Title>');
	echo('<URL>'.$server.'phone.php?func=cdk3&name='.$name.'&produkt='.$produkt.'</URL>');

	echo('<InputField>');
	echo('<Prompt>Summe:</Prompt>');
	echo('<Parameter>summe</Parameter>');
	echo('<Default>1</Default>');
	echo('<Selection>1</Selection>');
	echo('</InputField>');

	echo('<InputField>');
	echo('<Prompt>Projekt-ID (siehe Website):</Prompt>');
	echo('<Parameter>projectid</Parameter>');
	echo('<Default>'.$projectID.'</Default>');
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
	if(isset($_GET["name"]))
		$name=$_GET["name"];

	$produkt="";
	if(isset($_GET["produkt"]))
		$produkt=$_GET["produkt"];

	$summe=0.0;
	if(isset($_GET["summe"]))
		$summe=$_GET["summe"];

	$projectID=0;
	if(isset($_GET["projectid"]))
		$projectID=$_GET["projectid"];

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

	status('Neuer Deckel für '.$name.': '.$summe.' für '.$produkt, false);
}

// show a status text on the phone.
function status($text, $beep=false)
{
	if(!$beep)
		$beep="no";
	else
		$beep="yes";

	echo('<YealinkIPPhoneStatus Beep="'.$beep.'" SessionID="status" Timeout = "120" >');
	echo('<Message Icon="Message" Size="large" Align="center">');
	echo($text);
	echo('</Message>');
	echo('</YealinkIPPhoneStatus>');
}

// wait some time and then send turn of the LEDs.
function ledwait($waittime)
{
	sleep($waittime);
	echo('<?xml version="1.0" encoding="ISO-8859-1"?>');
	echo('<YealinkIPPhoneExecute Beep="no">');
	echo('<ExecuteItem URI="Led:POWER=off"/>');
	echo('<ExecuteItem URI="Led:LINE4_GREEN=off"/>');
	echo('<ExecuteItem URI="Led:LINE4_RED=off"/>');
	echo('<ExecuteItem URI="Led:LINE5_GREEN=off"/>');
	echo('<ExecuteItem URI="Led:LINE5_RED=off"/>');
	echo('</YealinkIPPhoneExecute>');
}

?>
