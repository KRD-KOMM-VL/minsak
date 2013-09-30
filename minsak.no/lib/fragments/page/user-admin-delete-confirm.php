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

$editUser = $data['editUser'];

echo '<div id="main">';
echo '<h1>Bekreft slett bruker</h1>';
echo 'Er du sikker på at du vil slette brukeren ' . $editUser['name'] . ' - ' . $editUser['username'] . '?';
echo ' <a href="/user-admin/delete/' . $editUser['id'] . '">Slett</a>';
echo ' <a href="/user-admin">Avbryt</a>';
echo '</div>';