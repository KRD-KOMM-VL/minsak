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
 * Static page:
 * Retningslinjer
 * 
 */

global $app;
?>
<!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
		<div class="area">
            <h1>Retningslinjer</h1> 

            <h2>Hva er innbyggerinitiativ?</h2>
            <p>Ordningen med innbyggerinitiativ er lovfestet i kommuneloven § 39a. Med innbyggerinitiativ menes at innbyggerne i en kommune eller fylke har rett til å sette en sak på den politiske dagsorden i kommunen eller fylkeskommunen. Det er i loven satt visse minimumsvilkår for at kommunestyret eller fylkestinget plikter å behandle saken. Hvis disse vilkårene er oppfylt, må det tas stilling til saken. De som står bak en sak har imidlertid ikke krav på noe bestemt utfall av saken.</p>
            <h2>Hvem kan fremme saker?</h2>
            <p>Det er innbyggerne i kommunen eller fylket som kan fremme saker eller skrive under på en sak. De som står bak saken må ha registrert bostedsadresse i kommunen eller fylket. Også ungdom under stemmerettsalderen kan fremme saker.</p>
            <h2>Hvor mange underskrifter kreves det?</h2>
            <p>For at kommunestyret eller fylkestinget skal ha plikt til å behandle en sak, må et visst antall personer stå bak saken. Saken må enten støttes av minst to prosent av innbyggerne, eller minimum 300 personer i kommunen/500 personer i fylket.</p>
            <h2>Hva kan man fremme saker om?</h2>
            <p>Innbyggerne kan fremme saker som gjelder kommunens eller fylkeskommunens virksomhet. Med dette menes virksomhet som kommunen eller fylkeskommunen rent faktisk er engasjert i, både lovpålagte og frivillige oppgaver. Det er dermed snakk om et vidt spekter av saker.</p>
            <p>Saker kan ikke gjelde sak som allerede er behandlet.</p>
            <p>I samme valgperiode kan det ikke fremmes saker hvis dette har</p>
            <ul>
                <li>samme innhold som et tidligere innbyggerinitiativ</li>
                <li>samme innhold som en sak som er behandlet av kommunestyret (eller fylkestinget) i løpet av valgperioden</li>
            </ul>

            <p>For at man skal ha krav på at kommunestyret eller fylkestinget behandler en sak som allerede har vært behandlet i valgperioden, må man altså vente til en ny valgperiode.</p>
            <p>Dette betyr ikke at det forbudt å fremme ”den samme” saken i løpet av én og samme valgperiode, eller at kommunestyret eller fylkestinget ikke har lov til å behandle saken. Kommunestyret eller fylkestinget kan velge å behandle en identisk sak, men initiativtakerne har imidlertid ikke krav på dette.</p>
            <h2>Når må saken behandles?</h2>
            <p>Kommunestyret eller fylkestinget skal ta stilling til saken senest seks måneder etter at den er fremmet. Det er ett unntak fra denne fristen. Hvis saken henvises til behandling i forbindelse med pågående plansak etter plan- og bygningsloven, gjelder ikke seksmånedersfristen.</p>
            <h2>Hvor lenge kan en sak bli liggende?</h2>
            <p>Saker som ikke har oppnådd tilstrekkelig antall underskrifter etter 12 måneder, vil automatisk bli slettet fra minsak.no.</p>
            <p>Mer informasjon om innbyggerinitiativ:</p>
            <ul>
                <li><a class="styled-link" href="http://www.regjeringen.no/nb/dep/krd/dok/veiledninger_brosjyrer/2004/publikasjonsnummer-h-2149.html?id=272454">Veileder om innbyggerinitiativ</a></li>
                <li><a class="styled-link" href="http://www.lovdata.no/all/tl-19920925-107-007.html#39a">Kommuneloven § 39a Innbyggerinitiativ</a></li>
            </ul>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
	<!-- add-nav -->
	<?php $app->renderFragment('widget/menu');?>
	</div>
</div>