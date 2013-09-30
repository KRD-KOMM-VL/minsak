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
 * Details page for one initiative
 * 
 */ 
global $app;

$initiative = $data['initiative'];
$initiativeUrl = '/sak/' . intval($initiative['id']);
$canonicalInitiativeUrl = $app->getInitiativeCanonicalURL($initiative['id']);
$locInfo = $app->dao->getLocationExtendedInfoById($initiative['location_id']);

$userIsOwner = false;
if ($initiative['user_id'] == $app->user['id']) {
    $userIsOwner = true;
}
$userIsModerator = false;
if ($app->user) {
    if ($userIsOwner || $app->user['isSiteAdmin'] || $app->user['isModerator']) {
        $userIsModerator = true;
    } else {
        // require a role to be allowed to moderate
        $userRoles = $app->dao->getUserRoles($app->user);
        if (array_key_exists($initiative['location_id'], $userRoles) && $userRoles['signature_moderator']) {
            $userIsModerator = true;
        }
    }
}

require_once(BASEDIR.'/lib/recaptchalib.php');
$emailParams = array(
    'to' => array(
        'validators' => array(
            array('type' => 'email', 'params' => Array(), 'message' => 'Ugyldig e-postadresse')
        )
    ),
    'from' => array(
        'validators' => array(
            array('type' => 'string', 'params' => Array('minLength' => 3), 'message' => $app->_('For kort avsendertekst'))
        )
    ),
    'message' => array(
        'validators' => array(
            array('type' => 'string', 'params' => Array('minLength' => 5), 'message' => 'For kort meldingstekst')
        )
    ),
);
$emailValidator = new FormValidator($emailParams, 'send-email-form');
$emailIsCommit = false;
$emailValidationErrors = null;
$emailValidationMissing = null;

$signParams = array(
	'sign_name' => array(
		'validators' => array(
			array('type' => 'string', 'params' => array('pattern' => '/\\S+\\s\\S+/'), 'message' => $app->_('Fullt navn er påkrevd'))
		)
	),
	'sign_address' => array(
		'validators' => array(
			array('type' => 'string', 'params' => array('minLength' => 1), 'message' => 'Du må fylle inn adresse')
		)
	),
	'sign_areacode' => array(
		'validators' => array(
			array('type' => 'string', 'params' => array('pattern' => '/^[0-9]{4}$/'), 'message' => 'Ugyldig postnummer')
		)
	),
//	'recaptcha_challenge_field' => array(
//		'validators' => array(
//			array('type' => 'recaptcha', 'params' => array('privateKey' => RECAPTCHA_PRIVATE_KEY), 'message' => 'Ugyldig recaptcha svar')
//		)
//	)
);
$signValidator = new FormValidator($signParams, 'initiative-sign-form');
$signIsCommit = false;
$signValidationErrors = null;
$signValidationMissing = null;


