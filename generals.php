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
		//echo("File saved.");
		return true;
	}else{
	    //echo("Error while saving the database.");
		return false;
	}
}

?>

