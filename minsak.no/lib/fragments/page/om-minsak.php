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
 * Om minsak.no
 * 
 */

global $app;
?>
<!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
		<div class="area">
			<h1>Hvem står bak minsak.no?</h1>
			<p>Minsak.no er utviklet i regi av Kommunal- og regionaldepartementet. Målet er at du lettere skal kunne foreslå saker for din kommune eller fylkeskommune. Dette er en rett du har, som er lovfestet i kommuneloven § 39a. <a href="#retningslinjer" class="open-popup">Les om retningslinjer her</a>.</p>
			<p>Vi oppfordrer alle til å vise nettvett og utøve god debattkultur på disse sidene. Redaktøren forbeholder seg retten til å slette innlegg og saker av krenkende karakter.</p>

            <h1>Formål med siden</h1>
            <p>Bidra til et levende lokaldemokrati og at innbyggerne kan legge fram saker for kommunestyret som er viktige for dem.</p>

            <h1>Egen Facebook-applikasjon</h1>
            <p>Minsak.no kan også integreres på kommunale og fylkeskommunale Facebook-sider gjennom en egen applikasjon. <a class="styled-link" target="_blank" href="<?php echo FB_TAB_URL; ?>">Denne finner du her</a>.</p>

            <a name="vilkaar" id="vilkaar"></a>
            <h1>Vilkår for bruk av minsak.no</h1>
            <p>For å legge ut innhold på nettstedet må du oppgi navnet ditt.</p> 
            <p>Du er selv ansvarlig for at egne innlegg er i samsvar med disse retningslinjene.</p>
            <p>Opptre med respekt for andre. Sørg for å holde deg til emnet for diskusjonen. Redaktøren forbeholder seg retten til å fjerne innlegg fra nettstedet, for eksempel:</p> 
            <ul>
                <li>Personangrep og ærekrenkelser</li>
                <li>Banning eller pornografiske ytringer</li>
                <li>Truende opptreden</li>
                <li>Krenkende utsagn knyttet til kjønn, etnisk opprinnelse eller politisk, religiøst eller kulturelt ståsted</li>
                <li>Bruk av andres åndsverk uten tillatelse</li>
                <li>Spam eller reklame</li>
            </ul>
            <p>Om du ser innlegg eller kommentarer som du mener bryter med disse retningslinjene gi beskjed til redaktøren.</p>
            <p>Forslag som legges ut kan fjernes uten varsel dersom de kan oppfattes som krenkende.</p>
            <p>Forslag kan også fjernes uten varsel dersom de er i strid med formål og retningslinjer for siden.</p>
            <p>Forslag fremmet av innbyggere som ikke bor i kommunen eller fylkeskommunen forslaget er rettet til kan også bli slettet uten varsel.</p>
            <p>Du må være oppmerksom på at innhold du offentliggjør på nettstedet blir tilgjengelig for en stor lesergruppe. Innlegg med trusler og oppfordringer til voldelige handlinger, eller som kan være straffbare, vil bli sendt Politiets sikkerhetstjeneste til vurdering sammen med automatisk innhentet informasjon som er knyttet til innlegget.</p>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>
	</div>
</div>