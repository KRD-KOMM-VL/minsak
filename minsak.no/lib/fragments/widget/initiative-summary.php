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
 * Summary "small square" view of an initiative 
 * 
 */

global $app;
$initiative = $data['initiative'];
$votes = $data['signaturecount'];
$imageUrl = $app->getInitiativeImageURL($initiative, AppContext::INITIATIVE_IMAGE_SIZE_SMALL);
$locInfo = $app->dao->getLocationExtendedInfoById($initiative['location_id']);
$sigReq = $locInfo['signatures_required'];
$votePercent = ($sigReq >= 1) ? min(100,round(100*$votes/$sigReq)) : 0;
$initiativeUrl = $app->getInitiativeCanonicalURL($initiative['id']);
?>
<li>
	<a href="<?php echo htmlspecialchars($initiativeUrl);?>"><img src="<?php echo htmlspecialchars($imageUrl); ?>" width="195" height="140" alt="<?php echo htmlspecialchars($initiative['title']); ?>" /></a>
	<ul class="info-list">
		<li class="title"><a href="<?php echo htmlspecialchars($initiativeUrl);?>"><?php echo htmlspecialchars($initiative['title']); ?></a></li>
		<li class="place"><?php echo htmlspecialchars($locInfo['extra']['name']);  ?></li>
		<li class="vote">
			<div class="vote-bar">
				<span style="width:<?php echo $votePercent ?>%">&nbsp;</span>
				<strong><?php echo htmlspecialchars($votes); ?> av <?php echo htmlspecialchars($sigReq); ?> signaturer</strong>
			</div>
		</li>
	</ul>
</li>
