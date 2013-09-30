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
?>			
<!-- main-area -->
<div class="main-area">
	<h1 class="heading-style" style="margin: 20px 0 5px;">Mine saker</h1>
	<a href="/profil" style="color: #0896A0;">Klikk her for å skifte passord</a>
	<ul class="recents" style="margin-top: 20px;">
	<?php
	$initiatives = $app->dao->getInitiativesByUser($app->user);
	$signatures = $app->dao->getValidSignatureCountsForInitiatives(array_keys($initiatives));
	foreach ($initiatives as $initiative) {
		$signaturecount = $signatures[$initiative['id']];
		$app->renderFragment('widget/initiative-summary', array('initiative' => $initiative, 'signaturecount' => $signaturecount));
	} 
	?>
	</ul>
</div>
