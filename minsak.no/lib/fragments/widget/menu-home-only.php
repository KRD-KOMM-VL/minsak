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
 * Menu block for left hand side main menu with only home link
 * 
 * Typically rendered like this
 * <div id="sidebar">
 * 	 <!-- add-nav -->
 * 	 <?php $app->renderFragment('widget/menu-home-only');?>
 * </div>
 * 
 */

global $app;
?>
<ul class="add-nav">
	<li class="home"><span><a accesskey="r" tabindex="14" href="/">						<?php echo $app->_('Hjem'); ?></a></span></li>
</ul>
