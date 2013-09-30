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
$initiative = $data['initiative'];
$imageUrl = $app->getInitiativeImageURL($initiative, AppContext::INITIATIVE_IMAGE_SIZE_SMALL);
$locInfo = $app->dao->getLocationExtendedInfoById($initiative['location_id']);
$sigReq = $locInfo['signatures_required'];
$initiativeUrl = $app->getInitiativeCanonicalURL($initiative['id']);
?>
<li style="list-style:none; padding: 10px 10px; margin: 4px 0px; background-color: #ffffff;">
    <span style="display:block; padding-bottom: 5px; margin-bottom: 5px; border-bottom: 2px solid #e7e7e7;">
    <?php if ($initiative['status'] == AppContext::INITIATIVE_STATUS_UNMODERATED) { ?>
        <strong>Moderer nytt initiativ:</strong>
        <input type="radio" name="initiative_<?php echo $initiative['id']; ?>" value="accept"/>Godkjenn
        <input type="radio" name="initiative_<?php echo $initiative['id']; ?>" value="reject"/>Forkast
        <input type="radio" checked="checked" name="initiative_<?php echo $initiative['id']; ?>" value="wait"/>Ingen endring
    <?php } else { ?>
        <strong>Moderer initiativ:</strong>
        <?php if ($initiative['status'] == AppContext::INITIATIVE_STATUS_REJECTED) { ?>
        <input type="radio" name="initiative_<?php echo $initiative['id']; ?>" value="accept"/>Godkjenn
        <?php } else { ?>
        <input type="radio" name="initiative_<?php echo $initiative['id']; ?>" value="reject"/>Forkast
        <?php } ?>
        <input type="radio" checked="checked" name="initiative_<?php echo $initiative['id']; ?>" value="wait"/>Ingen endring
    <?php } ?>
    </span>
    <a href="<?php echo htmlspecialchars($initiativeUrl);?>"><img src="<?php echo htmlspecialchars($imageUrl); ?>" width="195" height="140" alt="<?php echo htmlspecialchars($initiative['title']); ?>" /></a>
    <br/>
    <?php echo htmlspecialchars($initiative['text']); ?>
    <ul class="info-list">
    	<li class="title"><?php echo htmlspecialchars($initiative['title']); ?></li>
    	<li class="place"><?php echo htmlspecialchars($locInfo['extra']['name']);  ?></li>
    </ul>
</li>
