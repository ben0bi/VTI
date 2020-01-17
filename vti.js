// VTI Volle Transparenz Initiative
// Shared functions.

function showBlocker(txt="", show = true)
{
	$('#blocker_text').html(txt);
	$('#blocker').height($(document).height());
	if(show==true)
		$('#blocker').show();
	else
		$('#blocker').hide();
}

// hide the window for deletion of generic items.
function hideDeleteWindow() {$('#deleteWindow').hide();}

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
	this.price = 0;	// standard sell price.
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
		if(__defined(json['PRICE']))
			me.price = parseFloat(json['PRICE']);
		if(isNaN(me.price))
			me.price=0.0;
		if(__defined(json['PROJECTID']))
			me.projectid = json['PROJECTID'];
		if(__defined(json['DESC']))
			me.desc = json['DESC'];
		if(__defined(json['AMOUNT']))
			me.amount = parseFloat(json['AMOUNT']);
		if(isNaN(me.amount))
			me.amount=0;
	}
}

// the parser for this application.
var DataParser = function()
{
	var me = this;

	this.projects = [];
	this.transactions = [];
	this.deckels = [];
	this.inventory = [];

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

// THE MAIN CLASS
var VTI = function()
{
	var me = this;
	// var m_idToShow = -1; // used for showing the transactions for a specific project.	
	var m_parser = new DataParser();
	// add our parser to the parsers.
	GMLParser.addParser("DataParser", m_parser);
	
	// show the vti top bar.
	var showTopBar =function(which)
	{
		if(!VTI.showTopBarFlag)
			return "";
		
		// 0 = all

		var txt="<div id='ubermenu'>";
		// 1 = transactions
		if(which==1)
			txt+="Transaktionen | ";
		else
			txt+='<a href="javascript:" onclick="VTI.loadTable(1)">Transaktionen</a> | ';

		// 2 = projects
		if(which==2)
			txt+="Projekte | ";
		else
			txt+='<a href="javascript:" onclick="VTI.loadTable(2)">Projekte</a> | ';

		// 3 = deckel
		if(which==3)
			txt+="Deckel/Futures | ";
		else
			txt+='<a href="javascript:" onclick="VTI.loadTable(4)">Deckel/Futures</a> | ';

		// 4 = inventar
		if(which==4)
			txt+="Inventar"
		else
			txt+='<a href="javascript:" onclick="VTI.loadTable(5)">Inventar</a>';

		txt+="</div>";
		return txt;
	};
	
	// load the tables and fire a specific function after it.
	this.loadTable=function(which, customFunc=null)
	{
		log("Loading data...");
		hideDeleteWindow();
		switch(which)
		{
			case 5:
			case 'inventar':
			case 'inventory':
				showBlocker("Lade Inventar..");
				if(customFunc==null)
					customFunc = inventoryLoaded;
				PARSEGMLFILE("DB/combo_inventory.gml", customFunc);
			break;
			case 4:
			case 'deckel':
			case 'deckels':
				showBlocker("Lade Deckel..");
				if(customFunc==null)
					customFunc = deckelsLoaded;
				PARSEGMLFILE("DB/combo_deckels.gml", customFunc);
				break;
// NOT USED			case 3:
/*			case 'project':
				m_idToShow = id;
				showBlocker("Lade Projekt #"+id+"..");
				PARSEGMLFILE("database.gml", singleProjectLoaded);
				break;
*/
			case 2:
			case 'projects':
				showBlocker("Lade Projekte..");
				if(customFunc==null)
					customFunc = projectsLoaded;
				PARSEGMLFILE("DB/combo_projects.gml", customFunc);
				break;
			case 1:
			case 'transactions':
			default:
				showBlocker("Lade Transaktionen..");
				if(customFunc==null)
					customFunc = transactionsLoaded;
				PARSEGMLFILE("DB/combo_transactions.gml", customFunc);
				break;
		}
	}

	// inventory was loaded.
	var inventoryLoaded=function()
	{
		log("Inventory loaded.");
		var txt=showTopBar(4);
		txt+=showInventoryTable(-1,true);
		document.getElementById("pagecontent").innerHTML=txt;
		showBlocker("", false);
	}

	// all transactions loaded.
	var transactionsLoaded=function()
	{
		log("Transactions loaded.");

		var txt="";
		txt+=showTopBar(1);

		txt+='Transaktionen ohne Werte wurden gefunden, geschenkt oder waren schon im Inventar.<br />';
		txt+=showTransactions(-1);
		document.getElementById("pagecontent").innerHTML=txt;
		showBlocker("", false);
	}

	// show the data for a single project.
	this.singleProjectLoaded=function()
	{
		var projectid = m_showProjectId;
		m_showProjectId = -1;
		
		log("Project loaded.");
		var proj = getProjectByID(projectid);
		var txt="";

		txt+=showTopBar(0);

		txt+="<h1>Projekt #"+proj.id+":<br />"+proj.name+"</h1>";
		if(proj.link!="")
			txt+="<a href='"+proj.link+"'><h2>Projekt Seite</h2></a>";
		txt+="Beschreibung: "+proj.desc;
		txt+="<br /><br />Deckel für dieses Projekt:<br />"
		txt+=showDeckels(proj.id);

		txt+="<br /><br />Inventar:<br />";
		txt+=showInventoryTable(proj.id,true);

		txt+="<br /><br />Transaktionen für dieses Projekt:<br />";
		txt+=showTransactions(proj.id);
	
		document.getElementById("pagecontent").innerHTML=txt;
		showBlocker("", false);
	}

	// projects data loaded.
	function projectsLoaded()
	{
		log("Projects loaded.");
		//var parser = GMLParser.getParser("DataParser");

		var txt="";
		txt+=showTopBar(2);

		txt+='<table border="1">';
		txt+="<tr><td>";
		txt+="ID";
		txt+="</td><td>";
		txt+="Name";
		txt+="</td><td>";
		txt+="Beschreibung";
		txt+="</td><td>";
		txt+="Link";
		txt+="</td><td>";
		txt+="Balanz";
		txt+="</td><td>";
		txt+="!DEL!";
		txt+="</td></tr>";

		// add the inputs
		txt+="<tr><td>";
		txt+="<a href='javascript:' class='btn' onclick='addProject();'><nobr>NEU =&gt;</nobr></a>";
		txt+="</td><td>";
		txt+="<input type='text' id='input_project_name' class='fw' />";
		txt+="</td><td>";
		txt+="<input type='text' id='input_project_desc' class='fw' />";
		txt+="</td><td>";
		txt+="<input type='text' id='input_project_link' class='fw' />";
		txt+="</td><td>";
		txt+="<a href='javascript:' class='btn' onclick='addProject();'><nobr>&lt;= NEU</nobr></a>";
		txt+="</td><td>";
		txt+="! v !";
		txt+="</td></tr>";

		var fullbalanz = 0;
		var col = "";
		for(var i = 0; i < m_parser.projects.length; i++)
		{
			var p = m_parser.projects[i];
			txt+="<tr><td><a href='javascript:' onclick='VTI.showProject("+p.id+");'>["+p.id+"]</a>";
			txt+="</td><td><a href='javascript:' onclick='VTI.showProject("+p.id+");'>"+p.name+"</a>";
			var balanz = 0;
			col="#33FF33";
			for(var pq = 0; pq<m_parser.transactions.length;pq++)
			{
				var t = m_parser.transactions[pq];
				if(t.projectid==p.id)
				{
					balanz=balanz+t.in-t.out;
				}
			}
			if(balanz<0)
				col="#FF3333";
			txt+="</td><td>"+p.desc;
			txt+="</td><td><a href='"+p.link+"'>"+p.link+"</a>";
			txt+="</td><td class='tdr'><font color='"+col+"'>"+parseFloat(balanz).toFixed(2)+"</font>";
			txt+="</td><td><a href='javascript:' class='btnred' onclick='showDeleteWindow(\"PROJECTS\","+p.id+", $(this))'>!DEL!</a>";
			txt+="</tr>";
			fullbalanz+=balanz;
		}
		col="#33FF33";
		if(fullbalanz<0)
			col="#FF3333";
		txt+='<tr><td colspan="4"></td><td class="tdr"><font color="'+col+'">'+parseFloat(fullbalanz).toFixed(2)+'</font></td><td></td></tr>';
		txt+='</table>';
		document.getElementById("obergesamtfeld").innerHTML="<div class='fullbalanz_w'><font color='"+col+"'>"+parseFloat(fullbalanz).toFixed(2)+"</font></div>";
		document.getElementById("pagecontent").innerHTML=txt;

		showBlocker("", false);
	}

	// all deckels loaded
	var deckelsLoaded=function()
	{
		log("Deckels loaded.");
		g_deckelName = -1;
		//var parser = GMLParser.getParser("DataParser");
		var fullbalanz = 0;

		var txt="";
		txt+=showTopBar(3);

		txt+=showDeckelStandards(0,false);

		var fullbalanz = 0;
		for(var i = 0; i < m_parser.deckels.length; i++)
		{
			var d = m_parser.deckels[i];
			txt+=showDeckelStandards(1,false,d);
			fullbalanz+=parseFloat(d.summe);
		}
	
		txt+='</table>';
	
		var deckel = "<div class='deckel'>"+parseFloat(fullbalanz).toFixed(2)+"</div>";
	
		document.getElementById("obergesamtfeld").innerHTML=deckel;
		document.getElementById("pagecontent").innerHTML=txt;
		showBlocker("", false);
	}
	
	// show the deckels for a given project.
	var showDeckels=function(projectid=-1)
	{
	//	var parser = GMLParser.getParser("DataParser");
		var fullbalanz = 0;
	
		var txt="";
		txt+=showDeckelStandards(0,false,null,projectid);
	
		for(var i = 0; i < m_parser.deckels.length; i++)
		{
			var d = m_parser.deckels[i];
			if(projectid==-1 || d.projectid==projectid)
			{
				txt+=showDeckelStandards(1,false,d);
				fullbalanz+=parseFloat(d.summe);
			}
		}
	
		txt+='</table>';	
		txt="Deckelsumme: "+parseFloat(fullbalanz).toFixed(2)+"<br />"+txt;
		return txt;
	}


	// create a table item for the deckels.
	var showDeckelStandards=function(which, ommitname=false,deckel = null, projectid=0)
	{
		var txt="";
		// table header
		if(which==0)
		{
			txt+='<table border="1">';
			txt+="<tr><td>";
			txt+="&yen;&euro;&dollar; &lt;=";
			txt+="</td><td>";
			txt+="ID";
			txt+="</td><td>";
			txt+="Datum";
			txt+="</td><td>";
			if(!ommitname)
			{
				txt+="Name";
				txt+="</td><td>";
			}
			txt+="Produkt";
			txt+="</td><td>";
			txt+="Projekt";
			txt+="</td><td>";
			txt+="Deckelsumme";
			txt+="</td><td>";
			txt+="=&gt; &yen;&euro;&dollar;";
			txt+="</td><td>";
			txt+="!DEL!";
			txt+="</td></tr>";

			// add the inputs
			txt+="<tr><td>"
			txt+="<a href='javascript:' class='btn' onclick='addDeckel();'><nobr>NEU =&gt;</nobr></a>";
			txt+="</td><td>";
			txt+="<a href='javascript:' class='btn' onclick='addDeckel();'><nobr>NEU =&gt;</nobr></a>";
			txt+="</td><td>(auto)</td><td>";
			if(!ommitname)
			{
				txt+="<input type='text' id='input_deckel_name' class='fw' />";
				txt+="</td><td>";
			}
			txt+="<input type='text' id='input_deckel_product' class='fw' />";
			txt+="</td><td>";
			txt+="<select id='input_deckel_project' class='fw'>";
			//var parser = GMLParser.getParser("DataParser");
			for(var qq=0;qq<m_parser.projects.length;qq++)
			{
				var p = m_parser.projects[qq];
				var sel="";
				if(p.id==projectid)
					sel="selected='selected'";
				txt+="<option value='"+p.id+"' "+sel+" >["+p.id+"] "+p.name+"</option>";
			}
			txt+="</select>"
			txt+="</td><td>";
			txt+="<input type='text' id='input_deckel_summe' class='fw' />";
			txt+="</td><td>";
			txt+="<a href='javascript:' class='btn' onclick='addDeckel();'><nobr>&lt;= NEU</nobr></a>";
			txt+="</td><td>";
			txt+="! v !";
			txt+="</td></tr>";
		}
		// entry
		if(which==1)
		{
			txt+="</td><td><a href='javascript:' class='btngreen fw' onclick='luepfDeckel("+deckel.id+")'>&nbsp;&yen;&euro;&dollar; &lt;=&nbsp;</a>";
			txt+="</td><td>"+deckel.id+"</a>";
			txt+="</td><td>"+deckel.datum.getDate()+"."+(deckel.datum.getMonth()+1)+"."+deckel.datum.getFullYear();
			if(!ommitname)
				txt+="</td><td><a href='javascript:' onclick='VTI.loadDeckelsForIDByName("+deckel.id+");'>"+deckel.name+"</a>";
		
			txt+="</td><td>"+deckel.produkt;
			txt+="</td><td>["+deckel.projectid+"] "+getProjectByID(deckel.projectid).name;
			txt+="</td><td>"+parseFloat(deckel.summe).toFixed(2);
			txt+="</td><td><a href='javascript:' class='btngreen fw' onclick='luepfDeckel("+deckel.id+")'>&nbsp;=&gt; &yen;&euro;&dollar;&nbsp</a>";
			txt+="</td><td><a href='javascript:' class='btnred' onclick='showDeleteWindow(\"DECKELS\","+deckel.id+", $(this))'>!DEL!</a>";
			txt+="</tr>";
		}
		return txt;
	}

	// show all inventory or only the one for a specific project.
	var showInventoryTable=function(proid, isadmin =false)
	{
	//	var parser = GMLParser.getParser("DataParser");
		var txt="";
		txt+="<table border='1'>";
		txt+="<tr><td>";
		txt+="ID";
		txt+="</td><td>";
		txt+="Name";
		txt+="</td><td>";
		txt+="Beschreibung";
		txt+="</td><td>";
	
		txt+="Projekt";
		txt+="</td><td>";
	
		txt+="Standardpreis";
		txt+="</td><td>";	
	
		txt+="Anzahl";

	// add the inputs for a new item.
		if(isadmin)
		{
			txt+="</td><td>";
			txt+="!DEL!";
			txt+="</td></tr>";

			txt+='<tr><td>';
			txt+='<a href="javascript:" class="btn fw" onclick="createInventoryItem();">NEU =&gt;</a>';
			txt+='</td><td>';
			txt+="<input type='text' class='fw' id='input_inventory_name' />";
			txt+='</td><td>';
			txt+="<input type='text' class='fw' id='input_inventory_desc' />";
			txt+='</td><td>';
			txt+="<select name='projectselector' class='fw' id='input_inventory_projectid'>";
			for(var i=0;i<m_parser.projects.length;i++)
			{
				var p = m_parser.projects[i];
				var sel="";
				if(i==proid)
					sel='selected="selected"';
				txt+='<option value="'+p.id+'" '+sel+'>'+p.id+": "+p.name+'</option>';
			}
			txt+="</select>";
			txt+="</td><td>";
			txt+="<input type='text' class='fw' id='input_inventory_price' value='0.00' />";
			txt+="</td><td>";	
			txt+='<a href="javascript:" class="btn fw" onclick="createInventoryItem();">&lt;= NEU</a>';
			txt+="</td><td>";
			txt+="! v !";
			txt+='</td></tr>';
		}else{
			txt+="</td></tr>";
		}
		var o = "";
	
		for(var i=0;i<m_parser.inventory.length;i++)
		{
			var itm = m_parser.inventory[i];
			// maybe ommit this one.
			if(proid>=0 && proid!=itm.projectid)
				continue;
			
			txt+='<tr><td>';
			txt+=itm.id;
			txt+='</td><td>';
			txt+=itm.name;
			txt+='</td><td>';
			txt+=itm.desc;
//			if(proid>=0)
//			{
				txt+='</td><td>';
				txt+="["+itm.projectid+"] "+getProjectByID(itm.projectid).name;
//			}
			txt+='</td><td>';
			txt+=parseFloat(itm.price).toFixed(2);
			txt+='</td><td>';
			txt+=itm.amount;
			if(isadmin)
			{
				txt+='</td><td>';
				txt+='<a href="javascript:" class="btnred fw" onclick="showDeleteWindow(\'INVENTORY\','+itm.id+',$(this));">!DEL!</a>';
			}
			txt+='</td></tr>';
			o+="<option value='"+itm.id+"'>["+itm.id+"] "+itm.name+"</option>";
		}
	
		txt+="</table>";

		if(isadmin)
		{
			// set obergesamtfeld.
			var invval = 0.0;
			var g = '<div id="buysellwindow">';
			if(o=="")
			{
				g+="Bitte erst ein Produkt erstellen.";
			}else{
				invval = m_parser.inventory[0].price;
				g+="<table border='0'><tr><td colspan='6'>";
				g+= "<select id='input_buysell_itemid' class='fw' oninput='inventorychange(1);'>";
				g+=o;
				g+="</select></td></tr>";
				g+='<tr><td><input class="fifty" id="input_buysell_amount" type="text" placeholder="Anzahl" value="1" oninput="inventorychange(2);" /></td><td> * </td>';
				g+='<td><input class="fifty" id="input_buysell_itemprice" type="text" placeholder="Stückpreis" value="'+invval+'" oninput="inventorychange(3);" /></td><td>CHF = </td>';
				g+='<td><input class="fifty" id="input_buysell_gesamtprice" type="text" placeholder="Gesamtpreis" value="'+invval+'" oninput="inventorychange(4);" /></td><td>CHF</td></tr>';
				g+='<tr><td colspan="3"></td><td colspan="3"><a href="javascript:" onclick="BuySellInventory(1);" class="btn">&lt;= &#8383;U&yen;</a> | ';
				g+='<a href="javascript:" onclick="BuySellInventory(0);" class="btngreen">&dollar;&euro;&pound;&pound; =&gt;</a></td></tr>';
				g+='</div>';
			}
			g+='</div>';
			$('#obergesamtfeld').html(g);
		}
		return txt;
	}

	// load all deckels for a given deckel id, by name.
	this.loadDeckelsForIDByName=function(deckelid)
	{
		var dar = getDeckelsForID(deckelid);
	
		if(dar.length<=0)
		{
			log("NO DECKELS FOUND!");
			return;
		}
//		g_deckelName = dar[0].name;

		var fullbalanz = 0;

		var txt="";
		txt+=showTopBar(0);

		txt+="<h1>Deckel von "+dar[0].name+"</h1>";

		txt+=showDeckelStandards(0,true);

		var fullbalanz = 0.0;
		for(var i = 0; i < dar.length; i++)
		{
			var d = dar[i];
			txt+=showDeckelStandards(1,true,d);
			fullbalanz+=parseFloat(d.summe);
		}
	
		txt+='</table>';
	
		var deckel = "<div class='deckel'>"+parseFloat(fullbalanz).toFixed(2)+"</div>";
	
		document.getElementById("obergesamtfeld").innerHTML=deckel;
		document.getElementById("pagecontent").innerHTML=txt;	
	}

	// get a project by project id.
	var getProjectByID=function(id)
	{
		//var parser = GMLParser.getParser("DataParser");
		for(var i = 0; i < m_parser.projects.length; i++)
		{
			var p = m_parser.projects[i];
			if(p.id==id)
				return p;
		}
		return new Data_Project();
	}

	// get an inventory item by its id.
	this.getInventoryItemByID=function(id)
	{
		//var parser = GMLParser.getParser("DataParser");
	
		id=parseInt(id);
		if(isNaN(id))
			return new Data_Inventory();
		for(var i=0;i<m_parser.inventory.length;i++)
		{
			var itm=m_parser.inventory[i];
			if(itm.id==id)
				return itm;
		}
		return new Data_Inventory();
	}

	// get all deckels with the same name like the deckel with this id.
	var getDeckelsForID=function(id)
	{
		//var parser = GMLParser.getParser("DataParser");
		var found = [];
		var dk = null;
		// get the deckel with the given ID.
		for(var i=0;i<m_parser.deckels.length;i++)
		{
			if(m_parser.deckels[i].id==id)
			{
				dk=m_parser.deckels[i];
				break;
			}
		}
		if(dk==null)
		{
			log("No deckel found for id "+id);
			return;
		}
		// get all deckels with the same name.
		for(var i=0;i<m_parser.deckels.length;i++)
		{
			if(dk.name.toLowerCase()==m_parser.deckels[i].name.toLowerCase())
				found.push(m_parser.deckels[i]);
		}

		return found;
	}
	
	
	// show all transactions or only the ones for a specific project.
	var showTransactions=function(proid)
	{
	//	var parser = GMLParser.getParser("DataParser");

		var colsp = 4;
	
		var txt = "";
		txt+= '<table border="1">';
		txt+="<tr><td>";
		txt+="ID";
		txt+="</td><td>";
		txt+="Datum";
		txt+="</td><td>";
		txt+="Beschreibung";
		txt+="</td><td>";
		if(proid<0)
		{
			txt+="Projekt-ID";
			txt+="</td><td>";
			colsp+=1;
		}
		txt+="Produktlink";
		txt+="</td><td>";	
		txt+="REIN";
		txt+="</td><td>";
		txt+="RAUS";
		txt+="</td><td>";
		txt+="GESAMT";
		txt+="</td><td>";
		txt+="!DEL!";
		txt+="</td></tr>";

		// add the inputs
		txt+="<tr><td>";
		txt+="<a href='javascript:' class='btn' onclick='addTransaction();'><nobr>NEU =&gt;</nobr></a>";
		txt+="</td><td>";
		txt+="(auto)";
		txt+="</td><td>";
		txt+="<input type='text' class='fw' id='input_transaction_desc' />";
		txt+="</td><td>";
		if(proid<0)
		{
			txt+="<select name='projectselector' class='fw' id='input_transaction_projectid'>";
			for(var i=0;i<m_parser.projects.length;i++)
			{
				var p = m_parser.projects[i];
				txt+='<option value="'+p.id+'">'+p.id+": "+p.name+'</option>';
			}
			txt+="</select>";
			txt+="</td><td>";
		}
		txt+="<input type='text' class='fw' id='input_transaction_productlink' />";
		txt+="</td><td>";	
		txt+="<input type='text' class='fw' id='input_transaction_rein' />";
		txt+="</td><td>";
		txt+="<input type='text' class='fw' id='input_transaction_raus' />";
		txt+="</td><td>";
		txt+="<a href='javascript:' class='btn' onclick='addTransaction();'><nobr>&lt;= NEU</nobr></a>";
		txt+="</td><td>! v !";
		txt+="</td></tr>";
	
		var gesamtvalue = 0;
		var gesamtrein = 0;
		var gesamtraus = 0;
		for(var i = m_parser.transactions.length-1; i>=0; i--)
		{
			var t = m_parser.transactions[i];
			if(proid>=0 && t.projectid!=proid)
				continue;
			txt+="<tr><td>";
			txt+=t.id;
			txt+="</td><td>";
			txt+=t.datum.getDate()+"."+(t.datum.getMonth()+1)+"."+t.datum.getFullYear();
			txt+="</td><td>";
			txt+=t.desc;
			if(proid<0)
			{
				var prname = getProjectByID(t.projectid).name;
				txt+="</td><td><a href='javascript:' onclick='VTI.showProject("+t.projectid+");'>["+t.projectid+"] "+prname+"</a>";
			}
			txt+="</td><td>";
			if(t.link.length>0)
			{
				txt+='<a href="'+t.link+'" target="vti_productlink">[Link]</a>';
			}else{
				txt+="---";
			}
			txt+="</td><td class='tdr'><font color='#33FF33'>";
			if(t.in!=0)
				txt+=Math.abs(parseFloat(t.in)).toFixed(2);
			txt+="</font></td><td class='tdr'><font color='#FF3333'>";
			if(t.out!=0)
				txt+=Math.abs(parseFloat(t.out)).toFixed(2);
			var q = t.in-t.out;
			var col = "#33FF33";
			if(q<0)
				col="#FF3333";
			txt+="</font></td><td class='tdr'><font color='"+col+"'>";
			txt+=parseFloat(q).toFixed(2);
			txt+="</font></td>";
			txt+="<td><a href='javascript:' class='btnred' onclick='showDeleteWindow(\"TRANSACTIONS\","+t.id+", $(this))'>!DEL!</a>";
			txt+="</tr>";
			gesamtrein+=t.in;
			gesamtraus+=t.out;
			gesamtvalue+=q;
		}
	
		txt+='<tr>';
		txt+='<td colspan="'+colsp+'"></td>';
		col='#33FF33';
		if(gesamtvalue<0)
			col='#FF3333';

		var gesamttxt = "";
		gesamttxt+='<td class="tdr"><font color="#33FF33">'+parseFloat(gesamtrein).toFixed(2)+'</font></td>';
		gesamttxt+='<td class="tdr"><font color="#FF3333">'+parseFloat(gesamtraus).toFixed(2)+'</font></td>';
		gesamttxt+='<td class="tdr"><font color="'+col+'">'+parseFloat(gesamtvalue).toFixed(2)+'</font></td>';
		gesamttxt+='</tr>';
		gesamttxt+='</table>';

		txt+=gesamttxt;
	
		// set obergesamtfeld.
		var g = "<table border='1'><tr><td>REIN</td><td>RAUS</td><td>DIFF</td></tr><tr>"+gesamttxt;
		document.getElementById('obergesamtfeld').innerHTML=g;
	
		return txt;
	}

	// initialize VTI
	this.init=function(tabletoshow)
	{
		me.loadTable(tabletoshow);
	}
	
	// this show project
	var m_showProjectId = -1;
	this.showProject = function(projectid)
	{
		m_showProjectId = projectid;
		PARSEGMLFILE("DB/combo_singleproject.gml", VTI.instance.singleProjectLoaded);
	}
};
VTI.showTopBarFlag = true;

VTI.instance = new VTI();
VTI.init = function(tabletoshow) {VTI.instance.init(tabletoshow);}
VTI.loadTable = function(whichtable, customFunc = null) {VTI.instance.loadTable(whichtable, customFunc);}
VTI.loadDeckelsForIDByName = function(deckelid) {VTI.instance.loadDeckelsForIDByName(deckelid);}
VTI.showProject = function(projectid) 
{
	VTI.instance.showProject(projectid);
}

VTI.getInventoryItemByID = function(itemid) {return VTI.instance.getInventoryItemByID(itemid);}
