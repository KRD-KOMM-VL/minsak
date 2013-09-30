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
 * Menu block for left hand side main menu
 * 
 * Typically rendered like this
 * <div id="sidebar">
 * 	 <!-- add-nav -->
 * 	 <?php $app->renderFragment('widget/menu');?>
 * </div>
 * 
 */

global $app;
?>
<ul class="add-nav">
	<li class="home"><span><a accesskey="r" tabindex="14" href="/">						<?php echo $app->_('Hjem'); ?></a></span></li>
	<li class="proposed"><span><a accesskey="y" tabindex="16" href="/rediger-sak">		<?php echo $app->_('Foreslå en sak'); ?></a></span></li>
	<li class="privacy"><span><a class="open-popup" accesskey="u" tabindex="17" href="#retningslinjer">	<?php echo $app->_('Retningslinjer'); ?></a></span></li>
    <?php
    $location = $app->currentLocation;
    if ($location) {
        echo '<li class="more"><span><a accesskey="i" tabindex="18" href="/sok?locationId=' . $location['id'] . '">' . $app->_('Se saker') . '</a></span></li>';
    } else {
    	echo '<li class="more"><span><a accesskey="i" tabindex="18" href="/sok">' . $app->_('Se saker') . '</a></span></li>';
    }
    if ($app->userIsSiteAdmin()) {
        echo '<li class="more"><span><a accesskey="s" tabindex="19" href="/statistikk">Statistikk</a></span></li>';
    }
    if (!$location && array_key_exists('locationId', $_REQUEST)) {
        $location = $app->dao->getLocationById($_REQUEST['locationId']);
    }
    if ($location && $location['web_address']) {
        $menutext = $location['name'] . ($location['parent_id'] ? ' kommune' : ' fylke');
        $menulink = $location['web_address'];
    ?>
        <li class="location"><span><a accesskey="l" tabindex="19" target="_blank" href="<?php echo $menulink; ?>"><?php echo $menutext; ?></a></span></li>
    <?php
    }
    ?>
</ul>
