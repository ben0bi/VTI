// VTI Volle Transparenz Initiative
// Shared functions.

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

// the parser for this application.
var DataParser = function()
{
	var me = this;
	
	this.projects = [];
	this.transactions = [];
	
	this.parseGML = function(json, rootpath)
	{
		if(__defined(json['TRANSACTIONS']))
		{
			for(var i = 0; i<json['TRANSACTIONS'].length; i++)
			{
				var tr = new Data_Transaction();
				tr.parseGML(json['TRANSACTIONS'][i]);
				me.transactions.push(tr);
			}
		}
		if(__defined(json['PROJECTS']))
		{
			for(var i = 0; i<json['PROJECTS'].length; i++)
			{
				var pr = new Data_Project();
				pr.parseGML(json['PROJECTS'][i]);
				me.projects.push(pr);
			}
		}
	}
	
	this.clear = function() 
	{
		me.projects = [];
		me.transactions = [];
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

// show all transactions.
function transactionsLoaded()
{
	log("Transactions loaded.");
	
	var txt="";
	txt+='<div id="ubermenu">';
	txt+='Transaktionen | ';
	txt+='<a href="javascript:" onclick="loadTable(2);">Projekte</a>';
	txt+='</div>';
	
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
	txt+='<div id="ubermenu">';
	txt+='<a href="javascript:" onclick="loadTable(1)">Transaktionen</a> | ';
	txt+='<a href="javascript:" onclick="loadTable(2)">Projekte</a></div>';

	txt+="<h1>"+proj.name+"</h1>";
	if(proj.link!="")
		txt+="<a href='"+proj.link+"'><h2>Projekt Seite</h2></a>";
	txt+="ID: "+proj.id+"<br />";
	txt+="Beschreibung: "+proj.desc;
	txt+="<br /><br />Transaktionen für dieses Projekt:<br />";


	txt+=showTransactions(proj.id);
	document.getElementById("pagecontent").innerHTML=txt;
}
