<?php

// CUD - create, update or delete an item.

// new > 2.5.4: json saveing.

$CUD=$_POST['CUD'];
$whichtable=$_POST['whichtable'];
$id = $_POST['id'];

echo("CUD: $CUD $whichtable");

$datafile = 'database.gml';

// Read JSON file
$json = file_get_contents($datafile);

//Decode JSON
$json_data = [];
$json_data = json_decode($json,true);

//echo("OS: $json ".sizeof($json_data['EVENTS']));

// maybe create a new data chunk.
if(sizeof($json_data['TRANSACTIONS'])<=0)
{
	$json_data['TRANSACTIONS']=[];
}

if(sizeof($json_data['PROJECTS'])<=0)
{
	$json_data['PROJECTS']=[];
}

// get the next unique id.
function get_Next_DBID()
{
	global $json_data;
	global $whichtable;
	$id=0;
	$q=0;
	foreach($json_data[$wichtable] as $e)
	{
		$q++;
		$i=intval($e['ID']);
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
	if(intval($json_data[$whichtable][$i]['ID'])==$dbid)
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
			$nen['DESC']=$_POST['DESC'];
			$nen['']=$_POST[''];
			break;
		case "PROJECTS":
			break;
		default:
			break;
	}

	if($dbid==-1)
	{
		// set a new id.
		$nen['ID'] = get_Next_DBID();
		// add the entry
		$json_data['EVENTS'][] = $nen;
	}else{
		// we found the entry, change it.
		if($idx>=0)
		{
			// maybe first delete the old audio file.
			deleteAudioFile($idx);
			// set new old id
			$nen['ID'] = $dbid;
			$json_data['EVENTS'][$idx] = $nen;
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
	if($dbid>=0)
	{
		deleteAudioFile($idx);
		$n=[];
		$n['EVENTS']=[];
		// copy all except the one to delete.
		foreach($json_data['EVENTS'] as $itm)
		{
			if($itm['ID']!=$dbid)
				$n['EVENTS'][] = $itm;
		}
		$json_data = $n;
		saveJsonData();
	}else{
		echo (" Delete failed: DBID < 0 [$dbid]");
	}
	echo(" DB deletion done.");
}

//echo "Order: $order Title: $title <br />Blogtitle: $blogtitle<br />BlogText: $blogtext<br />";
?>
