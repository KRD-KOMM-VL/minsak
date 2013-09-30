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
            <h1>Føreslå ei sak</h1>
            <p>Du må hugse å gjere tydeleg greie for kva du ynskjer at kommunestyret eller fylkestinget skal ta stilling til. Saka bør vere så presist formulert at det er mogeleg å vedta saka slik ho ligg føre. Fyll ut felta nedanfor, vel eit passande bilete og send inn saka.</p>
        </div>
        <!-- info-block -->
        <div class="info-block">
            <h2>Har du lese retningslinene?</h2>
            <p>For at saka di skal kunne visast på misak.no, og deretter handsamast, må du oppfylle visse krav.</p>
            <a tabindex="14" href="/retningslinjer" target="_blank">Les retningslinene</a>
        </div>
    </div>
    <!-- note-row -->
    <div class="note-row">
        <strong>Felt merka med</strong><span class="star">*</span><strong>vert offentleggjort</strong>
    </div>
    <!-- sidebar -->
    <div id="sidebar">
        <!-- add-nav -->
        <?php $app->renderFragment('widget/menu'); ?>
    </div>
</div>
