/* General JSON data writer.
	by Benedict Jäggi in 2020
	
	needs a php setup on the server and the file
	gimli_ajax.php
	
	Maybe you need to set permissions on the table files and/or their folders:
	sudo chown www-data:www-data myfile.gml ==> The web user is now the owner.
	sudo chmod 775 myfile.gml ==> make it read and writeable for everyone.
*/
	
/* GIMLi does NOT need this!
	This is for the website creator to make easy calls to his GML-databases.
	Be aware that each table here has its own file. You may need to make some
	GMLS-tag additions for the dependencies.
	
	E.g. Table ITEMS needs table ROOMS, which would be saved as db_items.gml and db_rooms.gml.
	In db_items.gml, you may need to add "GMLS":["db_rooms.gml"]
	
	I personally made another set of files, the combo_ files, 
	where all dependencies for each table and website view are set.
	I have several tables, one is named PROJECTS. This one needs only one other table
	if ALL projects are shown, but ALL other tables if ONE specific project is shown.
	So I have combo_projects.gml for the list view and combo_singleproject.gml for 
	the single project view. Just one line of code (load the other combo) and all the 
	other stuff is done autmagically - just use the tables. :)
*/

var GMLWriter = function() {};

// Set the right relative path here:
GMLWriter.ajaxFile = "gimli-ajax.php";

GMLWriter.remove=
GMLWriter.del=
GMLWriter.removeEntry=
GMLWriter.deleteEntry=function(whichtable, id, successFunction=null, errorFunction=null)
{
	var data = {
				ID: id,
				whichtable: whichtable,
				CUD: 'delete'
				};			// the CUD event to do.
					// ^if CUD == 'create', it will create OR update an object.
					// if CUD == 'delete', it will delete the object.
		
	$.ajax({
		type: 'POST',
		url: GMLWriter.ajaxFile,
		data: data,
		success: successFunction,
		error: errorFunction,
		dataType: 'text'
	});
};

GMLWriter.update=
GMLWriter.write=
GMLWriter.updateEntry=
GMLWriter.writeEntry=
function(whichtable, id, data, successFunction=null, errorFunction=null)
{
	var data = {
				CUD: 'create',
				whichtable: whichtable,
				ID: id,
				DATA: data
			};

	$.ajax({
		type: 'POST',
		url: GMLWriter.ajaxFile,
		data: data,
		success: successFunction,
		error: errorFunction,
		dataType: 'text'
	});
};
