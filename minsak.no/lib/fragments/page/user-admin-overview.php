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
$users = $app->dao->getAllUsers();
?>
<div id="main">
    <h1>Brukeradministrasjon</h1>
    
    <a href="/user-admin/profile">Lag ny bruker</a>
    
    <table>
      <tr>
        <th>Navn</th>
        <th>Brukernavn</th>
        <th>Operasjoner</th>
      </tr>
    <?php foreach ($users as $user) { ?>
      <tr>
        <td><?php echo $user['name']; ?></td>
        <td><?php echo $user['username']; ?></td>
        <td>
          <a href="/user-admin/profile/<?php echo $user['id']; ?>">Profil</a>
          <a href="/user-admin/access/<?php echo $user['id']; ?>">Tilgang</a>
          <a href="/user-admin/delete-confirm/<?php echo $user['id']; ?>">Slett</a>
        </td>
      </tr>
    <?php } ?>
    </table>
</div>