Associated tables can be added with the GMLS-tag.

E.g. The transactions-table needs the names from the projects in the projects-table. So there is a GMLS-entry with the value "db_projects.gml"

In the first version, the GMLS were added directly to the table-sheet file. (The above GMLS-tag was in db_transactions.gml)

Now, there are combo-files for each task of the application:

combo_transactions incorporates the above db_transactions.gml and db_projects.gml files.
A project vice-versa needs the transactions, so the combo_projects.gml file is exactly the same.

But the single project view is in need of ALL the tables, so the combo_singleproject.gml contains them all.

etc.
