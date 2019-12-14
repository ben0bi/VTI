<?php

 // CUD - create, update or delete an item.

// new > 2.5.4: json saveing.

$CUD=$_POST["CUD"];
$whichtable=$_POST["whichtable"];
$dbid=-1; // set this for delete or update.

echo("CUD: $CUD $whichtable");

$datafile = 'database.gml';

// Read JSON file
$json = file_get_contents($datafile);

//Decode JSON
$json_data = [];
$json_data = json_decode($json,true);

//echo("OS: $json ".sizeof($json_data['EVENTS']));

// maybe create a new data chunk.
if(sizeof($json_data["TRANSACTIONS"])<=0)
{
	$json_data["TRANSACTIONS"]=[];
}

if(sizeof($json_data["PROJECTS"])<=0)
{
	$json_data["PROJECTS"]=[];
}

// get the next unique id.
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
	global $json_data;
	global $datafile;
	$jdata = json_encode($json_data);
	if(file_put_contents($datafile, $jdata))
	{
		echo("File saved.");
	}else{
	    echo "Error while saving the database.";
	}
}

$idx = -1;	// the real index.
// search for the given id
for($i=0;$i<sizeof($json_data[$whichtable]);$i++)
{
	if(intval($json_data[$whichtable][$i]["ID"])==$dbid)
	{
		$idx=$i;
		break;
	}
}

// create or update an entry.
if($CUD=='create')
{
	$nen = [];
	// create an entry..
	switch($whichtable)
	{
		case "TRANSACTIONS":
			$nen["PROJECTID"]=$_POST["PROJECTID"];
			$nen["DESC"]=$_POST["DESC"];
			$nen["LINK"]=$_POST["LINK"];
			$nen["REIN"]=$_POST["REIN"];
			$nen["RAUS"]=$_POST["RAUS"];
			$nen["DATE"]=$_POST["DATE"];
			break;
		case "PROJECTS":
			$nen["NAME"]=$_POST["NAME"];
			$nen["LINK"]=$_POST["LINK"];
			$nen["DESC"]=$_POST["DESC"];
			$nen["DATE"]=$_POST["DATE"];
			break;
		default:
			break;
	}

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
	$dbid=$_POST["ID"];
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
