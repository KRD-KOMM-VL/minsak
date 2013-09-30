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
$cmd = @(string)$app->urlParts[1];

$result = array();
$renderResult = true;

switch ($cmd) {
    
	case 'locations':
		$result = $app->dao->getLocations();
		$app->sendHttpCachePublic(60*60*24); // Allow caching for a day
		break;
		
	case 'saveImage':
	    if (!$app->user) {
	    	$app->log('saveImage: no user');
	        $app->fail(401, 'Unauthorized');
	    }
	    if (!array_key_exists('initiativeId', $_POST)) {
	        $app->log('saveImage: no initiative id');
	        $app->fail(500, 'Server error');
	    }
	    $initiativeId = $_POST['initiativeId'];
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    if ($initiative && $initiative['status'] != AppContext::INITIATIVE_STATUS_DRAFT) {
	    	$app->log('saveImage: initiative status is not draft (' . $initiativeId .')');
	        $app->fail(500, 'Server error');
	    }
        $fileNameBase = time() . '_' . rand(100000000, 999999999);
        $imageOriginalName = $_FILES['image']['name'];
        $imageExtensionIncDot = substr($imageOriginalName, strrpos($imageOriginalName, '.'));
        $originalFile = IMAGE_UPLOAD_DIR . $fileNameBase . $imageExtensionIncDot;
        $app->log('saveImage: uploading ' . $imageOriginalName . ' to ' . $originalFile . ' for initiative id ' . $initiativeId);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $originalFile)) {
            $variants = $app->getInitiativeImageVariants($fileNameBase, $imageExtensionIncDot);
            if (ImageUtils::convertImageToVariants($originalFile, $variants)) {
                $result['imageUrl'] = IMAGE_UPLOAD_DIR_URL_BASE . $variants[AppContext::INITIATIVE_IMAGE_SIZE_FULL]['filename'];
                if ($initiative) {
                    $initiative['image_file'] = $fileNameBase;
                    $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_LOCAL;
                    $app->dao->updateInitiative($initiative);
                } else {
                    $tempimage = Array(
                        'user_id' => $app->user['id'],
                        'image_type' => AppContext::INITIATIVE_IMAGE_TYPE_LOCAL,
                        'image_file' => $fileNameBase,
                        'uploaded_time' => time(),
                    );
                    $res = $app->dao->saveTemporaryImage($tempimage);
                    if (!$res) {
                    	$app->log('saveImage: failed to save temporary image');
                        $app->fail(500, 'Server error');
                    } else {
                        $result['temp_image_id'] = $res['id'];
                    }
                }
            } else {
            	$app->log('saveImage: unable to convert image to variants: ' + $originalFile);
                $app->fail(500, 'Server error');
            }
        } else {
        	$app->log('saveImage: move_uploaded_file failed');
            $app->fail(500, 'Server error');
        }
	    break;
	    
	case 'addFlickrImage':
	    $initiativeId = array_key_exists('initiativeId', $_REQUEST) ? $_REQUEST['initiativeId'] : 0;
	    $flickrImageId = array_key_exists('flickrImageId', $_REQUEST) ? $_REQUEST['flickrImageId'] : 0;
	    $flickrImageCredits = array_key_exists('flickrImageCredits', $_REQUEST) ? $_REQUEST['flickrImageCredits'] : '';
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    $flickrUrl = 'http://www.flickr.com/services/rest/?method=flickr.photos.getSizes&format=php_serial&api_key=' . FLICKR_APP_KEY . '&photo_id=' . urlencode($flickrImageId);
	    $fileNameBase = time() . '_' . rand(100000000, 999999999);
	    $ch = curl_init($flickrUrl);
	    ob_start();
	    $success = false;
	    try {
	        curl_exec($ch);
	        $success = true;
	        curl_close($ch);
	    } catch (Exception $e) {
	    }
	    $serialized = ob_get_clean();
	    if ($success) {
    	    $flickrData = unserialize($serialized);
    	    if (array_key_exists('stat', $flickrData) && $flickrData['stat'] == 'ok' && array_key_exists('sizes', $flickrData) && array_key_exists('size', $flickrData['sizes'])) {
    	        $sizes = Array();
    	        foreach ($flickrData['sizes']['size'] as $size) {
    	            $sizes[$size['label']] = $size;
    	        }
    	        $size = null;
    	        if (array_key_exists('Medium', $sizes)) {
    	            $size = $sizes['Medium'];
    	        } else if (array_key_exists('Small', $sizes)) {
    	            $size = $sizes['Small'];
    	        } else if (array_key_exists('Thumbnail', $sizes)) {
    	            $size = $sizes['Thumbnail'];
    	        }
    	        if ($size) {
    	            $imageUrl = $size['source'];
                    $imageExtensionIncDot = substr($imageUrl, strrpos($imageUrl, '.'));
                    $originalFile = IMAGE_UPLOAD_DIR . $fileNameBase . $imageExtensionIncDot;
                    $fp = fopen($originalFile, "w");
    	            $ch1 = curl_init($imageUrl);
    	            curl_setopt($ch1, CURLOPT_FILE, $fp);
    	            $success1 = false;
    	            try {
    	                curl_exec($ch1);
    	                $success1 = true;
    	                curl_close($ch1);
    	            } catch (Exception $e) {
    	            }
	                fclose($fp);
    	            if ($success1) {
                        $variants = $app->getInitiativeImageVariants($fileNameBase, $imageExtensionIncDot);
                        if (ImageUtils::convertImageToVariants($originalFile, $variants)) {
                            $result['imageUrl'] = IMAGE_UPLOAD_DIR_URL_BASE . $variants[AppContext::INITIATIVE_IMAGE_SIZE_FULL]['filename'];
                            if ($initiative) {
                                $initiative['image_file'] = $fileNameBase;
                                $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_FLICKR;
                                $initiative['image_flickr_photo_id'] = $flickrImageId;
                                $initiative['image_credits'] = $flickrImageCredits;
                                $app->dao->updateInitiative($initiative);
                            } else {
                                $tempimage = Array(
                                    'user_id' => $app->user['id'],
                                    'image_type' => AppContext::INITIATIVE_IMAGE_TYPE_FLICKR,
                                    'image_file' => $fileNameBase,
                                    'image_flickr_photo_id' => $flickrImageId,
                                    'image_flickr_image_credits' => $flickrImageCredits,
                                    'uploaded_time' => time(),
                                );
                                $res = $app->dao->saveTemporaryImage($tempimage);
                                if (!$res) {
                                    $app->fail(500, 'Server error');
                                } else {
                                    $result['temp_image_id'] = $res['id'];
                                }
                            }
                        } else {
                            $app->fail(500, 'Server error');
                        }
    	            } else {
    	                // handle error .. couldn't download image
    	                $result['error'] = 1;
    	            }
    	        } else {
    	            // handle error .. can't find suitable size
    	            $result['error'] = 2;
    	        }
    	    } else {
    	        // handle error .. data format not as expected
    	        $result['error'] = 3;
    	    }
	    } else {
	        // handle error .. couldn't get data from flickr
	        $result['error'] = 4;
	    }
	    
	    break;
	    	    
	case 'deleteImage':
	    $initiativeId = array_key_exists('initiativeId', $_REQUEST) ? $_REQUEST['initiativeId'] : 0;
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    $tempImage = $app->dao->getTemporaryImage($app->user['id']);
	    if ($tempImage) {
	        $app->dao->removeTemporaryImage($tempImage['id']);
	    }
	    if ($initiative) {
	        $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_MISSING;
	        $initiative['image_file'] = '';
	        $initiative['image_flickr_photo_id'] = '';
	        $app->dao->updateInitiative($initiative);
	    }
	    break;
	    
	case 'searchFlickr':
	    // http://www.flickr.com/services/rest/?method=flickr.test.echo&format=json&api_key=a3f9bcdb50af39c7b0aeb878ade82c02
	    // http://www.flickr.com/services/rest/?method=flickr.photos.licenses.getInfo&format=json&api_key=a3f9bcdb50af39c7b0aeb878ade82c02
	    // http://www.flickr.com/services/rest/?method=flickr.photos.search&api_key=a3f9bcdb50af39c7b0aeb878ade82c02&license=4,6,3,2,1,5&text=test&extras=license,url_t&per_page=300
	    // {"id":"6420306259", "owner":"34197995@N02", "secret":"0e660c4cdc", "server":"6211", "farm":7, "title":"Politibil", "ispublic":1, "isfriend":0, "isfamily":0, "license":"1", "url_t":"http:\/\/farm7.staticflickr.com\/6211\/6420306259_0e660c4cdc_t.jpg", "height_t":"67", "width_t":"100"}
	    $searchTerm = $app->getRequestValue('search', '');
	    $flickrUrl = 'http://www.flickr.com/services/rest/?method=flickr.photos.search&format=json&license=1,2,3,4,5,6&extras=license,url_t,owner_name&per_page=150&nojsoncallback=1&api_key=' . FLICKR_APP_KEY . '&text=' . urlencode($searchTerm);
        $failed = false;
	    try {
    	    $ch = curl_init($flickrUrl);
    	    curl_exec($ch);
    	    curl_close($ch);
	    } catch (Exception $e) {
	        $failed = true;
	    }
	    if ($failed) {
	        $result = Array('error' => true);
	    } else {
	        $renderResult = false; // because curl has already rendered it..
	    }
	    break;
	     	
	default:
		$this->fail(500, 'Unknown JSON command');
		break;
}

if ($renderResult) {
    echo json_encode($result);
}