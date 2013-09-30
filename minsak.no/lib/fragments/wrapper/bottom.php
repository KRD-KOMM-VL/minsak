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
		</div>
	</div>
	<!-- footer -->
	<div id="footer">
		<div class="holder">
			<!-- social -->
			<ul class="social">
				<li><a tabindex="29" class="twitter" target="_blank" title="Del på twitter" href="http://twitter.com/share?url=<?php echo urlencode(BASE_URL); ?>">twitter</a></li>
				<li><a tabindex="30" class="facebook" target="_blank" title="Del på facebook" href="http://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL); ?>">facebook</a></li>
				<li><a tabindex="32" class="google" target="_blank" title="Del på google+" href="https://m.google.com/app/plus/x/?v=compose&content=<?php echo urlencode(BASE_URL); ?>">google+</a></li>
			</ul>
			<p><a tabindex="28" title="Om <?php echo $app->_('Minsak.no'); ?>" href="/om-minsak"><?php echo $app->_('Minsak.no'); ?></a> <?php echo $app->_('- Kommunal- og regionaldepartementet. Ansvarlig redaktør:'); ?> <a href="mailto:postmottak@krd.dep.no">Dag Vestrheim</a>. <a href="/om-minsak#vilkaar">Vilkår for bruk.</a> <a href="/oss">Ofte stilte spørsmål.</a> <a tabindex="34" class="skip" href="#header">Back to top</a></p>
		</div>
	</div>
</body>
</html>
