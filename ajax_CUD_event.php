<?php
 
// CUD - create, update or delete an item.

// new > 3.0.0: generic saving of the post "DATA" field instead of custom save for each table.
/*
	E.g.
	Old: (post)whichtable = transactions, id = x, name = bla, desc = blu
			and this would be extracted here line by line
	New: (post)whichtable = transactions, data = (id=1, name=bla, desc=blu)
			and all the data stuff will be saved "directly" without extraction.
*/
// new > 2.5.4: json saveing.

// CUD can be "create" ("update" is exactly the same) or "delete"
// if CUD is "create" and dbid > 0, the entry with id dbid will be updated.
$CUD=$_POST["CUD"];
// which table to update? also determines the filename.
$whichtable=$_POST["whichtable"];

$dbid = -1 // create a new entry if no id is given.
// direct id
if(isset($_POST["ID"]))	{$dbid = $_POST["ID"];}
// id is in data chunk.
if(isset($_POST["DATA"]))
{
	if(isset($_POST["DATA"]["ID"])
		$dbid = $_POST["ID"];
	// SET dbid in the entry AFTER getting the data chunk for ommiting this id here.
}

echo("CUD: $CUD $whichtable");

// new: splitted all dbs into several files.
$datafile = 'DB/db_'.strtolower($whichtable).'.gml';//'database.gml';

// Read JSON file
$json = file_get_contents($datafile);

//Decode JSON
$json_data = [];
$json_data = json_decode($json,true);

//echo("OS: $json ".sizeof($json_data['EVENTS']));

// maybe create a new data chunk.
// 3.0.0 code
if(sizeof($json_data[$whichtable])<=0)
{
	$json_data[$whichtable]=[];
}

// old code
/*
if(sizeof($json_data["TRANSACTIONS"])<=0)
{
	$json_data["TRANSACTIONS"]=[];
}

if(sizeof($json_data["PROJECTS"])<=0)
{
	$json_data["PROJECTS"]=[];
}

if(sizeof($json_data["DECKELS"])<=0)
{
	$json_data["DECKELS"]=[];
}

if(sizeof($json_data["INVENTORY"])<=0)
{
	$json_data["INVENTORY"]=[];
}
endof old code */

// get the next unique id.
// (highest id + 1: BE AWARE OF DEPENDENCIES IF YOU DELETE THE LAST ENTRY AND CREATE A NEW ONE.)
function get_Next_DBID()
{
	global $json_data;
	global $whichtable;
	$id=0;
	$q=0;
	foreach($json_data[$whichtable] as $e)
	{
		$q++;
		$i=intval($e["ID"]);
		if($i>=$id)
			$id=$i+1;
	}
	echo("Next Transaction DB ID: $id");
	return $id;
}

// save the json data.
function saveJsonData()
{
	// new: save the table in its own file.
	global $json_data;
	global $datafile;
	global $whichtable;
	
	// create an empty db and then the table.
	$j = [];
	$j[$whichtable] = $json_data[$whichtable];
	// copy the gmls lines. This one is the ONLY one which GML uses internally,
	// ALL the other ones can be custom defined. GMLs are dependencies.
	$j["GMLS"]=$json_data["GMLS"];
	
	$jdata = json_encode($j);
	if(file_put_contents($datafile, $jdata))
	{
		echo("File saved.");
	}else{
	    echo("Error while saving the database.");
	}
}

// create or update an entry.
if($CUD=='create' || $CUD=='update')
{
	$nen = [];

	// search for the given id
	$idx = -1;	// the real index.
	for($i=0;$i<sizeof($json_data[$whichtable]);$i++)
	{
		if(intval($json_data[$whichtable][$i]["ID"])==$dbid)
		{
			$idx=$i;
			break;
		}
	}

	// create or update an entry.
	// 3.0.0 code: generic data
	$nen = $_POST["DATA"];
	// old code
	/*
	switch($whichtable)
	{
		case "TRANSACTIONS":
			$nen["PROJECTID"]=$_POST["PROJECTID"];
			$nen["DESC"]=$_POST["DESC"];
			$nen["LINK"]=$_POST["LINK"];
			$nen["REIN"]=$_POST["REIN"];
			$nen["RAUS"]=$_POST["RAUS"];
			$nen["DATE"]=$_POST["DATE"];
// NOT USED			$nen["COMBI"]=$_POST["COMBI"];
			break;
		case "PROJECTS":
			$nen["NAME"]=$_POST["NAME"];
			$nen["LINK"]=$_POST["LINK"];
			$nen["DESC"]=$_POST["DESC"];
			$nen["DATE"]=$_POST["DATE"];
			break;
		case "DECKELS":
			$nen["NAME"]=$_POST["NAME"];
			$nen["PRODUKT"]=$_POST["PRODUKT"];
			$nen["PROJECTID"]=$_POST["PROJECTID"];
			$nen["SUMME"]=$_POST["SUMME"];
			$nen["DATE"]=$_POST["DATE"];
			break;
		case "INVENTORY":
			$nen["NAME"]=$_POST["NAME"];
			$nen["DESC"]=$_POST["DESC"];
			$nen["PROJECTID"]=$_POST["PROJECTID"];
			$nen["PRICE"]=$_POST["PRICE"];
			$nen["AMOUNT"]=$_POST["AMOUNT"];
			break;
// NOT USED		case "COMBINATORS":
//			$nen["PROJECTID"]=$_POST["PROJECTID"];
//			$nen["NAME"]=$_POST["NAME"];
//			break;
		default:
			break;
	}
endof old code */
	if($dbid==-1)
	{
		// set a new id.
		$nen["ID"] = get_Next_DBID();
		// add the entry
		$json_data[$whichtable][] = $nen;
	}else{
		// we found the entry, change it.
		if($idx>=0)
		{
			$nen["ID"] = $dbid;
			$json_data[$whichtable][$idx] = $nen;
			echo("DONE");
		}else{
			echo("Entry with ID $dbid not found.");
		}
	}
	// save the data.
	saveJsonData();
}

// delete an entry.
if($CUD=='delete')
{
//	$dbid=$_POST["ID"];
	if($dbid>=0)
	{
		$n=[];
		// copy all except the one to delete.
		foreach($json_data[$whichtable] as $itm)
		{
			if($itm["ID"]!=$dbid)
				$n[] = $itm;
		}
		$json_data[$whichtable] = $n;
		saveJsonData();
	}else{
		echo (" Delete failed: DBID < 0 [$dbid]");
	}
	echo(" DB deletion done.");
}

//echo "Order: $order Title: $title <br />Blogtitle: $blogtitle<br />BlogText: $blogtext<br />";
?>
