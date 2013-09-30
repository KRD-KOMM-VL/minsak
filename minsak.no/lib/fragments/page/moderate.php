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
$user = $app->user;
$isSiteAdmin = $user['isSiteAdmin'];
$isModerator = $user['isModerator'];
$locations = $app->dao->getLocations();
if ($isSiteAdmin) {
    $initiatives = $app->dao->getPendingInitiatives();
} else {
    $initiativeModeratorLocationIds = Array();
    $signatureModeratorLocationIds = Array();
    $userRoles = $app->dao->getUserRoles($app->user);
    foreach ($userRoles as $userRole) {
        if ($userRole['initiative_moderator']) {
            $initiativeModeratorLocationIds[] = $userRole['location_id'];
        }
    }
    if ($initiativeModeratorLocationIds) {
    	$initiatives = $app->dao->getPendingInitiatives($initiativeModeratorLocationIds);
    } else {
    	$app->fail(404, 'Siden finnes ikke');
    }
}

$isCommit = false;
$initiativeModifiedCount = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    foreach ($_POST as $key => $value) {
        $matches = Array();
        if (preg_match('/^initiative_(\d+)$/', $key, $matches) > 0) {
            $initiativeId = $matches[1];
            if (array_key_exists($initiativeId, $initiatives)) {
                $initiative = $initiatives[$initiativeId];
                $initiativeOwner = $app->dao->getUserById($initiative['user_id']);
                $initiativeUpdated = true;
                $initiativeStatus = Array(
                    'initiative_id' => $initiative['id'],
                    'prev_status' => $initiative['status'],
                    'change_time' => time(),
                    'user_id' => $app->user['id']
                );
                // switch over legal status changes
                switch ($initiative['status'] . '_' . $value) {
                    case AppContext::INITIATIVE_STATUS_UNMODERATED . '_accept' :
                        $initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_OPEN;
                        Mail::sendInitiativeOpenedEmail($initiative, $initiativeOwner['username']);
                        break;
                    case AppContext::INITIATIVE_STATUS_UNMODERATED . '_reject' :
                        $initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_REJECTED;
                        Mail::sendInitiativeRejectedEmail($initiative, $initiativeOwner['username']);
                        break;
                    case AppContext::INITIATIVE_STATUS_SCREENING . '_complete' :
                        $initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_COMPLETED;
                        break;
                    default:
                        $initiativeUpdated = false;
                        break;
                }
                if ($initiativeUpdated) {
                    $app->dao->addInitiativeStatus($initiativeStatus);
                    $app->dao->updateInitiative($initiative);
                    unset($initiatives[$initiativeId]); // remove from list
                    $initiativeModifiedCount++;
                }
            }
        }
    }
    
    echo '<h1>Oppdatert ' . $initiativeModifiedCount . ' saker</h1>';
}

echo '<form action="/moderer" method="post">';

if (count($initiatives)) {
    echo '<h1>Moderer initiativer</h1>';
    echo '<ul style="padding-left: 0px;">';
    foreach ($initiatives as $initiative) {
        $app->renderFragment('widget/moderate-initiative-summary', Array('initiative' => $initiative));
    }
    echo '</ul>';
    echo '<input type="submit" name="submit" value="Oppdater" />';
} else {
    echo '<h1>Ingenting å moderere</h1>';
}

echo '</form>';
echo '</div>';
