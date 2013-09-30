<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
/*
 * Initialize the framework
 */


/*
 * BASEDIR points to the directory parent to the one containing this file.
 */
define('BASEDIR', dirname(dirname(__FILE__)));
  
/*
 * Include the required files
 */
include(BASEDIR.'/config.php');
include(BASEDIR.'/lib/app.php');
include(BASEDIR.'/lib/DataAccess.php');
include(BASEDIR.'/lib/Dao.php');
include(BASEDIR.'/lib/FormValidator.php');
include(BASEDIR.'/lib/Mail.php');
include(BASEDIR.'/lib/ImageUtils.php');

global $app;
$app = new AppContext();
