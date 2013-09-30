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
 * Widget:
 * Breadcrumbs
 * 
 * 
 */

global $app;

?>
<div class="breadcrumbs">
	<span>Du er her:</span>
    <ul>
    	<?php 
    	
    	$num_crumbs = count($app->breadcrumbs);
    	$c = 1;
    	foreach ($app->breadcrumbs as $crumb) {
    		if ($c == $num_crumbs || !isset($crumb['link'])) { // last element is never a link
    			echo "<li>{$crumb['label']}</li>\n";
    		} else {
    			echo "<li><a href=\"{$crumb['link']}\">{$crumb['label']}</a></li>\n";
    		}
    		$c++;
    	}
    	?>
	</ul>
</div>