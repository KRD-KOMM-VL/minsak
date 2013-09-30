<?php 
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php 

class AppContext
{
    /**
     * Initiative is a draft and is only visible/editable by the initiative creator
     * When the creator submits the initiative, it will become 'unmoderated' if the location requires moderation, or 'open' otherwise
     */
    const INITIATIVE_STATUS_DRAFT = 'draft';
    
    /**
     * Initiative is submitted by the creator, but not visible until manually moderated.
     * If a moderator declines the initiative, status will become 'rejected', otherwise it will become 'open'
     */
    const INITIATIVE_STATUS_UNMODERATED = 'unmoderated';
    
    /**
     * Initiative is rejected
     */
    const INITIATIVE_STATUS_REJECTED = 'rejected';
    
    /**
     * Initiative is open for everyone to see and sign. When enough signatures are collected it will become 'screening'
     */
    const INITIATIVE_STATUS_OPEN = 'open';
    
    /**
     * Initiative is open for everyone to see and sign while being screened for valid signatures.
     * If accepted, it will become 'completed', otherwise back to 'open'
     */
    const INITIATIVE_STATUS_SCREENING = 'screening';
    
    /**
     * Initiative is completed and eligible for being processed by the city council or appropriate authorities
     * It is visible, but not signable in this state
     */
    const INITIATIVE_STATUS_COMPLETED = 'completed';
    
    /**
     * Initiative is deleted
     */
    const INITIATIVE_STATUS_WITHDRAWN = 'withdrawn';
    

    /**
    * Initiative image of type missing (no image)
    */
    const INITIATIVE_IMAGE_TYPE_MISSING = 'missing';
    
    /**
     * Initiative image of type local file (user upload)
     */ 
    const INITIATIVE_IMAGE_TYPE_LOCAL = 'local';

    /**
    * Initiative image of type flickr cache
    */
    const INITIATIVE_IMAGE_TYPE_FLICKR = 'flickr';

    /**
    * Initiative image size: full image
    */
    const INITIATIVE_IMAGE_SIZE_FULL = 'full';

    /**
    * Initiative image size: small image
    */
    const INITIATIVE_IMAGE_SIZE_SMALL = 'small';
    
    /**
    * Initiative signature moderation status for new signatures (not yet moderated)
    */
    const INITIATIVE_SIGNATURE_MODERATION_NEW = 'new';
    /**
    * Initiative signature moderation status for accepted signatures
    */
    const INITIATIVE_SIGNATURE_MODERATION_ACCEPTED = 'accepted';
    /**
    * Initiative signature moderation status for rejected signatures
    */
    const INITIATIVE_SIGNATURE_MODERATION_REJECTED = 'rejected';
    
    
    /**
     * Comment is accepted (default)
     */
    const COMMENT_STATUS_ACCEPTED = 'accepted';
    
    /**
     * Comment is rejected
     */
    const COMMENT_STATUS_REJECTED = 'rejected';
    
    
    /**
     * Request uri
     * @var String
     */
    public $url = null;
    
    /**
     * Request uri split by '/' as an array
     * @var Array
     */
	public $urlParts = array();

	/**
	 * The page title
	 * @var String
	 */
	public $pageTitle = 'Minsak.no';
	
	/**
	 * Extra html head content
	 * @var String
	 */
	public $htmlHeadExtra = '';
	
	/**
	 * Reference to the Dao
	 * @var Dao
	 */
	public $dao = null;
	
    /**
     * The current municipality as an associative array if present, null otherwise
     * @var Array
     */
	public $currentMunicipality = null;
	
    /**
     * The current county as an associative array if present, null otherwise
     * @var Array
     */
	public $currentCounty = null;
	
	/**
	 * The current location as an associative array if present, null otherwise
	 * This can be either a county or a municipality
	 * @var Array
	 */
	public $currentLocation = null;
	
	/**
	 * 
	 * @var Array
	 */
	public $cookieLocation = null;
	
	/**
	 * The current user as an associative array if present, null otherwise
	 * @var Array
	 */
	public $user = null;
	
	/**
	 * Set to true if a session is started (this is only done if a user is logged in)
	 * @var boolean
	 */
	public $sessionStarted = false;
	
	/**
	 * The current language used
	 * @var String
	 */
	public $currentLanguage = 'nb';
	
	/**
	 * Value that is set to true when language has been set either from cookie, request parameters or location default
	 * @var boolean
	 */
	public $currentLanguageSet = false;
	
