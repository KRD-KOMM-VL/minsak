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
 * DAO class for abstracting database tasks.
 */
class Dao {

	/**
	 * The DataAccess object for communicating with the database.
	 * @var DataAccess
	 */
	private $da = null;
	
	/**
	 * The AppContext reference.
	 * @var AppContext
	 */
	private $app = null;

	public function __construct(DataAccess $da, AppContext $app) {
		$this->da = $da;
		$this->app = $app;
	}

	/**
	 * Utility function to get a single row from database based on the provided query
	 * 
	 * @param String $sql the sql query
	 * @param * $default the default value to return if not exactly one match.
	 * @return the database row as an associative array if the query matched exactly one row, otherwise $default.
	 */
	private function getUnique($sql, $default=false) {
	    $rows = $this->da->fetchAll($sql);
	    if (count($rows) == 1) {
	        return $rows[0];
	    } else {
	        return $default;
	    }
	}
	
	/**
	 * Get all users from database as an array of associative arrays
	 * 
	 * @return an array of associative arrays
	 */
	public function getAllUsers() {
	    $sql = "SELECT * FROM user";
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Get all users that are site admins
	 * @return an array of associative arrays
	 */
	public function getSiteAdmins() {
	    $sql = "SELECT * FROM user WHERE isSiteAdmin='1'";
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Get the user with the given user id
	 * @param int $userId the user id
	 * @return an associative array
	 */
	public function getUserById($userId) {
	    $sql = "SELECT * FROM user WHERE id='" . $this->da->escape($userId) . "'";
	    $rows = $this->da->fetchAll($sql);
	    if (count($rows) == 1) {
	        return $rows[0];
	    } else {
	        return false;
	    }
	}
	
	/**
	 * Get the user with the given username
	 * @param String $username
	 * @return an associative array if the user exists, false otherwise
	 */
	public function getUserByUsername($username) {
	    $sql = "SELECT * FROM user WHERE username='" . $this->da->escape($username) . "'";
	    return $this->getUnique($sql);
	}
	
	public function saveUser($userData) {
	    try {
	        $userId = array_key_exists('id', $userData) && $userData['id'] ? $userData['id'] : 0;
	        unset ($userData['id']);
    	    if ($userId) {
    	        $sql = $this->da->buildUpdateSql('user', $userData, Array('id' => $userId));
    	        $result = $this->da->query($sql);
    	    } else {
    	        $sql = $this->da->buildInsertSql('user', $userData);
    	        $result = $this->da->query($sql);
    	        if ($result) {
    	            $userId = $this->da->lastInsertID();
    	        }
    	    }
            if ($result) {
                $userData['id'] = $userId;
                return $userData;
            } else {
                return false;
            }
	    } catch (DataAccessException $dae) {
	        // can be caused by duplicate username: ERROR 1062 (23000): Duplicate entry 'xxx' for key 'username'
	        $this->app->log('saveUser caused exception: ' . $dae->getCode() . ' - ' . $dae->getMessage());
	        if ($dae->getCode() == 1062) {
	            return false;
	        } else {
	            throw $dae;
	        }
	    }
	}
	
	/**
	 * Delete the user with the given user id
	 * @param int $userId the user id
	 */
	public function deleteUser($userId) {
	    $sql = "DELETE FROM user WHERE id='" . $this->da->escape($userId) . "'";
	    $this->da->query($sql);
	}

	/**
	 * Get user access keys
	 * @param unknown_type $userId
	 */
	public function getUserAccessKeys($userId) {
	    $sql = "SELECT * FROM user_access_key WHERE user_id='" . $this->da->escape($userId) . "' ORDER BY created_time DESC";
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Add a user access key
	 * @param Array $userAccessKey table data as an associative array
	 */
	public function addUserAccessKey($userAccessKey) {
	    $sql = $this->da->buildInsertSql('user_access_key', $userAccessKey);
	    return $this->da->query($sql);
	}
	
	/**
	 * Delete user access keys
	 * @param Array $ids the ids to remove
	 */
	public function removeUserAccessKeys(Array $ids) {
	    $sql = "DELETE FROM user_access_key where id in (" . join(',', $this->da->escapeArray($ids)) . ")";
	}
	
	public function getUserAccessKeyByKey($key) {
	    $sql = "SELECT * FROM user_access_key WHERE access_key='" . $this->da->escape($key) . "'";
	    return $this->getUnique($sql);
	}
	
	/**
	 * Get user roles
	 * @param array|int $user user as associative array or user id
	 * @return array map keyed on location id of user roles as associative arrays
	 */
	public function getUserRoles($user) {
	    if (is_array($user)) {
	        $userId = $user['id'];
	    } else {
	        $userId = $user;
	    }
	    $sql = "SELECT * FROM user_role WHERE user_id='" . $this->da->escape($userId) . "'";
	    return $this->da->fetchAllKeyed($sql, 'location_id');
	}
	
	/**
	 * Add a user role
	 * @param array $userRole associative array of data to insert
	 */
	public function addUserRole(Array $userRole) {
	    $sql = $this->da->buildInsertSql('user_role', $userRole);
	    return $this->da->query($sql);
	}
	
	/**
	 * Update a user role
	 * @param array $userRole associative array of data to update
	 */
	public function updateUserRole(Array $userRole) {
	    $sql = "UPDATE user_role SET initiative_moderator='" . $this->da->escape($userRole['initiative_moderator']) . "', signature_moderator='" . $this->da->escape($userRole['signature_moderator']) . "' WHERE user_id='" . $this->da->escape($userRole['user_id']) . "' AND location_id='" . $this->da->escape($userRole['location_id']) . "'";
	    return $this->da->query($sql);
	}

	/**
	 * Delete a user role
	 * @param int $userId the user id
	 * @param int $locationId the location id
	 */
	public function deleteUserRole($userId, $locationId) {
	    $sql = "DELETE FROM user_role WHERE user_id='" . $this->da->escape($userId) . "' AND location_id='" . $this->da->escape($locationId) . "'";
	    return $this->da->query($sql);
	}
	
	/**
	 * Get all locations
	 * @return a map of associative arrays mapping location id to an associative array representation of a location
	 */
	public function getLocations() {
	    $sql = "SELECT * FROM location ORDER BY name COLLATE utf8_danish_ci";
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Get all legal location ids
	 * @return an array of int
	 */
	public function getLocationIds() {
	    return array_keys($this->getLocations());
	}
	
	/**
	 * Get all locations interlink them by letting all county locations get a 'children' element that is an
	 * array of municipalities and all municipality locations get a 'parent' element that is the county location.
	 * @return array a list of locations as associative arrays
	 */
	public function getLocationsInterlinked() {
	    $locations = $this->getLocations();
	    foreach ($locations as $id => $location) {
            if ($location['parent_id']) {
                $parentId = $location['parent_id'];
                if (array_key_exists($parentId, $locations)) {
                    if (!array_key_exists('children', $locations[$parentId])) {
                        $locations[$parentId]['children'] = Array();
                    }
                    $locations[$parentId]['children'][] = &$locations[$id];
                    $locations[$id]['parent'] = &$locations[$parentId];
                }
            }
        }
        return $locations;
	}
	
	/**
	 * Build a hierarchy of locations by returning a list of counties, each having 'parent' and 'children' properties where appropriate.
	 * @return array a list of counties as an associative array
	 */
	public function getLocationsHierarchy() {
	    $locations = $this->getLocationsInterlinked();
	    $counties = Array();
	    foreach ($locations as $id => $location) {
	        if (!array_key_exists('parent', $location)) {
	            $counties[] = $location;
	        }
	    }
	    return $counties;
	}
	
	/**
	 * Get the location with the given location id
	 * @param int $locationId
	 * @return an associative array if the location exists, false otherwise
	 */
	public function getLocationById($locationId) {
	    $sql = "SELECT * FROM location WHERE id='" . $this->da->escape($locationId) . "'";
	    return $this->getUnique($sql);
	}
	
	/**
	 * Get the location with the given slug
	 * @param String $slug the slug
	 * @return an associative array if the location exists, false otherwise
	 */
	public function getLocationBySlug($slug) {
	    $sql = "SELECT * FROM location WHERE slug='" . $this->da->escape($slug) . "'";
	    return $this->getUnique($sql);
	}
	
	/**
	 * Returns an array with extended location info (county name, etc)
	 */ 
	public function getLocationExtendedInfoById($locationId) {
		$loc = $this->getLocationById($locationId);
		if (!is_array($loc)) return null;
		$result = $loc;
		$extra = array('name' => $result['name']);
		if ($loc['parent_id'] > 0) {
			$parentLoc = $this->getLocationById($loc['parent_id']);
			if (is_array($parentLoc)) {
				$extra['name'] .= ', '.$parentLoc['name'];
			}
		}
		$result['extra'] = $extra;
		return $result;
	}
	
	/**
	 * Save a temporary image when creating an initiative
	 * @param Array $tempImage the data to store
	 * @return array the $tempImage with id added
	 */
	public function saveTemporaryImage($tempImage) {
	    $sql = "DELETE FROM temporary_image WHERE user_id='" . $this->da->escape($tempImage['user_id']) . "'";
	    $this->da->query($sql);
	    $sql = $this->da->buildInsertSql('temporary_image', $tempImage);
	    if ($this->da->query($sql)) {
	        $tempImage['id'] = $this->da->lastInsertID();
	        return $tempImage;
	    } else {
	        return false;
	    }
	}
	
	/**
	 * Remove a temporary image
	 * @param int $temporaryImageId
	 */
	public function removeTemporaryImage($temporaryImageId) {
	    $sql = "DELETE FROM temporary_image WHERE id='" . $temporaryImageId . "'";
	    $this->da->query($sql);
	}
	
	/**
	 * Get a temporary image for a user. A temporary image is only valid for 1 hour, and only the latest added is considered.
	 * @param int $userId the user id
	 * @return array the temporary image as an associative array, or false if no image present
	 */
	public function getTemporaryImage($userId) {
	    $sql = "SELECT * FROM temporary_image WHERE uploaded_time > " . (time() - 60 * 60) . " AND user_id='" . $this->da->escape($userId) . "' ORDER BY uploaded_time DESC LIMIT 1";
	    return $this->getUnique($sql);
	}
	
	/**
	* Get the initiative with the given initiative id
	* @param int $initiativeId
	* @return an associative array if the initiative exists, false otherwise
	*/
	public function getInitiativeById($initiativeId) {
		$sql = "SELECT * FROM initiative WHERE id='" . $this->da->escape($initiativeId) . "'";
		return $this->getUnique($sql);
	}
	
	
	/**
	 * Get initiatives owned by the specified user
	 * @param Array|int $user either a user object as an associative array (containing 'id' => userId) or a user id
	 * @return Array list of initiatives as associative arrays
	 */
	public function getInitiativesByUser($user) {
	    if (is_array($user)) {
	        $userId = $user['id'];
	    } else {
	        $userId = $user;
	    }
	    $sql = "SELECT * FROM initiative WHERE user_id='" . $this->da->escape($userId) . "'";
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Get drafted initiatives owned by the specified user
	 * @param Array|int $user either a user object as an associative array (containing 'id' => userId) or a user id
	 * @param int $excludeId an initiative id to exclude from the result
	 * @return Array list of initiatives as associative arrays
	 */
	public function getDraftInitiativesByUser($user, $excludeId=false) {
	    if (is_array($user)) {
	        $userId = $user['id'];
	    } else {
	        $userId = $user;
	    }
	    $sql = "SELECT * FROM initiative WHERE user_id='" . $this->da->escape($userId) . "' AND status='draft'";
	    if ($excludeId) {
	        $sql .= " AND id<>'" . $this->da->escape($excludeId) . "'";
	    }
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Get visible initiatives in the specified location or all locations
	 * @param int $limit number of entries to retrieve
	 * @param int $locationId the location id (if 0, get initiatives from all locations)
	 * @return array list of initiatives as associative arrays
	 */
	public function getVisibleInitiativesByLocation($limit, $locationId=0, $startAtIndex=0) {
	    $sql = "SELECT * FROM initiative WHERE status IN ('".AppContext::INITIATIVE_STATUS_OPEN."', '".AppContext::INITIATIVE_STATUS_SCREENING."', '".AppContext::INITIATIVE_STATUS_COMPLETED."')" . ($locationId ? " AND location_id='" . $this->da->escape($locationId) . "'" : "") . " ORDER BY created_time DESC LIMIT " . intval($limit);
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Search for initiatives
	 * @param string $title the title
	 * @param int $location_id the location id, or 0 for any location
	 * @param boolean $status_new true to include new initiatives
	 * @param boolean $status_completed true to include completed initiatives
	 * @param string $sort sort field
	 * @param string $sort_dir sort direction 'desc' or 'asc'
	 * @param int $start start of page
	 * @param int $limit number of items on page
	 * @return list of matches keyed on initiative id
	 */
	public function searchForInitiatives($title='',$location_id=0, $status_new=true, $status_completed=false, $sort='title', $sort_dir='desc', $start=0, $limit=0) {
		$sql = $this->buildQueryForInitiativeSearch($title, $location_id, $status_new, $status_completed, $sort, $sort_dir, $start, $limit);
		return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Get number of hits for initiative search criteria
	 * @param unknown_type $title the title
	 * @param unknown_type $location_id the location_id or 0 for any location
	 * @param unknown_type $status_new true to include new initiatives
	 * @param unknown_type $status_completed true to include completed initiatives
	 * @return number of hits
	 */
	public function countVisibleInitiativesBySearch($title,$location_id, $status_new, $status_completed) {
		$sql = $this->buildQueryForInitiativeSearch($title, $location_id, $status_new, $status_completed, false, false, 0, 0, true);
        $result = $this->getUnique($sql);
        if (is_array($result) && array_key_exists('count', $result)) {
            return $result['count'];
        } else {
            return false;
        }
	}
	
	/**
	 * Internal method for building search query
	 * @param unknown_type $title the title (but also matches on location name)
	 * @param unknown_type $location_id the location id, or 0 for any location
	 * @param unknown_type $status_new true to include new initiatives
	 * @param unknown_type $status_completed true to include completed initiatives
	 * @param unknown_type $sort sort field
	 * @param unknown_type $sort_dir sort direction 'desc' or 'asc'
	 * @param unknown_type $start start of page
	 * @param unknown_type $limit number of items on page
	 * @param unknown_type $selectCount set to true to make query for getting number of hits instead of a result set
	 */
	protected function buildQueryForInitiativeSearch($title='', $location_id=0, $status_new=true, $status_completed=false, $sort='age', $sort_dir='desc', $start=0, $limit=0, $selectCount=false)
	{
		// select from
		$sql = 'SELECT ' . ($selectCount ? 'count(*) as count' : 'i.*, l.parent_id, l.default_language, l.slug') . " FROM initiative i, location l  WHERE i.location_id=l.id";
		
		// where
		
		//	title
		$titleEscaped = $this->da->escape(strtolower($title));
		if ($title != '') {
			$sql .= ' AND ';
			// case-insensitive wildcard search
			$sql .= "LOWER(CONCAT(i.title, ' ', l.name)) LIKE '%" . $titleEscaped . "%'";
		}
		
		// location
		if ($location_id > 0) {
			$sql .= ' AND ';
			$sql .= "i.location_id='" . $this->da->escape(intval($location_id)) . "'";
		}
		
		// status
		$statuses = Array();
		if ($status_new || $status_completed) {
			if ($status_new) {
				$statuses[] = AppContext::INITIATIVE_STATUS_OPEN;
				$statuses[] = AppContext::INITIATIVE_STATUS_SCREENING;
			}
			if ($status_completed) {
				$statuses[] = AppContext::INITIATIVE_STATUS_COMPLETED;
			}
		} else {
		    $statuses[] = AppContext::INITIATIVE_STATUS_OPEN;
			$statuses[] = AppContext::INITIATIVE_STATUS_SCREENING;
			$statuses[] = AppContext::INITIATIVE_STATUS_COMPLETED;
		}
		$sql .= ' AND i.status IN (';
		$first = true;
		foreach ($statuses as $status) {
			if (!$first) {
				$sql .= ',';
			}
			$sql .= '\'' . $status . '\'';
			$first = false;
		}
		$sql .= ')';
		
		if (!$selectCount) {
			// wrap the sql in a left join to also get signature count
			$sql = "SELECT i.*, s.num_votes FROM (" . $sql . ") i LEFT JOIN (SELECT initiative_id, count(*) AS num_votes FROM signature WHERE moderated='accepted' GROUP BY initiative_id) s on s.initiative_id=i.id";
			
			// sort
			if ($sort !== false) {
			    if ($sort == 'age') {
			        $sql .= ' ORDER BY i.created_time ' . (('desc' == strtolower($sort_dir)) ? 'ASC' : 'DESC');
			    } else {
				$sql .= ' ORDER BY ' . ($sort == 'num_votes' ? 's' : 'i') . '.'. $sort . (('desc' == strtolower($sort_dir)) ? ' DESC' : ' ASC') . ', i.created_time DESC';
			}
			}
			
			// limit
			if (intval($limit) > 0) {
				$sql .= ' LIMIT ';
				if (intval($start) > 0) {
					$sql .= intval($start) . ',';
				}
				$sql .= intval($limit);
			}
		}
		
		return $sql;
	}
	
	/**
	 * Add an initiative status row
	 * @param Array $initiativeStatus the initiative status as an associative array
	 */
	public function addInitiativeStatus($initiativeStatus) {
	    $sql = $this->da->buildInsertSql('initiative_status', $initiativeStatus);
	    $this->da->query($sql);
	}
		
	/**
	 * Save or update initiative
	 * @param array $initiative the initiative as an associative array
	 */
	public function updateInitiative($initiative) {
        $initiativeId = array_key_exists('id', $initiative) && $initiative['id'] ? $initiative['id'] : 0;
        unset ($initiative['id']);
	    if ($initiativeId) {
	        $sql = $this->da->buildUpdateSql('initiative', $initiative, Array('id' => $initiativeId));
	        $result = $this->da->query($sql);
	    } else {
	        $sql = $this->da->buildInsertSql('initiative', $initiative);
	        $result = $this->da->query($sql);
	        if ($result) {
	            $initiativeId = $this->da->lastInsertID();
	        }
	    }
        if ($result) {
            $initiative['id'] = $initiativeId;
            return $initiative;
        } else {
            return false;
        }
	}
	
	/**
	 * Get number of initiatives per status
	 */
	public function getInitiativesPerStatus() {
	    $sql = "SELECT status, count(*) AS count FROM initiative GROUP BY status";
	    return $this->da->fetchAll($sql);
	}
	
	public function getInitiativesPerLocationPerStatus() {
	    $sql = "SELECT location_id, status, count(*) AS count FROM initiative GROUP BY location_id, status";
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Delete an initiative
	 * @param int $initiativeId
	 */
	public function deleteInitiative($initiativeId) {
	    $sql = "DELETE FROM initiative WHERE id='" . $this->da->escape($initiativeId) . "'";
	    return $this->da->query($sql);
	}
	
	/**
	 * Get pending initiatives
	 * @return Array map of initiatives keyed on initiative id
	 */
	public function getPendingInitiatives(Array $locationIds=null) {
	    $sql = "SELECT * FROM initiative WHERE status IN ('unmoderated')";
	    if (is_array($locationIds)) {
	        $sql .= " AND location_id IN (" . join(',', $this->da->escapeArray($locationIds)) . ")";
	    }
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
    /**
     * Get pending signatures. Either all pending signatures, or if $locationIds and $userId is given, only those in the given locations or owned by the given user
     * @param $locationIds an array of location ids
     * @param $userId the user id of the current user. this param is required if locationIds is given.
     * @return Array map of signatures keyed on signature id
     */
	public function getPendingSignatures(Array $locationIds=null, $userId=null) {
	    $sql = "SELECT * FROM signature WHERE moderated='new'";
	    if (is_array($locationIds)) {
            $sql .= " AND initiative_id IN (SELECT id FROM initiative WHERE location_id IN (" . join(',', $this->da->escapeArray($locationIds)) . ") OR user_id='" . $this->da->escape($userId) . "')";
	    }
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Get all signatures for an initiative identified by the initiative's id
	 * @param int $initiativeId the initiative id
	 * @return Array map of signatures keyed on signature id
	 */
	public function getSignaturesByInitiativeId($initiativeId) {
	    $sql = "SELECT * FROM signature WHERE initiative_id='" . $this->da->escape($initiativeId) . "'";
	    return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	/**
	 * Get all moderated signatures (only the name column is fetched from database) for an initiative identified by the initiative's id
	 * @param int $initiativeId the initiative id
	 * @return Array list of signatures
	 */
	public function getModeratedSignaturesByInitiativeId($initiativeId) {
	    $sql = "SELECT name FROM signature WHERE moderated='accepted' AND initiative_id='" . $this->da->escape($initiativeId) . "' ORDER BY name COLLATE utf8_danish_ci";
	    return $this->da->fetchAll($sql);
	}
	
	/**
	 * Get sum of number of initiatives that requires moderation
	 * @return int number of initiatives that requires moderation.
	 */
    public function getModerationRequired() {
        $sql = "SELECT COUNT(*) AS count FROM initiative WHERE status = 'unmoderated'";
        $result = $this->getUnique($sql);
        if (is_array($result) && array_key_exists('count', $result)) {
            return $result['count'];
        } else {
            return false;
        }
    }
	
	/**
	 * Get non site admin users that have pending moderation tasks
	 */
	public function getUsersWithModerationTasks() {
	    $sql = <<<EOS
SELECT * FROM `user` WHERE isSiteAdmin=0 AND id in (
    SELECT user_id FROM user_role WHERE initiative_moderator=1 AND location_id IN (SELECT DISTINCT(location_id) FROM initiative WHERE status = 'unmoderated')
)
EOS;
        return $this->da->fetchAllKeyed($sql, 'id');
	}
	
	
	/**
	* Save a new signature for an initiative
	* @param string $signatureName Name of the signature author
	* @param string $signatureAddress1 Address (part1) of the signature author
	* @param string $signatureAddress2 Address (part2) of the signature author
	* @param string $signatureAreaCode Area code of address of the signature author
	* @param int $initiativeId Initiative ID
	* @param boolean $autoModerated true if the comment is already auto-moderated, cf. location.auto_moderate_initiative
	* @return signature ID, or false on error
	*/
	public function addSignature($signatureName, $signatureAddress1, $signatureAddress2, $signatureAreaCode, $initiativeId, $autoModerated) {
		$sql = $this->da->buildInsertSql('signature', array(
			'initiative_id' => $initiativeId, 
			'name' => $signatureName, 
			'address1' => $signatureAddress1, 
			'address2' => $signatureAddress2, 
			'area_code' => $signatureAreaCode, 
			'created_time' => time(), 
			'moderated' => ($autoModerated ? AppContext::INITIATIVE_SIGNATURE_MODERATION_ACCEPTED : AppContext::INITIATIVE_SIGNATURE_MODERATION_NEW)
		));
		$result = $this->da->query($sql);
		if ($result) {
			return $this->da->lastInsertID();
		}
		return false;
	}
	
	public function updateSignature($signature) {
	    $sql = $this->da->buildUpdateSql('signature', $signature, Array('id' => $signature['id']));
	    $this->da->query($sql);
	}
	
	/**
	 * Retrieves the countable ('new' and 'accepted') signature count for a set of initiative IDs
	 * @param Array $initiativeIds array of initiatives
	 * @return Array with  ($initiativeId => $numberOfValidSignatures) 
	 */ 
	public function getValidSignatureCountsForInitiatives($initiativeIds) {
		if (empty($initiativeIds)) return array();
		$sql = "SELECT initiative_id, COUNT(*) AS signature_count FROM signature WHERE initiative_id IN (".join(',',$initiativeIds).") AND moderated in ('accepted', 'new') GROUP BY initiative_id";
		$resultrows = $this->da->fetchAllKeyed($sql, 'initiative_id');
		$result = array();
		foreach ($initiativeIds as $initiativeId) {
			$result[$initiativeId] = array_key_exists($initiativeId, $resultrows) ? $resultrows[$initiativeId]['signature_count'] : 0;
		}
		return $result;
	}
	
	/**
	 * Get list of initiatives that has enough moderated signatures to be 'completed'
	 * @return Array array of associative arrays
	 */
    public function getInitiativesWithEnoughSignatures() {
        // select i.*, l.signatures_required from initiative i, location l where i.location_id=l.id;
        // select i.*, count(*) as signature_count from initiative i, signature s where i.id=s.initiative_id and i.status='open' and s.moderated='accepted' group by i.id;
        $sql = "SELECT i.*, signatures_required FROM (SELECT i.*, count(*) AS signature_count FROM initiative i, signature s WHERE i.id=s.initiative_id AND i.status='open' AND s.moderated='accepted' GROUP BY i.id) i, location l WHERE i.location_id=l.id AND signature_count >= signatures_required";
        return $this->da->fetchAllKeyed($sql, 'id');
    }
	
	/**
	* Get the comment with the given initiative id
	* @param int $commentId
	* @return an associative array if the comment exists, false otherwise
	*/
	public function getCommentById($commentId) {
		$sql = "SELECT * FROM initiative_comment WHERE id='" . $this->da->escape($commentId) . "'";
		return $this->getUnique($sql);
	}

	/**
	 * Retrieves all comments for an initiative
	 * @param int $initiativeId the id of the initiative
	 * @return Array array of associative arrays 
	 */ 
	public function getCommentsForInitiative($initiativeId) {
		$sql = "SELECT * FROM initiative_comment WHERE initiative_id='".$this->da->escape($initiativeId)."' AND status='accepted' ORDER BY created_time DESC";
		return $this->da->fetchAll($sql);  
	}
	
	/**
	* Save a new comment for an initiative
	* @param string $commentName Name of the comment author
	* @param string $commentText Comment body text
	* @param int $initiativeId Initiative ID
	* @return comment ID, or false on error
	*/
	public function addComment($commentName, $commentText, $initiativeId) {
		$sql = $this->da->buildInsertSql('initiative_comment', array('initiative_id' => $initiativeId, 'name' => $commentName, 'text' => $commentText, 'created_time' => time()));
		$result = $this->da->query($sql);
		if ($result) {
			return $this->da->lastInsertID();
		}
		return false;
	}

	/**
	 * Update comment
	 * @param array $comment the comment as an associative array
	 */
	public function updateComment($comment) {
        $commentId = array_key_exists('id', $comment) && $comment['id'] ? $comment['id'] : 0;
        unset ($comment['id']);
        if (!$commentId) {
        	throw new DataAccessException('comment id not set');
        }
        $sql = $this->da->buildUpdateSql('initiative_comment', $comment, Array('id' => $commentId));
        $result = $this->da->query($sql);
        if ($result) {
            $comment['id'] = $commentId;
            return $comment;
        } else {
            return false;
        }
	}
}
