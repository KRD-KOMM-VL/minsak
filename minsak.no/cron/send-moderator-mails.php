<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php
/*
 * Send mail to all moderators that have items in need of moderation 
 */

include('../lib/init.php');
global $app;

// check if moderation is required
$moderationRequired = $app->dao->getModerationRequired();

if ($moderationRequired) {
    
    // send emails to all site admins (site admins can moderate anything)
    $siteAdmins = $app->dao->getSiteAdmins();
    if (is_array($siteAdmins)) {
        foreach ($siteAdmins as $siteAdmin) {
            $magic = $app->generateUserAccessKey($siteAdmin);
            Mail::sendSiteAdminModerationMail($siteAdmin, $magic);
        }
    }
    
    // send emails to all users with moderation tasks waiting
    $moderators = $app->dao->getUsersWithModerationTasks();
    if (is_array($moderators)) {
        foreach ($moderators as $moderator) {
            $magic = $app->generateUserAccessKey($moderator);
            Mail::sendModeratorMail($moderator, $magic);
        }
    }
}
