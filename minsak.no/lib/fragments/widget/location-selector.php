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
 * Location selector
 *
 * requires $data['elementId'] id of HTML element to be replaced
 * optional $data['locationId'] id of location to be selected
 * optional $data['mode'] operation mode
 * 
 * operation modes:
 * 0 - default, go to location, script creates wrapping form
 * 1 - update form field, script does not create wrapping form 
 * 
 */

global $app;
$elementId = $data['elementId'];
if (array_key_exists('locationId', $data)) {
    $locationId = $data['locationId'];
} else if ($app->currentLocation) {
    $locationId = $app->currentLocation['id'];
} else if ($app->cookieLocation) {
    $locationId = $app->cookieLocation['id'];
} else {
    $locationId = 0;
}
$mode = intval($app->getMapValue($data, 'mode', 0));
$buttonId = $app->getMapValue($data, 'buttonId', '');

?>
<script type="text/javascript">
jQuery('#<?php echo $elementId; ?>').removeClass("hidden");
jQuery(document).ready(function() {
    minsak.findLocationFormInit('#<?php echo $elementId . "', " . $locationId . ', ' . $mode . ", '#" . $buttonId . "'"; ?>);
});
</script>
