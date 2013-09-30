<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
/**
 * Main page 
 * 
 */

global $app;
?>			
<!-- main -->
<div id="main" style="z-index:500;">
	<!-- content -->
	<div id="content">
        <?php $app->renderFragment('widget/whats-your-initiative'); ?>
        <!-- form-block -->
		<div class="form-block">
		    <div class="row">
		        <h2><?php echo $app->_('Se saker fra<br />ditt hjemsted'); ?></h2>
		        <noscript><a class="more" title="Fylker og kommuner" href="/fylker-og-kommuner">more</a></noscript>
		    </div>
		    <div id="header-location-selector-form-container"></div>
		</div>
        <?php $app->renderFragment('widget/location-selector',Array('elementId'=>'header-location-selector-form-container', 'locationId' => 0)); ?>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>
	</div>
</div>
<!-- main-area -->
<div class="main-area">
	<h2 class="heading"><?php echo $app->_('Aktuelle saker'); ?></h2>
	<!-- recents -->
	<ul class="recents">
	<?php
	$lotsOfInitiatives = $app->dao->getVisibleInitiativesByLocation(100);
	$initiativeIds = array_keys($lotsOfInitiatives);
	$initiatives = Array();
	$initiativeCount = 0;
	$initiativeIdsCount = count($lotsOfInitiatives);
	while ($initiativeCount < 8 && $initiativeIdsCount > 0) {
	    $index = $initiativeCount < 2 ? 0 : rand(0, $initiativeIdsCount - 1); // use the two latest initiatives, then random picks of the rest
	    $initiativeId = $initiativeIds[$index];
	    array_splice($initiativeIds, $index, 1);
	    $initiatives[$initiativeId] = $lotsOfInitiatives[$initiativeId];
	    $initiativeCount++;
	    $initiativeIdsCount--;
	}
	$signatures = $app->dao->getValidSignatureCountsForInitiatives(array_keys($initiatives));
	foreach ($initiatives as $initiative) {
		$signaturecount = $signatures[$initiative['id']];
		$app->renderFragment('widget/initiative-summary', array('initiative' => $initiative, 'signaturecount' => $signaturecount));
	} 
	?>
	</ul>
	<div class="more-holder">
		<a tabindex="27" href="/sok"><?php echo $app->_('Se flere saker'); ?></a>
	</div>
</div>
