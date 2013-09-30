<?php 
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
*/


?><?php

// Per-deployment configuration
if (array_key_exists('SERVER_NAME', $_SERVER)) {
    $serverName = $_SERVER['SERVER_NAME'];
    $cli = false;
} else {
    $serverName = php_uname('n').'-'.basename(dirname(__FILE__)); // Also use dirname, since prod and staging may be on same host
    $cli = true;
}
switch ($serverName) {
    // add staging or development settings here...
    default:
    	// production settings
    	ini_set('display_errors', 0);
    	
    	define('BASE_URL', die('CONFIG_VALUE_MISSING'));
    	define('DOMAIN', die('CONFIG_VALUE_MISSING'));
    	 
        define('DB_HOST', die('CONFIG_VALUE_MISSING'));
        define('DB_NAME', die('CONFIG_VALUE_MISSING'));
        define('DB_USER', die('CONFIG_VALUE_MISSING'));
        define('DB_PASS', die('CONFIG_VALUE_MISSING'));
        define('MAIL_FROM_ADDRESS', die('CONFIG_VALUE_MISSING'));
        
        define('FACEBOOK_APP_ID', die('CONFIG_VALUE_MISSING'));
        
        define('FB_TAB_URL', die('CONFIG_VALUE_MISSING'));

        define('FLICKR_APP_KEY',    die('CONFIG_VALUE_MISSING'));
        define('FLICKR_APP_SECRET', die('CONFIG_VALUE_MISSING'));
        
        define('RECAPTCHA_PUBLIC_KEY',  die('CONFIG_VALUE_MISSING'));
        define('RECAPTCHA_PRIVATE_KEY', die('CONFIG_VALUE_MISSING'));
        
        define('LOG_SQL', false);
        break;
}

// Used for hmac'ing password hashes
define ('PASSWORD_SITE_KEY', die('CONFIG_VALUE_MISSING'));

// Max age of a user magic key to be legal (in seconds)
define ('MAGIC_KEY_LIFE_TIME', 24 * 60 * 60);
// Max age of a magic key for it to be reused (in seconds)
define ('MAGIC_KEY_REUSE_TIME', 2 * 60 * 60);
// Max age of a user magic key to be remembered
define ('MAGIC_KEY_REMEMBER_TIME', 7 * 24 * 60 * 60);
// Max number of magic keys to keep for a user
define ('MAGIC_KEY_MAX_COUNT', 3);

define('LOGS_DIR', BASEDIR . '/logs');
define('LOG_FILE', LOGS_DIR . '/app.log');
define('IMAGE_UPLOAD_DIR', BASEDIR . '/webroot/uploads/');
define('IMAGE_UPLOAD_DIR_URL_BASE', '/uploads/');

