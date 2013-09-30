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
$comment = $data['comment'];
?>
<li style="list-style:none; padding: 10px 10px; margin: 4px 0px; background-color: #ffffff;">
    <span style="display:block; padding-bottom: 5px; margin-bottom: 5px; border-bottom: 2px solid #e7e7e7;">
	    <strong>Moderer kommentar:</strong>
	    <?php if ($comment['status'] == AppContext::COMMENT_STATUS_REJECTED) { ?>
	    <input type="radio" name="comment_<?php echo $comment['id']; ?>" value="accept"/>Godkjenn
	    <?php } else { ?>
	    <input type="radio" name="comment_<?php echo $comment['id']; ?>" value="reject"/>Forkast
	    <?php } ?>
	    <input type="radio" checked="checked" name="comment_<?php echo $comment['id']; ?>" value="wait"/>Ingen endring
    </span>
    <p><?php echo htmlspecialchars($comment['name']); ?></p>
    <p><?php echo htmlspecialchars($comment['text']); ?></p>
</li>
