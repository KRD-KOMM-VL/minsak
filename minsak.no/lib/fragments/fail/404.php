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
 * Error page: 404
 * 
 * 
 */

global $app;
?>
<!-- main -->
<div id="main">
	<!-- content -->
	<div id="content">
		<div class="area">
			<h1>404: Siden finnes ikke</h1>
		</div>
	</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>
	</div>
</div>