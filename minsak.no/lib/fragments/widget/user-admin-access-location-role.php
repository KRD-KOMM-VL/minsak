<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
$location = $data['location'];
$locationId = $location['id'];
$role = array_key_exists('role', $location) ? $location['role'] : false;

echo '<span class="location-wrapper location_' . $locationId . '">';
echo $location['name'];
if (array_key_exists('subRoleCount', $location)) {
    echo ' (' . $location['subRoleCount'] . ')';
}
echo '<span class="location-elements-active' . (!$role ? ' hide' : '') . '">';
$imId = $locationId . '_im';
$smId = $locationId . '_sm';
echo ' <label for="' . $imId . '">Initiativmoderator</label> <input type="checkbox" id="' . $imId . '" name="' . $imId . '"' . ($role && $role['initiative_moderator'] ? ' checked="checked"' : '') . ' />';
echo ' <label for="' . $smId . '">Signaturmoderator</label> <input type="checkbox" id="' . $smId . '" name="' . $smId . '"' . ($role && $role['signature_moderator'] ? ' checked="checked"' : '') . ' />';
echo ' <a href="" class="delete-role">Slett rolle</a>';
echo '</span>';
echo '<span class="location-elements-inactive' . ($role ? ' hide' : '') . '">';
echo ' <a href="" class="add-role">Legg til rolle</a>';
echo '</span>';
if ($role) {
    echo '<input type="hidden" name="' . $locationId . '_del' . '" value="" />';
} else {
    echo '<input type="hidden" name="' . $locationId . '_add' . '" value="" />';
}
echo '</span>';