	/**
	 * The array of allowed languages as map from language code to language name
	 * @var Array
	 */
	public $languages = Array(
        'nb' => 'Bokmål',
        'nn' => 'Nynorsk',
    );
	
	/**
	 * The array of translations as map from default value to translated value.
	 * @see loadLocaleFile
	 * @var Array
	 */
    public $translations = Array();
    
    /**
     * Breadcrumbs as an array of array('label' => ..., 'link' => ...)
     * @var Array
     */
    public $breadcrumbs = Array();
	
    
	/**
	 * Constructor
	 */
	public function __construct() 
	{
	    /* @var $a type */
	    $this->dao = new Dao(new DataAccess($this), $this);
		$req = '';
		if (php_sapi_name() != 'cli') {
			if (array_key_exists('REQUEST_URI', $_SERVER)) {
				$req = $_SERVER['REQUEST_URI'];
				$queryIdx = strpos($req, '?');
				if ($queryIdx !== FALSE) {
					$req = substr($req, 0, $queryIdx);
				}
			}
			$this->url = $req;
			//$this->log("Request for " . $req);
			$this->urlParts = explode('/', $req);
			array_shift($this->urlParts);
			
			// Common facebook opengraph tags for all pages
			$this->htmlHeadExtra .= '<meta property="og:site_name" content="Minsak" />'
			                       .'<meta property="fb:app_id" content="'.FACEBOOK_APP_ID.'" />';
			
		}
		
		$this->initializeSessionIfPresent();
		$this->initializeLocation();
		$this->initializeLanguage();
		$this->breadcrumbs[] = Array('label' => $this->_('Forside'), 'link' => '/');
	}
	
	/**
	 * Signal a failure to the client
	 * @param int $errcode http error code
	 * @param String $errmsg error message
	 * @param String $errmsglong optional error message for the log (if not specified, $errmsg will be logged instead)
	 */
    public function fail($errcode, $errmsg, $errmsglong=null)
    {
        while (ob_get_level()) ob_end_clean();
        
        $this->breadcrumbs = array_slice($this->breadcrumbs, 0, 1);
        $this->breadcrumbs[] = Array('label' => $errmsg);
        header('HTTP/1.0 '.$errcode.' '.$errmsg);
        $this->renderFragment('wrapper/top');
        if ($this->hasFragment('fail/' . $errcode)) {
            $this->renderFragment('fail/' . $errcode, Array('errcode' => $errcode, 'errmsg' => $errmsg));
        } else {
            $this->renderFragment('fail/default', Array('errcode' => $errcode, 'errmsg' => $errmsg));
        }
        $this->renderFragment('wrapper/bottom');
        $this->log('Fail:' . $errcode . ' msg:' . ($errmsglong != null ? $errmsglong : $errmsg) . ' url:' . $this->url . ' referer:' . @$_SERVER['HTTP_REFERER']);
        exit(1);
    }
    
    /**
     * Send a redirect to the client
     * @param String $location the new location
     */
    public function redirect($location) {
        if (strpos($location, ':/') !== false) {
            $this->fail(400, 'Bad request');
        }
        if (strpos($location, '/') !== 0) {
            $this->fail(400, 'Bad request');
        }
        header("Location: $location");
        exit;
    }
    
    /**
     * Check if the current user is a site admin
     * @return boolean true if user is logged in and is site admin, false otherwise
     */
    public function userIsSiteAdmin() {
        return $this->user && $this->user['isSiteAdmin'];
    }
    
    /**
     * Set the language cookie to the specified language code
     * @param String $code
     */
    public function setLanguageCookie($code) {
        if (array_key_exists($code, $this->languages)) {
            $this->currentLanguage = $code;
            setcookie('language', $code, time() + 60 * 60 * 24 * 180, '/');
        }
    }
    
    /**
     * Set the location cookie
     * @param string $locationSlug the location slug
     */
    public function setLocationCookie($locationSlug) {
        setcookie('location', $locationSlug, time() + 60 * 60 * 24 * 180, '/');
    }

    /**
     * Check the location in cookie
     */
    public function initializeLocation() {
        if (array_key_exists('location', $_COOKIE)) {
            $locationSlug = $_COOKIE['location'];
            $location = $this->dao->getLocationBySlug($locationSlug);
            if ($location) {
                $this->cookieLocation = $location;
            }
        }
    }
    
