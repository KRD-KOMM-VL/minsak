<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
global $app;

$locations = $app->dao->getLocationsHierarchy();

echo '<ul>';
foreach ($locations as $location) {
    echo '<li><a href="/' . $location['slug'] . '">' . $location['name'] . '</a><ul>';
    foreach ($location['children'] as $child) {
        echo '<li><a href="/' . $child['slug'] . '">' . $child['name'] . '</a></li>';
    }
    echo '</ul></li>';
}
echo '</ul>';