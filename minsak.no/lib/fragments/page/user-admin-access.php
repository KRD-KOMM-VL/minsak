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

echo '<div id="main">';
$editUser = $data['editUser'];
$initiatives = $app->dao->getInitiativesByUser($app->user);

echo '<h1>Brukerdata</h1>';
echo '<dl>';
echo '<dt>Navn:</dt><dd>' . $editUser['name'] . '</dd>';
echo '<dt>Brukernavn/e-post:</dt><dd>' . $editUser['username'] . '</dd>';

if ($initiatives) {
    echo '<h1>Initiativ eid av denne brukeren</h1>';
    echo '<ul>';
    foreach ($initiatives as $initiative) {
        echo '<li>' . $initiative['title'] . '</li>';
    }
    echo '</ul>';
}

if ($editUser['isModerator']) {
    $roles = $app->dao->getUserRoles($editUser);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $handledLocationIdsMap = Array();
        foreach ($_POST as $key => $value) {
            $matches = Array();
            if (preg_match('/^(\d+)_\w+$/', $key, $matches)) {
                $locationId = $matches[1];
                if (!array_key_exists($locationId, $handledLocationIdsMap)) {
                    $handledLocationIdsMap[$locationId] = true;
                    $initiativeModerator = array_key_exists($locationId . '_im', $_POST) ? (bool) $_POST[$locationId . '_im'] : false;
                    $signatureModerator = array_key_exists($locationId . '_sm', $_POST) ? (bool) $_POST[$locationId . '_sm'] : false;
                    if (array_key_exists($locationId . '_del', $_POST) && $_POST[$locationId . '_del']) {
                        // delete role for this location
                        $app->dao->deleteUserRole($editUser['id'], $locationId);
                    } else {
                        $data = Array(
                        	'user_id' => $editUser['id'],
                        	'location_id' => $locationId,
                        	'initiative_moderator' => $initiativeModerator ? '1' : '0',
                        	'signature_moderator' => $signatureModerator ? '1' : '0'
                        );
                        if (array_key_exists($locationId . '_add', $_POST) && $_POST[$locationId . '_add']) {
                            // add role for this location
                            $app->dao->addUserRole($data);
                        } else if (array_key_exists($locationId, $roles)) {
                            $app->dao->updateUserRole($data);
                            // update role for this location
                        }
                    }
                }
            }
        }
        // reload roles after update
        $roles = $app->dao->getUserRoles($editUser);
    }
    
    $locations = $app->dao->getLocationsInterlinked();
    foreach ($roles as $role) {
        $locationId = $role['location_id'];
        if (array_key_exists($locationId, $locations)) {
            $locations[$locationId]['role'] = $role;
            if (array_key_exists('parent', $locations[$locationId])) {
                if (!array_key_exists('subRoleCount', $locations[$locationId]['parent'])) {
                    $locations[$locationId]['parent']['subRoleCount'] = 1;
                } else {
                    $locations[$locationId]['parent']['subRoleCount']++;
                }
            }
        }
    }
    echo '<h1>Brukerroller</h1>';
    echo '<script type="text/javascript">';
    echo 'jQuery(document).ready(jQuery.proxy(minsak.initUserAccessForm, minsak));';
    echo '</script>';
    echo '<form action="/user-admin/access/' . $editUser['id'] . '" method="post">';
    echo '<ul>';
    foreach ($locations as $location) {
        if (!array_key_exists('parent', $location)) {
            $locationId = $location['id'];
            $hasRole = array_key_exists('role', $location);
            echo '<li' . ($hasRole ? ' class="hasRole"' : '') . '>';
            $app->renderFragment('widget/user-admin-access-location-role', Array('location' => $location));
            if (array_key_exists('children', $location)) {
                echo '<ul>';
                foreach ($location['children'] as $childLocation) {
                    echo '<li' . ($hasRole ? ' class="hasRole"' : '') . '>';
                    $app->renderFragment('widget/user-admin-access-location-role', Array('location' => $childLocation));
                    echo '</li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
    }
    echo '</ul>';
    echo '<input type="submit" name="submit" value="Oppdater" />';
    echo '</form>';
}
echo '</div>';