    /**
     * Initialize language.
     * This method will set the language based on the following algorithm:
     * 1: if 'language' is present in request params, this language is used, but not set in language cookie
     * 2: else if language cookie is present, this language is used
     * 3: else if 'defaultLanguage' is present in request params, this language is used and set in language cookie
     * 4: else if current location has a default language, this language is used
     */
    public function initializeLanguage() {
        if (array_key_exists('language', $_GET) && array_key_exists($_GET['language'], $this->languages)) {
            $this->currentLanguage = $_GET['language'];
            $this->currentLanguageSet = true;
        } else if (array_key_exists('language', $_COOKIE) && array_key_exists($_COOKIE['language'], $this->languages)) {
            $this->currentLanguage = $_COOKIE['language'];
            $this->currentLanguageSet = true;
        } else if (array_key_exists('defaultLanguage', $_GET) && array_key_exists($_GET['defaultLanguage'], $this->languages)) {
            $this->currentLanguage = $_GET['defaultLanguage'];
            $this->setLanguageCookie($this->currentLanguage);
            $this->currentLanguageSet = true;
        } else if ($this->currentLocation && array_key_exists($this->currentLocation['default_language'], $this->languages)) {
            $this->currentLanguage = $this->currentLocation['default_language'];
        }
        // language cookie is intentionally not set here, as language and defaultLanguage is intended to be primarily used for widgets.
        
        $this->loadLocaleFile();
    }
    
    /**
     * Load a translation file based on the currently set language
     */
    public function loadLocaleFile() {
        $localeFile = BASEDIR . '/locale/' . $this->currentLanguage . '.php';
        if (file_exists($localeFile) && is_readable($localeFile)) {
            // $this->log('reading translation file "' . $localeFile . '"');
            include $localeFile; // must set $textMap
            if (isset($translations) && is_array($translations)) {
                $this->translations = $translations;
            } else {
                $this->log('translation file "' . $localeFile . '" does not set $translations');
            }
        } else {
            $this->log('translation file "' . $localeFile . '" does not exist / is not readable');
        }
    }
    
    /**
     * Translate a string
     * @param String $string
     * @return String the translated string if available, $string otherwise
     */
    public function _($string) {
        if (array_key_exists($string, $this->translations)) {
            return $this->translations[$string];
        } else {
            return $string;
        }
    }
    
    /**
     * Initialize the session if one is present (only when a user is logged in)
     * Called from __construct
     */
    protected function initializeSessionIfPresent() {
        if (array_key_exists(session_name(), $_COOKIE)) {
            session_start();
            $this->sessionStarted = true;
            if (array_key_exists('user', $_SESSION)) {
                $this->user = $_SESSION['user'];
            }
        }
    }
    
