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
$comment = $app->getMapValue($data, 'comment');

echo '<div id="main">';
$user = $app->user;
$isSiteAdmin = $user['isSiteAdmin'];

if (!$isSiteAdmin) {
	$app->fail(404, 'Siden finnes ikke');
}

$isCommit = false;
$commentUpdated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    $key = 'comment_' . $comment['id'];
    $action = array_key_exists($key, $_POST) ? $_POST[$key] : false;
    switch ($action) {
    	case 'reject':
    		$commentUpdated = true;
    		$comment['status'] = AppContext::COMMENT_STATUS_REJECTED;
    		break;
    	case 'accept':
    		$commentUpdated = true;
    		$comment['status'] = AppContext::COMMENT_STATUS_ACCEPTED;
    		break;
    }

    if ($commentUpdated) {
    	$app->dao->updateComment($comment);
    	echo '<h1>Kommentaren er oppdatert</h1>';
    }
}

echo '<form action="/moderer/kommentar/' . $comment['id'] . '" method="post">';

echo '<h1>Moderer kommentar (nåværende status: "' . $comment['status'] . '")</h1>';
echo '<ul style="padding-left: 0px;">';
$app->renderFragment('widget/moderate-comment-summary', Array('comment' => $comment));
echo '</ul>';
echo '<input type="submit" name="submit" value="Oppdater" />';

echo '</form>';
echo '</div>';
