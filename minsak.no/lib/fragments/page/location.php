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
$location = $app->currentLocation;
?>

<div id="main">
    <!-- content -->
    <div id="content">
        <?php $app->renderFragment('widget/whats-your-initiative'); ?>
        <?php $app->renderFragment('widget/cite'); ?>
    </div>
    <!-- sidebar -->
    <div id="sidebar">
        <!-- add-nav -->
        <?php $app->renderFragment('widget/menu'); ?>
    </div>
</div>
<!-- main-area -->
<div class="main-area">
    <h2 class="heading-plain"><?php echo $app->_('Siste saker fra') . ' ' . $location['name'] . ($app->currentMunicipality ? ' kommune' : ' fylke'); ?></h2>
    <!-- recents -->
    <ul class="recents">
    <?php
    $initiatives = $app->dao->getVisibleInitiativesByLocation(8, $location['id']);
    $signatures = $app->dao->getValidSignatureCountsForInitiatives(array_keys($initiatives));
    foreach ($initiatives as $initiative) {
        $signaturecount = $signatures[$initiative['id']];
        $app->renderFragment('widget/initiative-summary', array('initiative' => $initiative, 'signaturecount' => $signaturecount));
    }
    ?>
    </ul>
    <div class="more-holder">
        <a tabindex="26" href="/sok?locationId=<?php echo htmlspecialchars($location['id']); ?>">Se flere saker</a>
    </div>
</div>
