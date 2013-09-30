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
    $initiativesPerStatus = $app->dao->getInitiativesPerStatus();
    $statusInfoMap = Array(
        AppContext::INITIATIVE_STATUS_DRAFT => Array('title' => 'Under oppretting', 'count' => 0),
        AppContext::INITIATIVE_STATUS_UNMODERATED => Array('title' => 'Venter på moderering', 'count' => 0),
        AppContext::INITIATIVE_STATUS_OPEN => Array('title' => 'Åpen', 'count' => 0),
        AppContext::INITIATIVE_STATUS_SCREENING => Array('title' => 'Venter på signaturmoderering', 'count' => 0),
        AppContext::INITIATIVE_STATUS_COMPLETED => Array('title' => 'Innsendt', 'count' => 0),
        AppContext::INITIATIVE_STATUS_REJECTED => Array('title' => 'Avvist av moderator', 'count' => 0),
        AppContext::INITIATIVE_STATUS_WITHDRAWN => Array('title' => 'Trukket av innsender', 'count' => 0)
    );
    $totalInitiatives = 0;
    foreach ($initiativesPerStatus as $row) {
        $status = $row['status'];
        $count = $row['count'];
        $totalInitiatives += $count;
        if (array_key_exists($status, $statusInfoMap)) {
            $statusInfoMap[$status]['count'] = $count;
        }
    }
    
    $locations = $app->dao->getLocations();
    $initiativesPerLocation = $app->dao->getInitiativesPerLocationPerStatus();
    $locationInfoMap = Array(); // Array('location_id' => ..., 'name' => ..., 'total' => [count], '[status]' => [count], ...)
    foreach ($initiativesPerLocation as $row) {
        $locationId = $row['location_id'];
        $status = $row['status'];
        $count = $row['count'];
        if (!array_key_exists($locationId, $locationInfoMap)) {
            $location = $locations[$locationId];
            if ($location['parent_id'] > 0) {
                $locationIdString = sprintf("%04d", $locationId);
            } else {
                $locationIdString = sprintf("%02d", $locationId);
            }
            $locationInfoMap[$locationId] = Array('location_id' => $locationIdString, 'name' => $location['name'], 'total' => 0);
        }
        $locationInfoMap[$locationId]['total'] += $count;
        $locationInfoMap[$locationId][$status] = $count;
    }
    
    $users = $app->dao->getAllUsers();
    $siteAdminCount = 0;
    $moderatorCount = 0;
    $userCount = 0;
    foreach ($users as $user) {
        if ($user['isValidated']) {
            if ($user['isSiteAdmin']) {
                $siteAdminCount++;
            } else if ($user['isModerator']) {
                $moderatorCount++;
            } else {
                $userCount++;
            }
        }
    }
    
?>
<div id="main">
<h1>Statistikk</h1>

<h2>Initiativer per status</h2>
<table class="stats-table">
    <tr><th style="text-align: left;">Status</th><th>Antall</th></tr>
    <?php
        foreach ($statusInfoMap as $status => $info) {
            $title = $info['title'];
            $count = $info['count'];
            echo '<tr><td>' . $title . '</td><td style="text-align: right;">' . $count . '</td></tr>';
        }
        echo '<tr><td>Totalt</td><td style="text-align: right;">' . $totalInitiatives . '</td></tr>';
    ?>
</table>

<h2 style="margin-top: 40px;">Initiativer per kommune/fylke</h2>
<table class="stats-table colwidth">
<?php
    $i = 0;
    foreach ($locationInfoMap as $locationId => $info) {
        if ($i % 19 == 0) {
            echo '<tr><td colspan="2"></td><th><img src="/static/images/status-total.png"/></th>';
            foreach ($statusInfoMap as $status => $statusInfo) {
                echo '<th><img src="/static/images/status-' . $status . '.png"/></th>';
            }
            echo '</tr>';
        }
        echo '<tr><th>' . $info['location_id'] . '</th><th>' . $info['name'] . '</th><td>' . $info['total'] . '</td>';
        foreach ($statusInfoMap as $status => $statusInfo) {
            echo '<td>';
            if (array_key_exists($status, $info)) {
                echo $info[$status];
            }
            echo '</td>';
        }
        echo '</tr>';
        $i++;
    }
?>
</table>

<h2 style="margin-top: 40px;">Brukere</h2>
<table class="stats-table">
    <tr><th>Vanlige brukere:</th><td><?php echo $userCount; ?></td></tr>
    <tr><th>Moderatorer:</th><td><?php echo $moderatorCount; ?></td></tr>
    <tr><th>Administratorer:</th><td><?php echo $siteAdminCount; ?></td></tr>
    <tr><th>Totalt:</th><td><?php echo $userCount + $moderatorCount + $siteAdminCount; ?></td></tr>
</table>

</div>