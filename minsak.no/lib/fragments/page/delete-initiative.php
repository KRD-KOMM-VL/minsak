<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
		<!-- content-area -->
		<div class="content-area">

<?php

global $app;

$initiative = $data['initiative'];
$initiativeUrl = '/sak/' . intval($initiative['id']);
$locInfo = $app->dao->getLocationExtendedInfoById($initiative['location_id']);
$imageUrl = $app->getInitiativeImageURL($initiative, AppContext::INITIATIVE_IMAGE_SIZE_FULL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('submit', $_POST)) {

    if ($_POST['submit'] == 'Trekk saken' || $_POST['submit'] == 'Trekk saka') {
        $initiativeStatus = Array(
                'initiative_id' => $initiative['id'],
                'prev_status' => $initiative['status'],
                'change_time' => time(),
            'user_id' => $app->user['id']
        );
        $initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_WITHDRAWN;
        $app->dao->updateInitiative($initiative);
        $app->dao->addInitiativeStatus($initiativeStatus);
        
        // send notification to site admins
        $siteAdmins = $app->dao->getSiteAdmins();
        if (is_array($siteAdmins)) {
            foreach ($siteAdmins as $siteAdmin) {
                $magic = $app->generateUserAccessKey($siteAdmin);
                Mail::sendSiteAdminInitiativeChangedStatus($siteAdmin, $initiative, $initiativeStatus['prev_status'], $initiative['status'], $magic);
            }
        }
        
        echo 'Saken er trukket. <a href="/">Gå tilbake til forsiden</a>.';
        
    } else {
        $app->redirect($initiativeUrl);
    }

} else {
    ?>
	<!-- img-block -->
	<div class="img-block">
		<img src="<?php echo htmlspecialchars($imageUrl);?>" width="320" height="220" alt="<?php echo htmlspecialchars($initiative['title']); ?>" />
		<em class="note"><?php echo htmlspecialchars($initiative['image_credits']); ?></em>
	</div>
	<!-- info -->
	<div class="info">
            
		<h1><?php echo htmlspecialchars($initiative['title']); ?></h1>
		<dl class="meta">
			<dt><?php echo $app->_('Opprettet'); ?>:</dt>
			<dd><?php echo htmlspecialchars(date('d.m.Y', $initiative['created_time'])); ?> av <?php echo htmlspecialchars($initiative['name']); ?></dd>
			<dt><?php echo $app->_('Mottaker'); ?>:</dt>
			<dd><?php echo htmlspecialchars($locInfo['extra']['name']); ?></dd>

			<dt>Sluttdato for signering:</dt>
			<dd><?php echo htmlspecialchars(date('d.m.Y', $initiative['end_date'])); ?></dd>
		</dl>
       
	<div style="padding-bottom:10px; font-size: 116.5%; font-weight:bold;"> 
        <?php echo $app->_('Er du sikker på at du ønsker å trekke denne saken?'); ?>
	</div>
        <form class="send-form" method="post">
            <div class="invisible-js">
                <input id="cancel-withdraw" type="submit" name="submit" value="Avbryt" />
                <input id="withdraw-initiative" type="submit" name="submit" value="<?php echo $app->_('Trekk saken'); ?>" />
            </div>
            <div class="invisible-nonjs button-row">
                <a tabindex="36" class="cancel" href="#" onclick="jQuery('#cancel-withdraw').click(); return false;">Avbryt</a>
                <a style="margin-left:30px;" tabindex="37" class="delete" href="#" onclick="if (confirm('Denne handlingen kan ikke angres. Er du sikker?')) { jQuery('#withdraw-initiative').click(); } return false;"><?php echo $app->_('Trekk saken'); ?></a>
            </div>
        </form>
	</div>
    <?php
}

?>

		</div>
	</div>
    <div id="sidebar">
        <!-- add-nav -->
        <?php $app->renderFragment('widget/menu');?>
    </div>

</div>
