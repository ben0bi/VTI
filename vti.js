// VTI Volle Transparenz Initiative
// Shared functions.

var showTopBar =function(which)
{
	// 0 = all

	var txt="<div id='ubermenu'>";
	// 1 = transactions
	if(which==1)
		txt+="Transaktionen | ";
	else
		txt+='<a href="javascript:" onclick="loadTable(1)">Transaktionen</a> | ';

	// 2 = projects
	if(which==2)
		txt+="Projekte | ";
	else
		txt+='<a href="javascript:" onclick="loadTable(2)">Projekte</a> | ';

	// 3 = deckel
	if(which==3)
		txt+="Deckel/Futures | ";
	else
		txt+='<a href="javascript:" onclick="loadTable(4)">Deckel/Futures</a> | ';

	// 4 = inventar
	if(which==4)
		txt+="Inventar"
	else
		txt+='<a href="javascript:" onclick="loadTable(5)">Inventar</a>';

	txt+="</div>";
	return txt;
}

// a transaction for a specific project.
var Data_Transaction = function()
{
	var me = this;
	this.id = 0;
	this.desc = "Nothing";
	this.link = "";
	this.projectid = 0;
	this.in = 0.0;
	this.out = 0.0;
	this.datum = new Date();
	this.datum.setFullYear(1900);
	this.datum.setMonth(0);
	this.datum.setDate(1);
	this.parseGML = function(json, rootpath)
	{
		if(__defined(json['ID']))
			me.id = parseInt(json['ID']);
		if(__defined(json['PROJECTID']))
			me.projectid = parseInt(json['PROJECTID']);
		if(__defined(json['DATE']))
			me.datum = new Date(json['DATE']);
		if(__defined(json['DESC']))
			me.desc = json['DESC'];
		if(__defined(json['LINK']))
			me.link = json['LINK'];
		if(__defined(json['REIN']))
			me.in = parseFloat(json['REIN']);
		if(__defined(json['RAUS']))
			me.out = parseFloat(json['RAUS']);
		if(isNaN(me.in))
			me.in = 0.0;
		if(isNaN(me.out))
			me.out = 0.0;
	}
}

// a project.
var Data_Project = function()
{
	var me = this;
	this.id = 0;
	this.name = "Namenloses Projekt";
	this.desc = "Dieses Projekt hat keine Beschreibung.";
	this.link = "";
	
	this.parseGML = function(json, rootpath)
	{
		if(__defined(json['ID']))
			me.id = parseInt(json['ID']);
		if(__defined(json['NAME']))
			me.name = json['NAME'];
		if(__defined(json['DESC']))
			me.desc = json['DESC'];
		if(__defined(json['LINK']))
			me.link = json['LINK'];
	}
}

// a deckel/future.
var Data_Deckel = function()
{
	var me = this;
	this.id = 0;
	this.name = "Niemand";
	this.produkt = "Nichts";
	this.summe = 0;
	this.projectid = 0;
	this.datum = new Date();

	this.parseGML = function(json, rootpath)
	{
		if(__defined(json['ID']))
			me.id = parseInt(json['ID']);
		if(__defined(json['NAME']))
			me.name = json['NAME'];
		if(__defined(json['PRODUKT']))
			me.produkt = json['PRODUKT'];
		if(__defined(json['SUMME']))
			me.summe = parseFloat(json['SUMME']);
		if(isNaN(me.summe))
			me.summe=0;
		if(__defined(json['PROJECTID']))
			me.projectid = json['PROJECTID'];
		if(__defined(json['DATE']))
			me.datum = new Date(json['DATE']);
	}
}

// inventory item
var Data_Inventory = function()
{
	var me = this;
	this.id = 0;
	this.name = "Unbekanntes Produkt";
	this.desc = "Keine Beschreibung";
	// buy price muss beim inventar eingegeben werden (24x Bier für 14.40 zB.)
	// sell price muss beim inventar eingegeben werden (bevor man auf den knopf drückt.)
//	this.sellPrice = 0;	// standard sell price.
	this.amount = 0;
	this.projectid = 0;
	
	this.parseGML = function(json, rootpath)
	{
		if(__defined(json['ID']))
			me.id = parseInt(json['ID']);
		if(__defined(json['NAME']))
			me.name = json['NAME'];
/*		if(__defined(json['BUY']))
			me.buyPrice = parseFloat(json['BUY']);
		if(isNaN(me.buyPrice))
			me.buyPrice = 0.0;
		if(__defined(json['SELL']))
			me.sellPrice = parseFloat(json['SELL']);
		if(isNaN(me.sellPrice))
			me.sellPrice=0;
*/
		if(__defined(json['PROJECTID']))
			me.projectid = json['PROJECTID'];
		if(__defined(json['DESC']))
			me.desc = json['DESC'];
	}
}

