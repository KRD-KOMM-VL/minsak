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
 * Static page
 * Om misak.no
 * 
 */

global $app;
?>
<!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
		<div class="area">
			<h1>Kven står bak misak.no?</h1>
            <p>Misak.no er utvikla i regi av Kommunal- og regionaldepartementet. Målet er at du lettare skal kunne føreslå saker for din kommune eller fylkeskommune. Dette er ein rett du har, som er lovfesta i kommunelova § 39a. <a href="#retningslinjer" class="open-popup">Les om retningslinjer her</a></p>
            <p>Vi oppmodar alle om å vise ”nettvett” og utøve god debattkultur på desse sidene. Redaktøren har rett til å slette innlegg og saker av krenkande karakter.</p>

            <h1>Formål med sida</h1>
            <p>Bidra til eit levande lokaldemokrati og at innbyggjarane kan legge fram saker for kommunestyret som er viktige for dei.</p>

            <h1>Eigen Facebook-applikasjon</h1>
            <p>Misak.no kan òg integrerast på kommunale og fylkeskommunale Facebook-sider gjennom ein eigen applikasjon. <a class="styled-link" target="_blank" href="http://apps.facebook.com/209567215777834/">Denne finn du her</a></p>

            <a name="vilkaar" id="vilkaar"></a>
            <h1>Vilkår for bruk av misak.no</h1>
            <p>For å leggje ut innhald på nettstaden må du opplysa om namnet ditt.</p>
            <p>Du er sjølv ansvarleg for at eigne innlegg er i samsvar med desse retningslinjene.</p>
            <p>Opptre med respekt for andre. Sørg for å halde deg til emnet for diskusjonen. Redaktøren har rett til å fjerne innlegg frå nettstaden, for eksempel:</p>
            <ul>
                <li>Personangrep og ærekrenkingar</li>
                <li>Banning eller pornografiske ytringar</li>
                <li>Trugande framferd</li>
                <li>Krenkande utsegn knytt til kjønn, etnisk opphav eller politisk, religiøs eller kulturell ståstad</li>
                <li>Bruk av andres åndsverk utan løyve</li>
                <li>Spam eller reklame</li>
            </ul>
            <p>Om du ser innlegg eller kommentarar som du meinar bryt med desse retningslinjene gi beskjed til redaktøren.</p>
            <p>Forslag kan også fjernast utan varsel dersom de er i strid med formål og retningslinjer for sida.</p>
            <p>Forslag som vert lagde ut kan fjernast dersom dei kan oppfattast som krenkande.</p>
            <p>Forslag fremja av innbyggjarar som ikkje bur i kommunen eller fylkeskommunen forslaget er retta til kan også bli sletta utan varsel.</p>
            <p>Du må vere merksam på at innhald du offentleggjer på nettstaden blir tilgjengeleg for ei stor lesargruppe. Innlegg med trugslar og oppfordringar til valdelege handlingar, eller som kan vere straffbare, vil bli sendt Politiets sikkerhetstjeneste til vurdering saman med automatisk innhenta informasjon som er knytt til innlegget.</p>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>
	</div>
</div>