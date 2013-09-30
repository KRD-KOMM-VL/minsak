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
$username = $app->getMapValue($data, 'username', '');
$redirect = $app->getMapValue($data, 'redirect', '/');
$register = $app->getMapValue($data, 'register');
$cancel = $app->getMapValue($data, 'cancel');
?>
<form action="/login" method="post">
<table>
<tr><td><?php echo $app->_('Brukernavn/e-post'); ?>:</td><td><input type="text" name="username" value="<?php echo $app->getEscapedRequestValue('username', $username); ?>"/></td></tr>
<tr><td>Passord:</td><td><input type="password" autocomplete="off" name="password" value="<?php echo $app->getEscapedPostValue('password'); ?>"/></td></tr>
</table>
<input type="hidden" name="redirect" value="<?php echo $app->getEscapedRequestValue('redirect', $redirect); ?>"/>
<input type="submit" name="submit" value="Logg inn" />
</form>
<?php if ($register) { ?>
<ul>
<li><a href="/forgot-password" class="styled-link"><?php echo $app->_('Glemt passordet'); ?>?</a></li>
<li><a href="/profil" class="styled-link"><?php echo $app->_('Registrer ny bruker'); ?></a></li>
<?php if ($cancel) { ?>
<li><a href="/" class="styled-link">Avbryt</a></li>
<?php } ?>
</ul>
<?php } else { ?>
<a href="/forgot-password" class="styled-link"><?php echo $app->_('Glemt passordet'); ?>?</a>
<?php } ?>