$commentNamePlaceholderString = 'Skriv inn navn';
$commentTextPlaceholderString = 'Skriv inn kommentar';
$commentParams = array(
	'comment_name' => array(
		'validators' => array(
			array('type' => 'string', 'params' => array('pattern' => '/\\S+\\s\\S+/', 'rejectplaceholder' => $commentNamePlaceholderString), 'message' => 'Fullt navn er påkrevd')
		)
	),
	'comment_text' => array(
		'validators' => array(
			array('type' => 'string', 'params' => array('minLength' => 2, 'rejectplaceholder' => $commentTextPlaceholderString), 'message' => $app->_('Mangler tekst'))
		)
	),
	'recaptcha_challenge_field' => array(
		'validators' => array(
			array('type' => 'recaptcha', 'params' => array('privateKey' => RECAPTCHA_PRIVATE_KEY), 'message' => 'Ugyldig recaptcha-svar')
		)
	)
);
$commentValidator = new FormValidator($commentParams, 'initiative-comment-form');
$commentIsCommit = false;
$commentValidationErrors = null;
$commentValidationMissing = null;
$commentIsFail = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($data['subaction']) {
		case 'signatur':
			// validate form; if successful, redirect back to main page, otherwise fill in errors
			$signIsCommit = true;
			$signValidationResult = $signValidator->validatePostParameters();
			$signValidationErrors = $signValidationResult['errors'];
			$signValidationMissing = $signValidationResult['missing'];
			
			if (count($signValidationErrors) == 0 && count($signValidationMissing) == 0) {
				// signature accepted
				$result = $app->dao->addSignature($_POST['sign_name'], $_POST['sign_address'], $_POST['sign_address2'], $_POST['sign_areacode'], $initiative['id'], $locInfo['auto_moderate_signature']);
				if ($result) {
					$app->redirect($initiativeUrl . '?msg=sign');
				}
				else {
					$this->fail(500, 'Internal server error', 'Det oppstod en feil idet signaturen skulle lagres i databasen. Prøv igjen senere');
				}
			}
			break;
		case 'kommentar':
			// validate form; if successful, redirect back to main page, otherwise fill in errors
			$commentIsCommit = true;
			$commentValidationResult = $commentValidator->validatePostParameters();
			$commentValidationErrors = $commentValidationResult['errors'];
			$commentValidationMissing = $commentValidationResult['missing'];
			if (count($commentValidationErrors) == 0 && count($commentValidationMissing) == 0) {
				// comment accepted
				$result = $app->dao->addComment($_POST['comment_name'], $_POST['comment_text'], $initiative['id']);
				if ($result) {
					$comment = $app->dao->getCommentById($result);
				    // send notification to site admins
				    $siteAdmins = $app->dao->getSiteAdmins();
				    if (is_array($siteAdmins)) {
				        foreach ($siteAdmins as $siteAdmin) {
				            $magic = $app->generateUserAccessKey($siteAdmin);
				            Mail::sendSiteAdminInitiativeCommented($siteAdmin, $initiative, $comment, $_POST['comment_name'], $_POST['comment_text'], $magic);
				        }
				    }
				    
					$app->redirect($initiativeUrl . '?msg=comment');
				}
				else {
					$this->fail(500, 'Internal server error', 'Det oppstod en feil idet kommentaren skulle lagres i databasen. Prøv igjen senere');
				}
			} else {
				$commentIsFail = true;
			}
			break;
		case 'email':
		    $emailIsCommit = true;
		    $emailValidationResult = $emailValidator->validatePostParameters();
		    $emailValidationErrors = $emailValidationResult['errors'];
		    $emailValidationMissing = $emailValidationResult['missing'];
		    if (count($emailValidationErrors) == 0 && count($emailValidationMissing) == 0) {
		        $to = $_POST['to'];
		        $from = strip_tags($_POST['from']);
		        $message = strip_tags($_POST['message']);
		        Mail::sendInitiativeEmail($canonicalInitiativeUrl, $to, $from, $message);
		        $app->redirect($initiativeUrl . '?msg=email');
		    }
		    break;
		default:
			// shouldn't happen (web-dispatch filters subactions, too)
			$app->fail(404, 'Page not found');
			break;
	}
	
}



$votesArray = $app->dao->getValidSignatureCountsForInitiatives(array($initiative['id']));
$votes = $votesArray[$initiative['id']];
$imageUrl = $app->getInitiativeImageURL($initiative, AppContext::INITIATIVE_IMAGE_SIZE_FULL);
$sigReq = $locInfo['signatures_required'];
$votePercent = ($sigReq >= 1) ? min(100,round(100*$votes/$sigReq)) : 0;


// If the initiative has received enough votes, change status to 'screening' and send an email to the initiative owner
if ($votes >= $sigReq && $initiative['status'] == AppContext::INITIATIVE_STATUS_OPEN) {
    $initiativeOwner = $app->dao->getUserById($initiative['user_id']);
    $initiativeStatus = Array(
        'initiative_id' => $initiative['id'],
        'prev_status' => $initiative['status'],
        'change_time' => time(),
        'user_id' => 0 // no user caused this, the number of signatures just got large enough
    );
    $initiativeStatus['current_status'] = $initiative['status'] = AppContext::INITIATIVE_STATUS_SCREENING;
    $app->dao->updateInitiative($initiative);
    $app->dao->addInitiativeStatus($initiativeStatus);
    Mail::sendInitiativeScreeningMail($initiative, $initiativeOwner['username'], $app->generateUserAccessKey($initiativeOwner));
    
    // send notification to site admins
    $siteAdmins = $app->dao->getSiteAdmins();
    if (is_array($siteAdmins)) {
        foreach ($siteAdmins as $siteAdmin) {
            $magic = $app->generateUserAccessKey($siteAdmin);
            Mail::sendSiteAdminInitiativeChangedStatus($siteAdmin, $initiative, AppContext::INITIATIVE_STATUS_OPEN, AppContext::INITIATIVE_STATUS_SCREENING, $magic);
        }
    }
}

