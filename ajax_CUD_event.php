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

include("generals.php");

// CUD can be "create" ("update" is exactly the same) or "delete"
// if CUD is "create" and dbid > 0, the entry with id dbid will be updated.
$CUD=$_POST["CUD"];
// which table to update? also determines the filename.
$whichtable=$_POST["whichtable"];

$dbid = -1; // create a new entry if no id is given.
// direct id
if(isset($_POST["ID"]))	{$dbid = $_POST["ID"];}
// id is in data chunk.
if(isset($_POST["DATA"]))
{
	if(isset($_POST["DATA"]["ID"]))
		$dbid = $_POST["DATA"]["ID"];
	// SET dbid in the entry AFTER getting the data chunk for ommiting this id here.
}

echo("CUD: $CUD $dbid $whichtable");

// new: splitted all dbs into several files.
$datafile = 'DB/db_'.strtolower($whichtable).'.gml';//'database.gml';
$json_data = getJSONFile($datafile);

// maybe create a new data chunk.
// 3.0.0 code
if(sizeof($json_data[$whichtable])<=0)
{
	$json_data[$whichtable]=[];
}

// create or update an entry.
if($CUD=='create' || $CUD=='update')
{
	$nen = [];

	// create or update an entry.
	// 3.0.0 code: generic data
	$nen = $_POST["DATA"];

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

	if($dbid>=0)
	{
		// we found the entry, change it.
		if($idx>=0)
		{
			$nen["ID"] = $dbid;
			$json_data[$whichtable][$idx] = $nen;
			echo("DONE");
		}else{
			echo("Entry with ID $dbid not found.");
		}
	}else{
		// set a new id.
		$nen["ID"] = get_Next_DBID($json_data, $whichtable);
		// add the entry
		$json_data[$whichtable][] = $nen;
	}
	// save the data.
	saveJsonData($datafile, $whichtable, $json_data);
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
		saveJsonData($datafile, $whichtable, $json_data);
	}else{
		echo (" Delete failed: DBID < 0 [$dbid]");
	}
	echo(" DB deletion done.");
}

//echo "Order: $order Title: $title <br />Blogtitle: $blogtitle<br />BlogText: $blogtext<br />";
?>