// the parser for this application.
var DataParser = function()
{
	var me = this;

	this.projects = [];
	this.transactions = [];
	this.deckels = [];

	this.parseGML = function(json, rootpath)
	{
		// transaction database.
		if(__defined(json['TRANSACTIONS']))
		{
			for(var i = 0; i<json['TRANSACTIONS'].length; i++)
			{
				var tr = new Data_Transaction();
				tr.parseGML(json['TRANSACTIONS'][i]);
				me.transactions.push(tr);
			}
		}

		// project database
		if(__defined(json['PROJECTS']))
		{
			for(var i = 0; i<json['PROJECTS'].length; i++)
			{
				var pr = new Data_Project();
				pr.parseGML(json['PROJECTS'][i]);
				me.projects.push(pr);
			}
		}

		// deckels/futures
		if(__defined(json['DECKELS']))
		{
			for(var i=0;i<json['DECKELS'].length; i++)
			{
				var de = new Data_Deckel();
				de.parseGML(json['DECKELS'][i]);
				me.deckels.push(de);
			}
		}
		
		// inventory
		if(__defined(json['INVENTORY']))
		{
			for(var i=0;i<json['INVENTORY'].length; i++)
			{
				var inv = new Data_Inventory();
				inv.parseGML(json['INVENTORY'][i]);
				me.inventory.push(inv);
			}
		}
	}

	this.clear = function() 
	{
		me.projects = [];
		me.transactions = [];
		me.deckels = [];
		me.inventory = [];
	}
}

// add our parser to the parsers.
GMLParser.addParser("DataParser", new DataParser());
var g_idToShow = 0; // used for showing the transactions for a specific project.

// load the tables and fire a specific function after it.
function loadTable(which, id=0)
{
	log("Loading data...");
	switch(which)
	{
		case 5:
		case 'inventar':
		case 'inventory':
			PARSEGMLFILE("database.gml", inventoryLoaded);
			break;
		case 4:
		case 'deckel':
		case 'deckels':
			PARSEGMLFILE("database.gml", deckelsLoaded);
			break;
		case 3:
		case 'project':
			m_idToShow = id;
			PARSEGMLFILE("database.gml", singleProjectLoaded);
			break;
		case 2:
		case 'projects':
			PARSEGMLFILE("database.gml", projectsLoaded);
			break;
		case 1:
		case 'transactions':
		default:
			PARSEGMLFILE("database.gml", transactionsLoaded);
			break;
	}
}

function inventoryLoaded()
{
	log("Inventory loaded.");
	var txt=showTopBar(4);
	txt+=showInventoryTable(-1);
	document.getElementById("pagecontent").innerHTML=txt;
}

// show all transactions.
function transactionsLoaded()
{
	log("Transactions loaded.");

	var txt="";
	txt+=showTopBar(1);

	txt+='Transaktionen ohne Werte wurden gefunden, geschenkt oder waren schon im Inventar.<br />';
	txt+=showTransactions(-1);
	document.getElementById("pagecontent").innerHTML=txt;
}

// get a project by project id.
function getProjectByID(id)
{
	var parser = GMLParser.getParser("DataParser");
	for(var i = 0; i < parser.projects.length; i++)
	{
		var p = parser.projects[i];
		if(p.id==id)
			return p;
	}
	return new Data_Project();
}

// show the data for a single project.
function singleProjectLoaded()
{
	log("Project loaded.");
	var parser = GMLParser.getParser("DataParser");
	var proj = getProjectByID(g_idToShow);
	var txt="";

	txt+=showTopBar(0);

	txt+="<h1>Projekt #"+proj.id+":<br />"+proj.name+"</h1>";
	if(proj.link!="")
		txt+="<a href='"+proj.link+"'><h2>Projekt Seite</h2></a>";
	txt+="Beschreibung: "+proj.desc;
	txt+="<br /><br />Deckel für dieses Projekt:<br />"
	txt+=showDeckels(proj.id);

	txt+="<br /><br />Transaktionen für dieses Projekt:<br />";
	txt+=showTransactions(proj.id);
	
	document.getElementById("pagecontent").innerHTML=txt;
}

// get all deckels with the same name like the deckel with this id.
function getDeckelsForID(id)
{
	var parser = GMLParser.getParser("DataParser");
	var found = [];
	var dk = null;
	// get the deckel with the given ID.
	for(var i=0;i<parser.deckels.length;i++)
	{
		if(parser.deckels[i].id==id)
		{
			dk=parser.deckels[i];
			break;
		}
	}
	if(dk==null)
	{
		log("No deckel found for id "+id);
		return;
	}
	// get all deckels with the same name.
	for(var i=0;i<parser.deckels.length;i++)
	{
		if(dk.name.toLowerCase()==parser.deckels[i].name.toLowerCase())
			found.push(parser.deckels[i]);
	}

	return found;
}
