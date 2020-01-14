<?php
// split the one database.gml into several files.

$datafile = '../database.gml';

// Read JSON file
$json = file_get_contents($datafile);

//Decode JSON
$json_data = [];
$json_data = json_decode($json,true);

// maybe create a new data chunk.
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

function splitData($datatable)
{
	global $json_data;
	$jdata = [];
	$jdata[$datatable] =$json_data[$datatable];
	if($datatable!="PROJECTS")
	{
		$jdata["GMLS"] = [];
		$jdata["GMLS"][] = "db_projects.gml";
	}
	$newdatafile= "db_".strtolower($datatable).".gml";
	$djdata=json_encode($jdata);
	if(file_put_contents($newdatafile, $djdata))
	{
		echo("File saved.");
	}else{
	    echo("Error while saving the database.");
	}
}

splitData('TRANSACTIONS');
splitData('PROJECTS');
splitData('DECKELS');
splitData('INVENTORY');

?>
