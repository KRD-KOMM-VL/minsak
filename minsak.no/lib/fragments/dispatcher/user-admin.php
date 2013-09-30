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

$operation = count($app->urlParts) > 1 ? $app->urlParts[1] : '';
$editUserId = count($app->urlParts) > 2 ? ((int) @$app->urlParts[2]) : 0;
if ($editUserId) {
    $editUser = $app->dao->getUserById($editUserId);
} else {
    $editUser = null;
}

switch ($operation) {
    case '':
        $app->renderFragment('page/user-admin-overview');
        break;
    case 'profile':
        // if no editUser, prepare to edit a new user
        $app->breadcrumbs[] = Array('label' => 'Profil', 'link' => $app->url);
        $app->renderFragment('page/user-admin-profile', Array('editUser' => $editUser));
        break;
    case 'access':
        $app->breadcrumbs[] = Array('label' => 'Tilgang', 'link' => $app->url);
        if (!$editUser) {
            $app->fail(404, 'Siden finnes ikke');
        }
        $app->renderFragment('page/user-admin-access', Array('editUser' => $editUser));
        break;
    case 'delete-confirm': // display are you sure
	    $app->breadcrumbs[] = Array('label' => 'Slett bruker', 'link' => $app->url);
        if (!$editUser) {
            $app->fail(404, 'Siden finnes ikke');
        }
        $app->renderFragment('page/user-admin-delete-confirm', Array('editUser' => $editUser));
        break;
    case 'delete': // do the deletion and return to user admin
        if (!$editUser) {
            $app->fail(404, 'Siden finnes ikke');
        }
        $app->dao->deleteUser($editUser['id']);
        $app->redirect('/user-admin');
        break;
}

