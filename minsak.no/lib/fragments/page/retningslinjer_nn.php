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
            <h1>Retningsliner</h1> 

            <h2>Kva er innbyggjarinitiativ?</h2>
            <p>Ordninga med innbyggjarinitiativ er lovfesta i kommunelova § 39a. Med innbyggjarinitiativ meinast at innbyggjarane i ein kommune eller eit fylke har rett til å setje ei sak på den politiske dagsorden i kommunen eller fylkeskommunen. Det er i lova sett visse minimumsvilkår for at kommunestyret eller fylkestinget plikter å handsame saka. Dersom desse vilkåra er stetta, må det takast stilling til saka. Dei som står bak ei sak har ikkje krav på noko bestemt utfall av saka.</p>
            <h2>Kven kan fremje saker?</h2>
            <p>Det er innbyggjarane i kommunen eller fylket som kan fremje saker eller skrive under på ei sak. Dei som står bak saka må ha registrert bustadsadresse i kommunen eller fylket. Ungdom under røysterettsalder kan òg fremje saker.</p>
            <h2>Kor mange underskrifter vert det kravd?</h2>
            <p>For at kommunestyret eller fylkestinget skal ha plikt til å handsame ei sak, må ei viss mengd personar stå bak saka. Saka må anten verte støtta av minst to prosent av innbyggjarane, eller minimum 300 personar i kommunen / 500 personar i fylket.</p>
            <h2>Kva kan ein fremje saker om?</h2>
            <p>Innbyggjarane kan fremje saker som gjeld kommunen eller fylkeskommunen si verksemd. Med dette meiner ein verksemd som kommunen eller fylkeskommunen faktisk er engasjert i, både lovpålagde og friviljuge oppgåver. Det er såleis snakk om eit vidt spekter av saker.</p>
            <p>Saker kan ikkje gjelde sak som allereie er handsama.</p>
            <p>I same valperiode kan det ikkje fremjast saker dersom dette har</p>
            <ul>
                <li>same innhald som eit tidlegare innbyggjarinitiativ</li>
                <li>same innhald som ei sak som er handsama av kommunestyret (eller fylkestinget) i valperioden</li>
            </ul>

            <p>For at ein skal ha krav på at kommunestyret eller fylkestinget handsamar ei sak som allereie har vore handsama i valperioden, må ein difor vente til ein ny valperiode.</p>
            <p>Dette inneber ikkje at det er forbode å fremje ”den same” saka i ein og same valperiode, eller at kommunestyret eller fylkestinget ikkje har lov til å handsame saka. Kommunestyret eller fylkestinget kan velje å handsame ei identisk sak, men initiativtakarane har ikkje krav på dette.</p>
            <h2>Når må saka handsamast?</h2>
            <p>Kommunestyret eller fylkestinget skal ta stilling til saka seinast seks månader etter at ho er fremja. Det er eitt unnatak frå denne fristen. Dersom saka vert send over til handsaming i samband med pågåande plansak etter plan- og bygningslova, gjeld ikkje seksmånadersfristen.</p>
            <h2>Kor lenge kan ei sak bli liggjande?</h2>
            <p>Saker som ikkje har oppnådd tilstrekkeleg tal på underskrifter etter 12 månader, vil automatisk bli sletta frå misak.no.</p>
            <p>Meir informasjon om innbyggjarinitiativ:</p>
            <ul>
                <li><a class="styled-link" href="http://www.regjeringen.no/nb/dep/krd/dok/veiledninger_brosjyrer/2004/publikasjonsnummer-h-2149.html?id=272454">Rettleiing om innbyggjarinitiativ</a></li>
                <li><a class="styled-link" href="http://www.lovdata.no/all/tl-19920925-107-007.html#39a">Kommunelova § 39a Innbyggjarinitiativ</a></li>
            </ul>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
	<!-- add-nav -->
	<?php $app->renderFragment('widget/menu');?>
	</div>
</div>