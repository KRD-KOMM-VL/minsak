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
echo '<div id="main">';
$user = $app->user;
$isSiteAdmin = $user['isSiteAdmin'];
$isModerator = $user['isModerator'];
$signatures = $app->dao->getSignaturesByInitiativeId($initiative['id']);
$location = $app->dao->getLocationById($initiative['location_id']);
$owner = $app->dao->getUserById($initiative['user_id']);

$initiativeStatus = Array(
    'initiative_id' => $initiative['id'],
    'prev_status' => $initiative['status'],
    'change_time' => time(),
    'user_id' => $app->user['id']
);

echo '<h1>Moderer signaturer for saken<h1>';
echo '<h2>' . htmlspecialchars($initiative['title']) . "</h2>";
echo '<br/>';

$isCommit = false;
$signatureModifiedCount = 0;

$acceptedSignatures = Array();
foreach ($signatures as $signature) {
    if ($signature['moderated'] == AppContext::INITIATIVE_SIGNATURE_MODERATION_ACCEPTED) {
        $acceptedSignatures[] = $signature;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    
    if (($initiative['status'] == AppContext::INITIATIVE_STATUS_SCREENING || $initiative['status'] == AppContext::INITIATIVE_STATUS_OPEN) &&
            array_key_exists('submit', $_POST) && $_POST['submit'] == 'submit' && count($acceptedSignatures) >= $location['signatures_required']) {

        $initiative['status'] = AppContext::INITIATIVE_STATUS_COMPLETED;
        $app->dao->updateInitiative($initiative);
        Mail::sendInitiativeCompletedMail($initiative, $acceptedSignatures, $location['email_address'], $owner['username']);
        Mail::sendInitiativeCompletedReceiptMail($initiative, $acceptedSignatures, $owner['username'], $location['email_address']);
        $initiativeStatus['current_status'] = $initiative['status'];
        $app->dao->addInitiativeStatus($initiativeStatus);

        // send notification to site admins
        $siteAdmins = $app->dao->getSiteAdmins();
        if (is_array($siteAdmins)) {
            foreach ($siteAdmins as $siteAdmin) {
                $magic = $app->generateUserAccessKey($siteAdmin);
                Mail::sendSiteAdminInitiativeChangedStatus($siteAdmin, $initiative, $initiativeStatus['prev_status'], $initiative['status'], $magic);
            }
        }
        
    } else if ($initiative['status'] != AppContext::INITIATIVE_STATUS_COMPLETED) {

        foreach ($_POST as $key => $value) {
            $matches = Array();
            if (preg_match('/^signature_(\d+)$/', $key, $matches) > 0) {
                $signatureId = $matches[1];
                if (array_key_exists($signatureId, $signatures)) {
                    $signature = $signatures[$signatureId];
                    $signatureUpdated = true;
                    switch ($value) {
                        case 'accept':
                            $signature['moderated'] = AppContext::INITIATIVE_SIGNATURE_MODERATION_ACCEPTED;
                            $signature['moderated_user_id'] = $user['id'];
                            break;
                        case 'reject':
                            $signature['moderated'] = AppContext::INITIATIVE_SIGNATURE_MODERATION_REJECTED;
                            $signature['moderated_user_id'] = $user['id'];
                            break;
                        default:
                            $signatureUpdated = false;
                    }
                    if ($signatureUpdated) {
                        $app->dao->updateSignature($signature);
                        unset($signatures[$signatureId]); // remove from list
                        $signatureModifiedCount++;
                    }
                }
            }
        }
        
        echo '<h1>Oppdatert ' . $signatureModifiedCount . ' signaturer</h1>';
        
        if ($signatureModifiedCount > 0) {
            // reload signatures if some are modified
            $signatures = $app->dao->getSignaturesByInitiativeId($initiative['id']);
        }

    }
}

$acceptedSignatureCount = 0;
$newSignatureCount = 0;
$signatureStates = Array();
foreach ($signatures as $signature) {
    // $app->log('-- status = ' . $signature['moderated']);
    if (array_key_exists($signature['moderated'], $signatureStates)) {
        $signatureStates[$signature['moderated']]++;
    } else {
        $signatureStates[$signature['moderated']] = 1;
    }
    if ($signature['moderated'] == AppContext::INITIATIVE_SIGNATURE_MODERATION_ACCEPTED) {
        $acceptedSignatureCount++;
    } else if ($signature['moderated'] == AppContext::INITIATIVE_SIGNATURE_MODERATION_NEW) {
        $newSignatureCount++;
    }
}

// $app->log('-- acceptedSignatureCount = ' . $acceptedSignatureCount);
// $app->log('-- newSignatureCount = ' . $newSignatureCount);

if (($initiative['status'] == AppContext::INITIATIVE_STATUS_SCREENING || $initiative['status'] == AppContext::INITIATIVE_STATUS_OPEN) && $acceptedSignatureCount >= $location['signatures_required']) {
    echo '<h1>' . $app->_('Saken din nok antall underskrifter til å bli sendt inn') . '</h1>';
    echo '<p>' . $app->_('Sjekk om underskriftene kommer fra innbyggerne i din kommune eller fylkeskommune') . '</p>';
    if ($newSignatureCount > 0) {
        echo '<p>Du har flere nye signaturer du kan godkjenne. Det kan være lurt å ta med så mange som mulig, da kommunen/fylket også vil gjøre en avsjekk på om signaturene er gyldige før saken tas opp.';
    }
    echo '<form action="/moderer-signaturer/' . $initiative['id'] . '" method="post">';
    echo '<input type="hidden" name="submit" value="submit" />';
    echo '<input type="submit" name="button" value="Send inn saken til kommunen/fylket" />';
    echo '</form>';
    echo '<br/><br/>';
} else if ($initiative['status'] == AppContext::INITIATIVE_STATUS_SCREENING && ($acceptedSignatureCount + $newSignatureCount) < $location['signatures_required']) {
    // set back to open, so that the owner is noticed when new signatures arrives.
    $initiative['status'] = AppContext::INITIATIVE_STATUS_OPEN;
    $app->dao->updateInitiative($initiative);
    $initiativeStatus['current_status'] = $initiative['status'];
    $app->dao->addInitiativeStatus($initiativeStatus);

    // send notification to site admins
    $siteAdmins = $app->dao->getSiteAdmins();
    if (is_array($siteAdmins)) {
        foreach ($siteAdmins as $siteAdmin) {
            $magic = $app->generateUserAccessKey($siteAdmin);
            Mail::sendSiteAdminInitiativeChangedStatus($siteAdmin, $initiative, $initiativeStatus['prev_status'], $initiative['status'], $magic);
        }
    }
}


if ($initiative['status'] != AppContext::INITIATIVE_STATUS_COMPLETED) {
    echo '<form action="' . $app->url . '" method="post">';
    
    if (count($signatures)) {
        echo $app->_('For hver signatur kan du velge å godkjenne eller å forkaste.');
        
        foreach (Array('new' => 'Nye signaturer', 'accepted' => 'Godkjente signaturer', 'rejected' => 'Avviste signaturer') as $state => $title) {
            if (array_key_exists($state, $signatureStates)) {
                echo '<h1>' . $title . ' (' . $signatureStates[$state] . ')</h1>';
                echo '<ul style="padding-left: 0px;">';
                foreach ($signatures as $signature) {
                    if ($signature['moderated'] == $state) {
                        $app->renderFragment('widget/moderate-signature-summary', Array('signature' => $signature));
                    }
                }
                echo '</ul>';
            }
        }
    }
    
    if (count($signatures) == 0) {
        echo '<h1>Ingen signaturer for denne saken</h1>';
    } else {
        echo '<input type="submit" name="submit" value="Oppdater" />';
    }
    
    echo '</form>';
} else {
    echo '<h1>Saken er sendt inn til kommunen/fylket</h1>';
}

echo '</div>';
