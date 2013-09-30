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
$signatures = $app->dao->getModeratedSignaturesByInitiativeId($initiative['id']);
// $signaturesPerPage = 100;
// $lastPage = ceil(count($signatures) / $signaturesPerPage); 
// $page = 1;
// if (array_key_exists(2, $app->urlParts)) {
//     $page = min($lastPage, max(1, intval($app->urlParts[2])));
// }
// $signatures = array_slice($signatures, ($page - 1) * $signaturesPerPage, $signaturesPerPage);
$midPoint = ceil(count($signatures) / 2);
?>

<!-- main -->
<div id="main">
    <!-- content -->
    <div id="content">
        <!-- content-area -->

        <div class="content-area">
            <h1 style="margin-bottom: 10px;">Underskrifter til <?php echo htmlspecialchars($initiative['title']); ?></h1>
            <div class="separator-after" style="padding-bottom: 10px;"><a href="/sak/<?php echo $initiative['id']; ?>"><?php echo $app->_('Tilbake til saken'); ?></a></div>
            <div class="two-column left">
                <?php
                    for ($i = 0; $i < $midPoint; $i++) {
                        echo '<div class="signature">' . htmlspecialchars($signatures[$i]['name']) . '</div>';
                    }
                ?>
            </div>
            <div class="two-column right">
                <?php
                    for ($i = $midPoint; $i < count($signatures); $i++) {
                        echo '<div class="signature">' . htmlspecialchars($signatures[$i]['name']) . '</div>';
                    }
                ?>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>

    <!-- sidebar -->
    <div id="sidebar">
        <!-- add-nav -->
        <?php $app->renderFragment('widget/menu');?>
    </div>

</div>
