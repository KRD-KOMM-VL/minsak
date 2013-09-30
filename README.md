minsak
======

Minsak.no er et nettsted er utviklet av firmaet Melvær og Lien på oppdrag fra Kommunal- og regionaldepartementet. På nettstedet kan innbyggere foreslå en sak og samle underskrifter for den. Med nok underskrifter legger løsningen opp til at saken kan sendes til kommunen. Oppfyller saken vilkårene kommuneloven § 39a skal den behandles i kommunestyret eller fylkestinget. Det er også mulig å legge inn debattinnlegg i tilknytning til saken så lenge underskriftsaksjonen pågår.


---------------------------------------------------------------------
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.

---------------------------------------------------------------------


How to set up the system:
-------------------------


Configure the database:

- Create the database (optionally by using the commented statements in sql/create-database.sql)
  - You might consider renaming the database to minsak, as that is the sites new name. config.php must then be updated.

- Run sql/create-database.sql on the database

- Run sql/initial-data.sql on the database (setting up all locations (counties and municipalities))

- Run sql/set-signatures-required.sql on the database (setting up number of signatures required for each location)

- Run sql/set-email-and-web-for-locations.sql on the database (you might only want to do this on the production database)



Prepare the filesystem:

- Apache must have write access to logs and webroot/uploads



Configure the application (edit config.php)

- The config.php provided is a skeleton for production settings. As long as the defines are present, it is
  no problem to use separate files for test and production if that works better for you.




Set up the web server

- let the documentroot refer to the webroot directory

- make sure the statements in webroot/.htaccess are supported and allowed
