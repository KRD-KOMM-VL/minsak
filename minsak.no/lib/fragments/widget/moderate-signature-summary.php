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
$signature = $data['signature'];
$moderated = $signature['moderated'];
?>
<li style="list-style:none; padding: 10px 10px; margin: 4px 0px; background-color: #ffffff;">
    <span style="display:block; padding-bottom: 5px; margin-bottom: 5px; border-bottom: 2px solid #e7e7e7;">
        <strong>Moderer:</strong>
        <?php
        switch ($moderated) {
        	case 'accepted':
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="wait" checked="checked"/>Godkjenn';
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="reject"/>Forkast';
        		break;
        	case 'rejected':
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="accept"/>Godkjenn';
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="wait" checked="checked"/>Forkast';
        		break;
        	default:
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="accept"/>Godkjenn';
        		echo '<input type="radio" name="signature_' . $signature['id'] . '" value="reject"/>Forkast';
        		break;
        }
        ?>
    </span>
    <ul>
    	<li><?php echo htmlspecialchars($signature['name']); ?></li>
        <li><?php echo htmlspecialchars($signature['address1']); ?></li>
        <?php if ($signature['address2']) { ?><li><?php echo htmlspecialchars($signature['address2']); ?></li><?php } ?>
        <li><?php echo htmlspecialchars($signature['area_code']); ?></li>
    </ul>
</li>