$this->htmlHeadExtra .= '<meta property="og:title" content="'.htmlspecialchars($initiative['title']).'">'
.'<meta property="og:type" content="cause">'
.'<meta property="og:url" content="'.htmlspecialchars($canonicalInitiativeUrl).'">'
.'<meta property="og:image" content="'.htmlspecialchars(BASE_URL . $imageUrl).'">'
.'<meta property="og:description" content="'.htmlspecialchars($initiative['text']).'">'
;

$comments = $app->dao->getCommentsForInitiative($initiative['id']);

?>

<script type="text/javascript"><!--
<?php echo $signValidator->getJavascriptValidator(); ?>
<?php echo $commentValidator->getJavascriptValidator(); ?>
<?php echo $emailValidator->getJavascriptValidator(); ?>
// --></script>


			<!-- main -->
			<div id="main">
				<!-- content -->
				<div id="content">
					<!-- content-area -->

					<div class="content-area">
						<!-- img-block -->
						<div class="img-block">
							<img src="<?php echo htmlspecialchars($imageUrl);?>" width="320" height="220" alt="<?php echo htmlspecialchars($initiative['title']); ?>" />
							<em class="note"><?php echo htmlspecialchars($initiative['image_credits']); ?></em>
						</div>
						<!-- info -->
						<div class="info">
                        
                            <?php
                            if (array_key_exists('msg', $_REQUEST)) {
                                echo '<div class="message-box">';
                                switch ($_REQUEST['msg']) {
                                    case 'email':
                                        echo $app->_('E-post har blitt sendt');
                                        break;
                                    case 'comment':
                                        echo $app->_('Din kommentar er lagt inn');
                                        break;
                                    case 'sign':
                                        echo $app->_('Din signatur er mottatt');
                                        break;
                                    case 'new':
                                        echo $app->_('Din sak har blitt opprettet');
                                        break;
                                }
                                echo '</div>';
                            }
                            ?>

							<h1><?php echo htmlspecialchars($initiative['title']); ?></h1>
		                    <?php echo '<span style="white-space: pre-line">' . htmlspecialchars($initiative['text']) . '</span>'; ?>
							
							<dl class="meta" style="margin-top: 10px;">
								<dt><?php echo $app->_('Opprettet'); ?>:</dt>
								<dd><?php echo htmlspecialchars(date('d.m.Y', $initiative['created_time'])); ?> av <?php echo htmlspecialchars($initiative['name']); ?></dd>
								<dt><?php echo $app->_('Mottaker'); ?>:</dt>
								<dd><?php echo htmlspecialchars($locInfo['extra']['name']); ?></dd>

								<dt>Sluttdato for signering:</dt>
								<dd><?php echo htmlspecialchars(date('d.m.Y', $initiative['end_date'])); ?></dd>
							</dl>
							<!-- vote-block -->
							<div class="vote-block">
								<strong><?php echo htmlspecialchars($votes);?> av <?php echo htmlspecialchars($locInfo['signatures_required']);?> underskrifter</strong>
								<div class="vote-bar">

									<span style="width:<?php echo htmlspecialchars($votePercent); ?>%">&nbsp;</span>
								</div>
							</div>
                            <?php if ($initiative['end_date'] > time() && ($initiative['status'] == AppContext::INITIATIVE_STATUS_OPEN || $initiative['status'] == AppContext::INITIATIVE_STATUS_SCREENING)) { ?>
							<div class="sign-holder">
								<a tabindex="14" class="open-popup" style="margin-right: 20px;" href="#popup2" id="sign-open-popup-link" >Skriv under sak</a>
                                <a tabindex="14" class="signatures-link" href="/signaturer/<?php echo $initiative['id']; ?>"><?php echo $app->_('Se underskrifter');?></a>
                            </div>
                            <?php
                            } else if ($initiative['status'] == AppContext::INITIATIVE_STATUS_UNMODERATED) {
                                echo $app->_('Denne saken må godkjennes av redaktøren før det åpnes');
                            } else {
                                echo $app->_('Denne saken er lukket og kan ikke skrives under på lenger');
                            }
                            if ($userIsModerator) {
                                echo '<div class="sign-holder"><a href="/moderer-signaturer/' . $initiative['id'] . '">Forkaste og godkjenne underskrifter</a></div>';
                            }
                            if ($userIsOwner) {
                                echo '<div class="delete-holder"><a href="/trekk-sak/' . $initiative['id'] . '">' . $app->_('Trekk saken') . '</a></div>';
                            }
                            ?>
						</div>
					</div>

    <?php if ($initiative['end_date'] > time() && ($initiative['status'] == AppContext::INITIATIVE_STATUS_OPEN || $initiative['status'] == AppContext::INITIATIVE_STATUS_SCREENING)) { ?>
	<div class="lightbox" id="popup2">
		<div class="holder">
			<div class="content">
				<!-- heading -->
				<div class="heading">
					<strong class="title"><?php echo $app->_('Skriv under denne saken'); ?></strong>

					<a href="#" class="close">Avbryt</a>
				</div>
				<p><?php echo $app->_('Fyll inn navn, adresse og postnummer for å signere saken. Informasjonen må oppgis slik at signaturen din kan verifiseres.'); ?></p>
				<form id="initiative-sign-form" class="sign-form" action="<?php echo htmlspecialchars($initiativeUrl);?>/signatur" method="post" onsubmit="return <?php echo $signValidator->getJavascriptValidatorFunctionName();?>();">
					<fieldset>
						<div class="area nofloat">
							<!-- row -->
							<div class="row">

								<label for="sign_name"><?php echo $app->_('Navn'); ?>:</label>
								<div class="address-box">
									<span class="txt"><input class="default" type="text" name="sign_name" id="sign_name" value="<?php echo $app->getEscapedPostValue('sign_name', ''); ?>" /></span>
									<?php echo $signValidator->getErrorBlock('sign_name', $signIsCommit, $signValidationErrors, $signValidationMissing); ?>
								</div>
							</div>
							<!-- row -->
							<div class="row">
								<label for="sign_address">Adresse:</label>
								<div class="address-box">
									<span class="txt"><input class="default" type="text" name="sign_address" id="sign_address" value="<?php echo $app->getEscapedPostValue('sign_address', ''); ?>" /></span>
									<span class="txt"><input class="default" type="text" name="sign_address2" id="sign_address2" value="<?php echo $app->getEscapedPostValue('sign_address2', ''); ?>" /></span>
									<?php echo $signValidator->getErrorBlock('sign_address', $signIsCommit, $signValidationErrors, $signValidationMissing); ?>
								</div>
							</div>
							<!-- row -->
							<div class="row">
								<label for="sign_areacode">Postnummer:</label>
								<div class="address-box">
									<span class="txt txt6"><input class="default" type="text" name="sign_areacode" id="sign_areacode" value="<?php echo $app->getEscapedPostValue('sign_areacode', ''); ?>"/></span>
									<?php echo $signValidator->getErrorBlock('sign_areacode', $signIsCommit, $signValidationErrors, $signValidationMissing); ?>
								</div>
							</div>

						</div>
						<div class="placeholder">
							<?php /* TODO: fix this...: echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY); */ ?>
							<?php echo $signValidator->getErrorBlock('recaptcha_challenge_field', $signIsCommit, $signValidationErrors, $signValidationMissing); ?>
						</div>
						<div class="button-row">
							<a class="submit invisible-nonjs" href="#" onclick="jQuery('#submit-signature').click(); return false;">Skriv under</a>
						</div>
						<input class="invisible-js" id="submit-signature" type="submit" value="Signer" />
					</fieldset>
				</form>
			</div>
		</div>
		<div class="lightbox-b">&nbsp;</div>
	</div>
    <?php } ?>

    <h2 class="comment-on"><a href="#popup3" class="open-popup" id="comment-open-popup-link">Kommenter sak</a></h2>
    
	<div class="lightbox" id="popup3">
		<div class="holder">
			<div class="content">
				<!-- heading -->
				<div class="heading">
					<strong class="title">Kommenter denne saken</strong>

					<a href="#" class="close">Avbryt</a>
				</div>
				
					<form id="initiative-comment-form" class="comment-form" action="<?php echo htmlspecialchars($initiativeUrl.'/kommentar'); ?>" method="post">
						<fieldset>
						<?php
						  $fieldValue = $app->getPostValue('comment_name', '');
						  $fieldIsDefault = false;
						  $fieldClass = 'default'; // prevent click-to-clear
						  if ($fieldValue == '') {
						  	$fieldIsDefault = true;
						  	$fieldValue = $commentNamePlaceholderString;
						  	$fieldClass = '';
						  }
						?>
						<span class="txt"><input tabindex="80" type="text" class="<?php echo $fieldClass ;?>" title="Skriv inn navn" value="<?php echo htmlspecialchars($fieldValue); ?>" id="comment_name" name="comment_name" /></span>
						<?php echo $commentValidator->getErrorBlock('comment_name', $commentIsCommit, $commentValidationErrors, $commentValidationMissing); ?>
						<div class="row">
							<div class="text-area">
								<div class="text-area-holder" style="width:auto; padding: 0;">
									<div class="frame">
									<?php
									  $fieldValue = $app->getPostValue('comment_text', '');
									  $fieldIsDefault = false;
									  $fieldClass = 'default'; // prevent click-to-clear
									  if ($fieldValue == '') {
									  	$fieldIsDefault = true;
									  	$fieldValue = $commentTextPlaceholderString;
									  	$fieldClass = '';
									  }
									?>
										<textarea tabindex="81" class="<?php echo $fieldClass ;?>" title="Skriv inn kommentar" cols="30" rows="10" name="comment_text" id="comment_text"><?php echo htmlspecialchars($fieldValue);?></textarea>

									</div>
								</div>
							</div>
							<?php echo $commentValidator->getErrorBlock('comment_text', $commentIsCommit, $commentValidationErrors, $commentValidationMissing); ?>
						</div>
						<div class="row">
							<?php echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, null, true); ?>
							<?php echo $commentValidator->getErrorBlock('recaptcha_challenge_field', $commentIsCommit, $commentValidationErrors, $commentValidationMissing); ?>
						</div>
						<input tabindex="18" class="submit" type="submit" value="Send" onclick="return <?php echo $commentValidator->getJavascriptValidatorFunctionName();?>();"/>						
						
						</fieldset>
					</form>
				
			</div>
		</div>
		<div class="lightbox-b">&nbsp;</div>
	</div>
    
    

					<!-- comment-area-->
					<div class="comment-area">
					<?php
					$commentcount = count($comments);
					$commentcounttext = $commentcount.' ' . $app->_('kommentarer') . ':';
					switch ($commentcount) {
						case 0:
							$commentcounttext = 'Ingen ' . $app->_('kommentarer') . ':';
							break;
						case 1:
							$commentcounttext = 'En kommentar:';
							break;
					}
					?>
						<h3><?php echo $commentcounttext; ?></h3>
						<ul>
						<?php
						foreach ($comments as $comment) {
							?>
							<li>
								<div class="row">
									<em class="date"><?php echo htmlspecialchars($app->_('weekday'.date('w',$comment['created_time'])).' '.date('d.m.Y', $comment['created_time'])); ?></em>
								</div>

								<strong><?php echo htmlspecialchars($comment['name']);?> sier</strong>:
								<?php echo htmlspecialchars($comment['text']); ?>
							</li>
							<?php 
						}  
						?>
						</ul>

					</div>
				</div>
	<!-- sidebar -->
	<div id="sidebar">
		<!-- add-nav -->
		<?php $app->renderFragment('widget/menu');?>

		<!-- social-block -->
		<div class="social-block">
			<h3>Del &ldquo;<?php echo $initiative['title']; ?>&rdquo;</h3>

			<div class="holder" style="padding-top: 8px;">
				<span tabindex="27" class="facebook" href="#">Facebook</span>
			</div>
			<div class="placeholder" style="padding-top: 4px;">
                <fb:like href="<?php echo htmlspecialchars($canonicalInitiativeUrl); ?>" send="false" layout="button_count" width="160" show_faces="false"></fb:like>
			</div>
            <div class="holder" style="padding-top: 8px;">
                <a tabindex="28" class="twitter" target="_blank" href="http://twitter.com/share?url=<?php echo urlencode($canonicalInitiativeUrl); ?>">Twitter</a>
            </div>
            <div class="holder" style="padding-top: 8px;">
			    <a tabindex="29" class="epost open-popup" href="#popup1">E-post</a>
            </div>
            <div class="holder" style="padding-top: 8px;">
                <span tabindex="30" class="google" href="#">Google +</span>
            </div>
            <div style="padding-top: 4px;">
                <div class="g-plusone" data-size="medium" data-href="<?php echo htmlspecialchars($canonicalInitiativeUrl); ?>"></div>
            </div>
		</div>
		
	</div>

