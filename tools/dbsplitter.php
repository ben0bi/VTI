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

function splitData($datatable, $newdatafile)
{
	global $json_data;
	$jdata = [];
	$jdata[$datatable] =$json_data[$datatable];
	$djdata=json_encode($jdata);
	if(file_put_contents($newdatafile, $djdata))
	{
		echo("File saved.");
	}else{
	    echo("Error while saving the database.");
	}
}

splitData('TRANSACTIONS', '../DB/db_transactions.gml');
splitData('PROJECTS', '../DB/db_projects.gml');
splitData('DECKELS', '../DB/db_deckels.gml');
splitData('INVENTORY','../DB/db_inventory.gml');

?>
