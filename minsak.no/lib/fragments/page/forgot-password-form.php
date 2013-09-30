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
<div id="main">
<?php
if (array_key_exists('error', $data)) {
    echo 'Kunne ikke sende nytt passord til brukeren. Er du sikker på at du har skrevet epost-adressen riktig?';
}
?>
<h1>Send meg nytt passord</h1>
<form action="/forgot-password" method="post">
Epost: <input type="text" name="username" value="<?php echo $app->getEscapedPostValue('username'); ?>"/>
<input type="hidden" name="redirect" value="<?php echo $app->getEscapedPostValue('redirect'); ?>"/>
<input type="submit" name="submit" value="Lag nytt passord" />
</form>
<a href="/login" class="styled-link">Logg inn</a>
</div>