    /**
     * User forgot password. Create a new one and send to users email address
     * @param String $username
     * @return true if successful, false if user did not exist
     */
    public function userForgotPassword($username) {
        $user = $this->dao->getUserByUserName($username);
        if ($user) {
            $newPassword = $this->createPassword();
            $user['password'] = $this->hashPassword($newPassword);
            if ($this->dao->saveUser($user)) {
                Mail::sendForgotPasswordEmail($user, $newPassword);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Login a user
     * @param String $username the username
     * @param String $word the password
     * @return boolean true if successful, false otherwise
     */
    public function login($username, $word) {
        $user = $this->dao->getUserByUsername($username);
        if ($user && $this->checkPasswordVsHash($word, $user['password'])) {
            return $this->loginUser($user);
        } else {
        	$this->log('login failed for user ' . $username);
            return false;
        }
    }
    
    /**
     * Log in the given user
     * @param Array $user the user as an associative array
     * @return boolean true if the user was logged in
     */
    private function loginUser($user) {
        if ($this->sessionStarted) {
            session_destroy();  // destroy current session if present
        }
        session_start();
        $this->user = $_SESSION['user'] = $user;
        $this->log('logging in user ' . $user['username']);
        if (!$this->user['isValidated']) {
            $this->user['isValidated'] = 1;
            $this->dao->saveUser($this->user);
        }
        return true;
    }
    
    public function updateUser($user) {
    	if ($this->sessionStarted && $this->user) {
    		$this->user = $_SESSION['user'] = $user;
    	}
    }
    
    /**
     * Logout the current logged in user
     */
    public function logout() {
        if ($this->user && array_key_exists(session_name(), $_COOKIE)) {
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            $this->log('logging out user ' . $this->user['username']);
        }
        $this->user = null;
    }
    
    /**
     * Set the current location from slug
     * @param String $slug the slug
     */
    public function setLocationFromSlug($slug) {
		$this->currentLocation = $this->dao->getLocationBySlug($slug);
		if ($this->currentLocation) {
		    if (!$this->currentLanguageSet && array_key_exists($this->currentLocation['default_language'], $this->languages)) {
		        $this->currentLanguage = $this->currentLocation['default_language'];
		        $this->currentLanguageSet = true;
		        $this->loadLocaleFile();
		    }
		    if ($this->currentLocation['parent_id'] || $this->currentLocation['id'] == 301) { // special case for oslo
		        $this->currentMunicipality = $this->currentLocation;
		        $this->currentCounty = $this->dao->getLocationById($this->currentLocation['parent_id']);
		    } else {
		        $this->currentCounty = $this->currentLocation;
		    }
            $this->breadcrumbs[] = Array('label' => $this->currentLocation['name'], 'link' => '/' . $slug);
            $this->setLocationCookie($slug);
		} else {
			$this->fail(404, 'Siden finnes ikke');
		}
    }
    
    /**
     * Test whether the given fragment exists
     * @param String $fragmentName
     */
    public function hasFragment($fragmentName) {
        return is_readable(BASEDIR . '/lib/fragments/' . $fragmentName.'.php') || is_readable(BASEDIR . '/lib/fragments/' . $fragmentName . '_' . $this->currentLanguage . '.php');
    }
    
    /**
     * Render the given fragment
     * @param String $fragmentName the name of the fragment to render
     * @param Array $data the data to pass along to the fragment
     */
    public function renderFragment($fragmentName, $data=array()) 
    {
        $languageFragmentFile = BASEDIR . '/lib/fragments/' . $fragmentName . '_' . $this->currentLanguage . '.php';
        if (is_readable($languageFragmentFile)) {
            include($languageFragmentFile);
        } else {
            include(BASEDIR . '/lib/fragments/' . $fragmentName . '.php');
        }
    }
    
    /**
     * Terminate all output blocks
     */
    public function renderImmediate()
    {
        while (ob_get_level()) ob_end_clean();
    }
    
    /**
     * Render the given fragment wrapped by a top and bottom fragment.
     * The main fragment is rendered first, within an output block, so the surrounding fragments will be affected by changes made by the main fragment.
     * @param String $fragmentName the main fragment to render
     * @param Array $data the data to pass along to the fragments
     * @param String $topFragment the fragment to render before. default is 'top'
     * @param String $bottomFragment the fragment to render after. default is 'bottom'
     */
    public function renderWrappedFragment($fragmentName, $data=array(), $topFragment='wrapper/top', $bottomFragment='wrapper/bottom')
    {
        ob_start();
        $this->renderFragment($fragmentName, $data);
        $mainhtml = ob_get_clean();
        
        $this->renderFragment($topFragment, $data);
        echo $mainhtml;
        $this->renderFragment($bottomFragment, $data);
    }
    
    /**
     * Send cache headers
     * @param String $maxage maximum cache age
     */
    public function sendHttpCachePublic($maxage) {
    	$now = time();
    	$expireTS = $now + $maxage;
    	header('Expires: '.gmdate('r', $expireTS));
    	header('Cache-Control: max-age='.$maxage.', public');
    	header('Last-Modified: '.gmdate('r', $now));
    }
    
    /**
     * Log message to the log file
     * @param String $message the message
     * @param String $level the level. Default is "DEBUG"
     */
    public function log($message, $level="DEBUG") {
        error_log("[$level] ".date("Y.m.d H:i:s") . ": $message\n", 3, LOG_FILE);
    }
	
    /**
     * Create a uuid
     * @return string the uuid
     */
    public function createUuid() {
        return $this->createRandomString(32);
    }
    
    /**
     * Create a random password
     * @return string the password
     */
    public function createPassword() {
        return $this->createRandomString(12);
    }
    
    /**
     * Hash a password
     *
     * Password is stored as:
     * salt + hmac_sha512(salt + password, sitekey)
     * salt is a 32char random string 
     * length is then 32+128 = 160 chars
     * 
     * @param String $string the password to hash
     * @param String $salt the salt to use. Default is to create a random string
     */
    public function hashPassword($string, $salt='') {
        if ($salt == '') {
            $salt = $this->createRandomString(32);
        }
        return $salt . hash_hmac('sha512', $salt.$string, PASSWORD_SITE_KEY);
    }
    
    /**
     * Check a password vs a hash
     * @param String $password the password
     * @param String $hash the hash
     * @return boolean true if password matches hash
     */
    public function checkPasswordVsHash($password, $hash) {
        $salt = substr($hash, 0, 32);
        return $this->hashPassword($password, $salt) === $hash;
    }
    
    /**
     * Log in a user by using his user access key
     * @param String $key
     * @return mixed true if user was logged in, false if key does not exist or a user as an associative array if key exists, but is too old
     */
    public function loginMagic($key) {
        $userAccessKey = $this->dao->getUserAccessKeyByKey($key);
        $timestamp = time();
        if ($userAccessKey['created_time'] + MAGIC_KEY_LIFE_TIME > $timestamp) {
            $user = $this->dao->getUserById($userAccessKey['user_id']);
            if ($user) {
                return $this->loginUser($user); // true
            } else {
                return false;
            }
        } else if ($userAccessKey) {
            return $this->dao->getUserById($userAccessKey['user_id']);
        } else {
            return false;
        }
    }
    
    /**
     * Create a magic key for user to log in automatically
     * @param Array $user the user
     * @return String a magic key
     */
    public function generateUserAccessKey($user) {
        $userAccessKeys = $this->dao->getUserAccessKeys($user['id']);
        $timestamp = time();
        $deleteIds = Array();
        if (count($userAccessKeys) > 0 && $userAccessKeys[0]['created_time'] + MAGIC_KEY_REUSE_TIME > $timestamp) {
            $userAccessKey = array_shift($userAccessKeys);
        } else {
            $userAccessKey = Array('user_id' => $user['id'], 'created_time' => $timestamp, 'access_key' => $this->createRandomString(64));
            $this->dao->addUserAccessKey($userAccessKey);
        }
        for ($i = 0; $i < count($userAccessKeys); $i++) {
            if ($i >= MAGIC_KEY_MAX_COUNT || $userAccessKeys[$i]['created_time'] < $timestamp - MAGIC_KEY_REMEMBER_TIME) {
                $deleteIds[] = $userAccessKeys[$i]['id'];
            }
        }
        if ($deleteIds) {
            $this->dao->removeUserAccessKeys($deleteIds);
        }
        return $userAccessKey['access_key'];
    }
    
    /**
     * Create a random string. Used for password, hash and uuid generation.
     * @param int $count number of characters in the string
     * @param String $chars the allowed chars in the random string concatenated
     * @return string
     */
    public function createRandomString($count, $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        $len = strlen($chars);
        $string = '';
        for ($i = 0; $i < $count; $i++) {
            $string .= substr($chars, rand(0, $len - 1), 1);
        }
        return $string;
    }
    
    /**
     * Get a value from a map with a default fallback if the value is not present
     * @param array $map the map
     * @param String $key the key
     * @param any $default the value to return if $key is not present in $map. Default is false.
     * @return unknown $map[$key] if present, $default otherwise 
     */
    public function getMapValue($map, $key, $default=false) {
        return is_array($map) && array_key_exists($key, $map) ? $map[$key] : $default;
    }
    
    /**
     * Get escaped REQUEST input value from a form field
     * @param String $field form field name
     * @param String $fallback fallback value if form field is missing
     * @return String
     */
    public function getEscapedRequestValue($field, $fallback='') {
        return htmlspecialchars($this->getRequestValue($field, $fallback));
    }
    
    /**
     * Get REQUEST input value from a form field
     * @param String $field form field name
     * @param String $fallback fallback value if form field is missing
     * @return String
     */
    public function getRequestValue($field, $fallback='') {
        if (array_key_exists($field, $_REQUEST)) {
            return $_REQUEST[$field];
        } else {
            return $fallback;
        }
    }
    
    /**
     * Get escaped POST input value from a form field
     * @param String $field form field name
     * @param String $fallback fallback value if form field is missing
     * @return String
     */
    public function getEscapedPostValue($field, $fallback='') {
        return htmlspecialchars($this->getPostValue($field, $fallback));
    }
    
    /**
     * Get POST input value from a form field
     * @param String $field form field name
     * @param String $fallback fallback value if form field is missing
     * @return String
     */
    public function getPostValue($field, $fallback='') {
        if (array_key_exists($field, $_POST)) {
            return $_POST[$field];
        } else {
            return $fallback;
        }
    }
    
    /**
     * Return only the digits at the start of the string
     * @param string $value
     * @return string the digits at the start of the string
     */
    public function stripToNumeric($value) {
        $matches = Array();
        preg_match('/^(\d*)/', $value, $matches);
        return $matches[1];
    }
    
    /**
     * Get form error block for form field
     * @param String $field field name
     * @param Array $map map of error blocks
     * @param String $before prepend this to error block if an error is present
     * @param String $after append this to error block if an error is present
     * @return string
     */
    public function getFormErrorBlock($field, $map, $before='<span class="form-error">', $after='</span>') {
        if (is_array($map) && array_key_exists($field, $map)) {
            return $before . $map[$field] . $after;
        } else {
            return '';
        }
    }
    
    /**
     * Returns the absolute, canonical URL for an initiative
     * @param int $initiativeId the initiative ID
     * @return string
     */ 
    public function getInitiativeCanonicalURL($initiativeId) {
    	return BASE_URL.'sak/'.intval($initiativeId);
    }
    
    /**
     * Returns a relative URL to an initiative's image.
     * @param Array $initiative the initiative database record
     * @param String $imagesize one of 'full', 'small'.
     * @return string
     */ 
    public function getInitiativeImageURL($initiative, $imagesize, $default=NULL) {
        global $app;
        
    	// Force imagesize to a known value
    	switch ($imagesize) {
    		case self::INITIATIVE_IMAGE_SIZE_FULL:
    		case self::INITIATIVE_IMAGE_SIZE_SMALL:
    			break;
    		default:
    			$imagesize = self::INITIATIVE_IMAGE_SIZE_SMALL; 
    	}
    	
    	$imageType = is_array($initiative) && array_key_exists('image_type', $initiative) ? $initiative['image_type'] : self::INITIATIVE_IMAGE_TYPE_MISSING;
    	switch ($imageType) {
    		case self::INITIATIVE_IMAGE_TYPE_LOCAL:
    			// Local file
    			return '/uploads/'.$initiative['image_file'].'.'.$imagesize.'.jpg';
    		case self::INITIATIVE_IMAGE_TYPE_FLICKR:
    			return '/uploads/'.$initiative['image_file'].'.'.$imagesize.'.jpg';
    			break;
    			case self::INITIATIVE_IMAGE_TYPE_MISSING:
    		default:
    			// Image missing
    			if ($default != null) {
    			    return $default;
    			} else {
    			    $initiativeId = is_array($initiative) && array_key_exists('id', $initiative) ? $initiative['id'] : 0;
    			    return '/static/default-initiative-images/' . sprintf('%02d', $initiativeId % 20) . '.png';
    			}
    	}
    }
    
    /**
     * Get initiative image variants for use when uploading an image
     * @param unknown_type $fileNameBase base file name without extension
     * @param unknown_type $imageExtensionIncDot file extension including the '.' character
     * @return Array list of image variants usable with ImageUtils::convertImageToVariants
     */
    public function getInitiativeImageVariants($fileNameBase, $imageExtensionIncDot) {
        return Array(
            'original' => Array('filename' => $fileNameBase . $imageExtensionIncDot),
            self::INITIATIVE_IMAGE_SIZE_SMALL => Array('filename' => $fileNameBase . '.small.jpg', 'width' => 195, 'height' => 140),
            self::INITIATIVE_IMAGE_SIZE_FULL => Array('filename' => $fileNameBase . '.full.jpg', 'width' => 195, 'height' => 140),
        );
    }
    
    /**
     * Truncate a string at a word boundary making it approximately $length characters long
     * @param String $str the string to truncate
     * @param int $length the length to approximate
     * @param String $encoding string encoding
     * @return string
     */
   	public function mb_substr_wholeword($str, $length=0, $encoding=null) {
   		$words = mb_split(' ', mb_substr($str,0,$length+50,$encoding));
   		$i = 0;
   		$c = mb_strlen($words[$i]);
   		$ret = '';
   		while ($c < $length) {
   			if ($i > 0) {
   				$ret .= ' ';	
   			}
   			$ret .= $words[$i];
   			
   			$c = mb_strlen($ret) + 1 + mb_strlen($words[$i+1]);
   			$i++;
   		}
   		return $ret;
   		
   	}
    
}

