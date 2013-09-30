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
$initiative = $app->getMapValue($data, 'initiative');
$initiativeOwner = $app->dao->getUserById($initiative['user_id']);

echo '<div id="main">';
$user = $app->user;
$isSiteAdmin = $user['isSiteAdmin'];

if (!$isSiteAdmin) {
	$app->fail(404, 'Siden finnes ikke');
}

$isCommit = false;
$initiativeUpdated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    $key = 'initiative_' . $initiative['id'];
    $action = array_key_exists($key, $_POST) ? $_POST[$key] : false;
    $initiativeStatus = Array(
        'initiative_id' => $initiative['id'],
        'prev_status' => $initiative['status'],
        'change_time' => time(),
        'user_id' => $app->user['id']
    );
    switch ($action) {
    	case 'reject':
    		$initiativeUpdated = true;
    		$initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_REJECTED;
            Mail::sendInitiativeRejectedEmail($initiative, $initiativeOwner['username']);
    		break;
    	case 'accept':
    		$initiativeUpdated = true;
    		$initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_OPEN;
    		Mail::sendInitiativeOpenedEmail($initiative, $initiativeOwner['username']);
    		break;
    }

    if ($initiativeUpdated) {
    	$app->dao->addInitiativeStatus($initiativeStatus);
    	$app->dao->updateInitiative($initiative);
        $siteAdmins = $app->dao->getSiteAdmins();
    	if (is_array($siteAdmins)) {
    		foreach ($siteAdmins as $siteAdmin) {
    			$magic = $app->generateUserAccessKey($siteAdmin);
    			Mail::sendSiteAdminInitiativeChangedStatus($siteAdmin, $initiative, $initiativeStatus['prev_status'], $initiativeStatus['current_status'], $magic);
    		}
    	}
    	echo '<h1>Saken er oppdatert</h1>';
    }
}

echo '<form action="/moderer/sak/' . $initiative['id'] . '" method="post">';

echo '<h1>Moderer initiativ (nåværende status: "' . $initiative['status'] . '")</h1>';
echo '<ul style="padding-left: 0px;">';
$app->renderFragment('widget/moderate-initiative-summary', Array('initiative' => $initiative));
echo '</ul>';
echo '<input type="submit" name="submit" value="Oppdater" />';

echo '</form>';
echo '</div>';