</div>
<div class="lightbox" id="popup1">
    <div class="holder">
        <div class="content">
            <!-- heading -->
            <div class="heading">
                <strong class="title">Del &ldquo;<?php echo htmlspecialchars($initiative['title']); ?>&rdquo; via e-post</strong>
                <a href="#" class="close">Avbryt</a>
            </div>
            <!-- send-form -->
            <form id="send-email-form" class="send-form" method="post" action="<?php echo htmlspecialchars($initiativeUrl);?>/email" onsubmit="return <?php echo $emailValidator->getJavascriptValidatorFunctionName();?>();">
				<fieldset>
                    <div class="area">
                        <!-- row -->
						<div class="row">
                            <label for="to">Til:</label>
                            <span class="txt"><input id="to" name="to" type="text" /></span>
                            <?php echo $emailValidator->getErrorBlock('to', $emailIsCommit, $emailValidationErrors, $emailValidationMissing); ?>
                        </div>
                        <!-- row -->
				    	<div class="row">
                            <label for="from">Fra:</label>
                            <span class="txt"><input id="from" name="from" type="text" /></span>
                            <?php echo $emailValidator->getErrorBlock('from', $emailIsCommit, $emailValidationErrors, $emailValidationMissing); ?>
                        </div>
                        <!-- row -->
                        <div class="row">
							<label for="message">Melding:</label>
                            <div class="textarea">
                                <div class="textarea-holder">
                                    <div class="textarea-frame">
                                        <textarea id="message" name="message" cols="30" rows="10"></textarea>
									</div>
                                </div>
                            </div>
                            <?php echo $emailValidator->getErrorBlock('message', $emailIsCommit, $emailValidationErrors, $emailValidationMissing); ?>
						</div>
                    </div>
                    <div class="button-row">
                        <a class="submit invisible-nonjs" href="#" onclick="jQuery('#send-email-form').submit(); return false;">Send</a>
                        <input class="invisible-js" type="submit" value="Send e-post" />
					</div>
                </fieldset>
            </form>
        </div>
    </div>
	<div class="lightbox-b">&nbsp;</div>
</div>

<?php 
if (count($signValidationErrors) > 0 || count($signValidationMissing) > 0) {
	// Force open the lightbox on load, to show the errors (typically recaptcha error)
?>
<script type="text/javascript"><!--
jQuery(document).ready(function(){jQuery('#sign-open-popup-link').click()});
// --></script>
<?php 
} else if ($commentIsFail) {
?>
<script type="text/javascript"><!--
jQuery(document).ready(function(){jQuery('#comment-open-popup-link').click()});
// --></script>
<?php	
}
