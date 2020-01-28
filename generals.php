<?php
 
// CUD general functions.

// load a file and get its json as array.
function getJSONFile($datafile)
{
	// Read JSON file
	$json = file_get_contents($datafile);

	//Decode JSON
	$json_data = [];
	$json_data = json_decode($json,true);
	return $json_data;
}

// get the next unique id.
// (highest id + 1: BE AWARE OF DEPENDENCIES IF YOU DELETE THE LAST ENTRY AND CREATE A NEW ONE.)
function get_Next_DBID($json_data, $whichtable)
{
//	global $json_data;
//	global $whichtable;
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
function saveJsonData($datafile, $whichtable, $json_data)
{
	// new: save the table in its own file.
//	global $json_data;
//	global $datafile;
//	global $whichtable;

	// create an empty db and then the table.
	$j = [];
	$j[$whichtable] = $json_data[$whichtable];
	// copy the gmls lines. This one is the ONLY one which GML uses internally,
	// ALL the other ones can be custom defined. GMLs are dependencies.
	if(isset($json_data["GMLS"]))
		$j["GMLS"]=$json_data["GMLS"];

	$jdata = json_encode($j);
	if(file_put_contents($datafile, $jdata))
	{
		echo("File saved.");
	}else{
	    echo("Error while saving the database.");
	}
}


// maybe create a new data chunk.
// 3.0.0 code
if(sizeof($json_data[$whichtable])<=0)
{
	$json_data[$whichtable]=[];
}

// get the next unique id.
// (highest id + 1: BE AWARE OF DEPENDENCIES IF YOU DELETE THE LAST ENTRY AND CREATE A NEW ONE.)
function get_Next_DBID($json_data, $whichtable)
{
//	global $json_data;
//	global $whichtable;
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
function saveJsonData($datafile, $whichtable, $json_data)
{
	// new: save the table in its own file.
//	global $json_data;
//	global $datafile;
//	global $whichtable;

	// create an empty db and then the table.
	$j = [];
	$j[$whichtable] = $json_data[$whichtable];
	// copy the gmls lines. This one is the ONLY one which GML uses internally,
	// ALL the other ones can be custom defined. GMLs are dependencies.
	if(isset($json_data["GMLS"]))
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
