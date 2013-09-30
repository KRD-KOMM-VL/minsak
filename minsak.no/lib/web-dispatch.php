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
/* @var $app AppContext */

$fragmentToRender = 'page/index';
$topFragment = 'wrapper/top';
$bottomFragment = 'wrapper/bottom';
$renderWrapped = true;
$data = Array();

header('Content-Type: text/html; charset=utf-8');

switch ($app->urlParts[0]) {
	case '':
		break;
	case 'registered-user':
	    $app->breadcrumbs[] = Array('label' => 'Registrert bruker', 'link' => '/registered-user');
	    $fragmentToRender = 'page/registered-user';
	    break;
	case 'login':
        $app->breadcrumbs[] = Array('label' => 'Logg inn', 'link' => '/login');
	    if ($_POST && array_key_exists('username', $_POST) && array_key_exists('password', $_POST) && $app->login($_POST['username'], $_POST['password'])) {
	        $location = '/';
	        if (array_key_exists('redirect', $_POST)) {
	            $location = $_POST['redirect'];
	        }
	        $app->redirect($location);
	    } else {
	        $fragmentToRender = 'page/login-form';
	    }
	    break;
	case 'forgot-password':
        $app->breadcrumbs[] = Array('label' => $app->_('Glemt passord'), 'link' => '/forgot-password');
	    if ($_POST) {
	        if (array_key_exists('username', $_POST) && $app->userForgotPassword($_POST['username'])) {
	            $fragmentToRender = 'page/forgot-password-sent';
	        } else {
	            $fragmentToRender = 'page/forgot-password-form';
	            $data['error'] = true;
	        }
	    } else {
	        $fragmentToRender = 'page/forgot-password-form';
	    }
	    break;
	case 'logout':
        $app->logout();
        $location = '/';
        if (array_key_exists('redirect', $_GET)) {
            $location = $_GET['redirect'];
        }
        $app->redirect($location);
        break;
	case 'lang':
	    $redirect = @$_GET['redirect'];
	    $code = @$_GET['code'];
	    if ($code) {
	        $app->setLanguageCookie($code);
	    }
	    if ($redirect) {
	        $app->redirect($redirect);
	    } else {
	        $app->fail(404, 'Siden finnes ikke');
	    }
	case 'user-admin':
        $app->breadcrumbs[] = Array('label' => $app->_('Brukeradministrasjon'), 'link' => '/user-admin');
	    if ($app->userIsSiteAdmin()) {
	        $fragmentToRender = 'dispatcher/user-admin';
	    } else {
	        $app->redirect('/login?redirect=' . htmlspecialchars('/user-admin'));
	    }
	    break;
	case 'sesam':
	    $key = $app->getRequestValue('key');
	    $redirect = $app->getRequestValue('redirect', '/moderer');
	    if ($key && ($user = $app->loginMagic($key)) === true) {
	        $app->redirect($redirect);
	    } else {
	        if (is_array($user)) {
	            $data['username'] = $user['username'];
	        }
	        $fragmentToRender = 'page/login-form';
	    }
	    break;
	case 'mine-saker':
		if (!$app->user) {
			$app->redirect('/');
		}
		$app->breadcrumbs[] = Array('label' => $app->_('Mine saker'), 'link' => '/mine-saker');
		$fragmentToRender = 'page/mine-saker';
		break;
	case 'moderer':
        $app->breadcrumbs[] = Array('label' => $app->_('Moderer'), 'link' => '/moderer');
	    if ($app->user) {
	    	if (count($app->urlParts) == 3) {
	    		switch ($app->urlParts[1]) {
	    			case 'sak':
	    				$initiative = $app->dao->getInitiativeById($app->urlParts[2]);
	    				if (is_array($initiative)) {
	    					$fragmentToRender = 'page/moderate-initiative';
	    					$data['initiative'] = $initiative;
	    				} else {
	    					$app->fail(404, 'Siden finnes ikke');
	    				}
	    				break;
	    			case 'kommentar':
	    				$comment = $app->dao->getCommentById($app->urlParts[2]);
	    				if (is_array($comment)) {
	    					$fragmentToRender = 'page/moderate-comment';
	    					$data['comment'] = $comment;
	    				} else {
	    					$app->fail(404, 'Siden finnes ikke');
	    				}
	    				break;
	    			default:
	    				$app->fail(404, 'Siden finnes ikke');
	    		}
	    	} else if (count($app->urlParts) == 1) {
	        	$fragmentToRender = 'page/moderate';
	    	} else {
	    		$app->fail(404, 'Siden finnes ikke');
	    	}
	    } else {
	        $app->redirect('/login?redirect=' . $app->url);
	    }
	    break;
	case 'moderer-signaturer':
	    if (!$app->user) {
	        $app->redirect('/login?redirect=' . urlencode($app->url));
	    }
	    $initiativeId = array_key_exists(1, $app->urlParts) ? $app->urlParts[1] : 0;
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    if (!is_array($initiative)) {
	        $app->fail(404, 'Siden finnes ikke');
	    }
	    if ($initiative['user_id'] != $app->user['id'] && !$app->user['isSiteAdmin'] && !$app->user['isModerator']) {
	        // require a role to be allowed to moderate
	        $userRoles = $app->dao->getUserRoles($app->user);
	        if (!array_key_exists($initiatve['location_id'], $userRoles) || !$userRoles['signature_moderator']) {
	            $app->fail(404, 'Siden finnes ikke');
	        }
	    }
	    $app->breadcrumbs[] = Array('label' => htmlspecialchars($initiative['title']), 'link' => '/sak/' . $initiativeId);
	    $app->breadcrumbs[] = Array('label' => $app->_('Moderer signaturer'), 'link' => $app->url);
	    $fragmentToRender = 'page/moderate-signatures';
	    $data['initiative'] = $initiative;
	    break;
	case 'trekk-sak':
	    if (!$app->user) {
	        $app->redirect('/login?redirect=' . urlencode($app->url));
	    }
	    $initiativeId = array_key_exists(1, $app->urlParts) ? $app->urlParts[1] : 0;
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    if (!is_array($initiative)) {
	        $app->fail(404, 'Siden finnes ikke');
	    }
	    if ($initiative['user_id'] == $app->user['id']) {
    	    $app->breadcrumbs[] = Array('label' => htmlspecialchars($initiative['title']), 'link' => '/sak/' . $initiativeId);
    	    $app->breadcrumbs[] = Array('label' => $app->_('Trekk saken'), 'link' => $app->url);
    	    $fragmentToRender = 'page/delete-initiative';
    	    $data['initiative'] = $initiative;
	    } else {
	        $app->fail(404, 'Siden finnes ikke');
	    }
	    break;
 	case 'profil':
	    $app->breadcrumbs[] = Array('label' => 'Profil', 'link' => '/profil');
        $fragmentToRender = 'page/profile';
	    break;
	case 'fylker-og-kommuner':
        $app->breadcrumbs[] = Array('label' => 'Fylker og kommuner', 'link' => '/fylker-og-kommuner');
	    $fragmentToRender = 'page/fylker-og-kommuner';
		break;
	case 'ajax':
	    $fragmentToRender = 'ajax';
	    $renderWrapped = false;
	    break;
	case 'om-minsak':
		$app->breadcrumbs[] = Array('label' => 'Om minsak.no', 'link' => '/om-minsak');
		$fragmentToRender = 'page/om-minsak';
		break;
	case 'retningslinjer':
		$app->breadcrumbs[] = Array('label' => 'Retningslinjer', 'link' => '/retningslinjer');
		$fragmentToRender = 'page/retningslinjer';
		break;
	case 'sak':
	    $app->breadcrumbs[] = Array('label' => 'Sak', 'link' => $app->url);
		$fragmentToRender = 'page/initiative';
		$initiativeId = array_key_exists(1, $app->urlParts) ? $app->urlParts[1] : 0;
		// Get initiative
		$initiative = $app->dao->getInitiativeById($initiativeId);
		if (!is_array($initiative)) {
			$app->fail(404, 'Siden finnes ikke');
		}
		$status = $initiative['status'];
		if ($status == AppContext::INITIATIVE_STATUS_OPEN ||
		    $status == AppContext::INITIATIVE_STATUS_SCREENING ||
		    $status == AppContext::INITIATIVE_STATUS_COMPLETED ||
		    ($status == AppContext::INITIATIVE_STATUS_UNMODERATED && $app->user && $app->user['id'] == $initiative['user_id'])) {

		$data['initiative'] = $initiative;
		$data['subaction'] = '';
		} else {
		    $app->fail(404, 'Siden finnes ikke');
		}
		
		// Look for subactions
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$subAction = array_key_exists(2, $app->urlParts) ? $app->urlParts[2] : null;
			if ($subAction !== null) {
				$data['subaction'] = $subAction;
				switch ($subAction) {
					case 'kommentar':
					case 'signatur':
					case 'email':
						// pass-through to initiative page, which has a post handler for signing and commenting
						break;
					default:
						$app->fail(404, 'Siden finnes ikke');
						break;
				}
			}
		}
		
		$renderWrapped = true;
		break;
	case 'signaturer':
	    $initiativeId = array_key_exists(1, $app->urlParts) ? $app->urlParts[1] : 0;
	    $initiative = $app->dao->getInitiativeById($initiativeId);
	    if (!is_array($initiative)) {
	        $app->fail(404, 'Siden finnes ikke');
	    }
	    $app->breadcrumbs[] = Array('label' => htmlspecialchars($initiative['title']), 'link' => '/sak/' . $initiativeId);
	    $app->breadcrumbs[] = Array('label' => $app->_('Signaturer'), 'link' => $app->url);
	    $fragmentToRender = 'page/signatures';
	    $data['initiative'] = $initiative;
	    break;
	case 'rediger-sak':
	    $fragmentToRender = 'page/edit-initiative-form';
	    $app->breadcrumbs[] = Array('label' => 'Rediger sak', 'link' => '/rediger-sak');
	    break;
	case 'flickr':
	    $fragmentToRender = 'page/flickr';
	    $app->breadcrumbs[] = Array('label' => 'Velg Flickr-bilde', 'link' => $app->url);
	    break;
	case 'sok':
		$data['wrapper_class'] = 'initiative-list';
		$fragmentToRender = 'page/sok';
		$app->breadcrumbs[] = Array('label' => 'Søk', 'link' => '/sok');
		break;
	case 'oss':
	    $fragmentToRender = 'page/oss';
	    $app->breadcrumbs[] = Array('label' => 'Ofte stilte spørsmål', 'link' => '/oss');
	    break;
	case 'statistikk':
        $app->breadcrumbs[] = Array('label' => $app->_('Statistikk'), 'link' => '/statistikk');
	    if ($app->userIsSiteAdmin()) {
	        $fragmentToRender = 'page/statistics';
	    } else {
	        $app->redirect('/login?redirect=' . htmlspecialchars('/statistikk'));
	    }
	    break;
	default:
	    $fragmentToRender = 'page/location';
		$currentLocationSlug = $app->urlParts[0];
		$app->setLocationFromSlug($currentLocationSlug); // this will cause a fail if municipality or county slug does not exist
		break;
}

$app->log('url:' . $app->url . ' fragmentToRender:' . $fragmentToRender);
if ($renderWrapped) {
    $app->renderWrappedFragment($fragmentToRender, $data, $topFragment, $bottomFragment);
} else {
    $app->renderFragment($fragmentToRender, $data);
}