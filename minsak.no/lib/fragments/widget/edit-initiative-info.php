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

<div id="main">
    <!-- content -->
    <div id="content">
        <div class="area">
            <h1>Foreslå en sak</h1>
            <p>Husk å gjøre tydelig rede for hva du ønsker at kommunestyret eller fylkestinget skal ta stilling til. Saken bør være så presist formulert at det er mulig å vedta saken slik den framstår. Fyll ut feltene under, velg et passende bilde og send inn saken.</p>
        </div>
        <!-- info-block -->
        <div class="info-block">
            <h2>Har du lest retningslinjene?</h2>
            <p>For at saken din kan vises på minsak.no, og deretter kunne behandles, må du oppfylle visse krav.</p>
            <a tabindex="14" href="/retningslinjer" target="_blank">Les retningslinjene</a>
        </div>
    </div>
    <!-- note-row -->
    <div class="note-row">
        <strong>Felt merket med</strong><span class="star">*</span><strong>offentliggjøres</strong>
    </div>
    <!-- sidebar -->
    <div id="sidebar">
        <!-- add-nav -->
        <?php $app->renderFragment('widget/menu-home-only'); ?>
    </div>
</div>
