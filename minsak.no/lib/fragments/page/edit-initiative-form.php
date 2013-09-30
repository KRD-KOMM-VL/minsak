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

require_once(BASEDIR . '/lib/recaptchalib.php');
$initiative = Array();
$isCommit = false;
$validationErrors = Array();
$validationMissing = Array();
$imageInfo = Array();
if ($app->user) {
    if (count($app->urlParts) > 1) {
        $i = $app->dao->getInitiativeById($app->urlParts[1]);
        if ($i && $i['status'] == AppContext::INITIATIVE_STATUS_DRAFT && $i['user_id'] == $app->user['id']) {
            $initiative = $i;
            $drafts = $app->dao->getDraftInitiativesByUser($app->user, $initiative['id']);
        } else {
            $app->fail(404, "Siden finnes ikke");
        }
    } else {
        $drafts = $app->dao->getDraftInitiativesByUser($app->user);
    }
    $tempImage = $app->dao->getTemporaryImage($app->user['id']);
    if (array_key_exists('image_type', $initiative) && $initiative['image_type'] != 'missing') {
        $imageInfo['image_type'] = $initiative['image_type'];
        $imageInfo['image_file'] = $initiative['image_file'];
        $imageInfo['image_flickr_photo_id'] = $initiative['image_flickr_photo_id'];
    } else if ($tempImage) {
        $imageInfo['image_type'] = $tempImage['image_type'];
        $imageInfo['image_file'] = $tempImage['image_file'];
        $imageInfo['image_flickr_photo_id'] = $tempImage['image_flickr_photo_id'];
        $imageInfo['image_credits'] = $tempImage['image_flickr_image_credits'];
    }
    $postParams = Array(
        'locationId' => Array(
            'validators' => Array(
                Array('type' => 'set', 'params' => $app->dao->getLocationIds(), 'message' => $app->_('Du må velge en kommune eller et fylke som mottaker')) 
            )
        ),
    	'sender' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('pattern' => '/\S{2,}/'), 'message' => $app->_('Ugyldig avsender'))
            )
        ),
        'name' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('pattern' => '/\S\.?\s\S/'), 'message' => $app->_('Ugyldig navn'))
            )
        ),
        'address' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('pattern' => '/\S/'), 'message' => $app->_('Ugyldig adresse'))
            )
        ),
        'zipcode' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('pattern' => '/^\d{4}$/'), 'message' => $app->_('Ugyldig postnummer'))
            )
        ),
        'enddate' => Array(
            'validators' => Array(
                Array('type' => 'date', 'params' => Array(), 'message' => $app->_('Ugyldig dato'))
            )
        ),
        'title' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('minLength' => 1), 'message' => $app->_('Ugyldig tittel'))
            )
        ),
    	'description' => Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('minLength' => 10), 'message' => $app->_('Ugyldig beskrivelse'))
            )
        ),
        'rules' => Array(
            'validators' => Array(
                Array('type' => 'checkbox', 'params' => Array('requiredValue' => 'on'), 'message' => $app->_('Du må godta reglene'))
            )
        ),
        'recaptcha_challenge_field' => Array(
            'validators' => Array(
                Array('type' => 'recaptcha', 'params' => Array('privateKey' => RECAPTCHA_PRIVATE_KEY), 'message' => 'Ugyldig recaptcha-svar')
            )
        )
    );
    if (array_key_exists('image-upload', $_FILES) || $tempImage) {
        $postparams['image-upload'] = Array(
        	'validators' => Array(
                Array('type' => 'filetype', 'params' => Array('jpg','png','gif'), 'message' => $app->_('Bildet du har lastet opp må være av typen jpg, png eller gif')),
                Array('type' => 'fileuploaded', 'params' => Array(), 'message' => $app->_('Noe har gått veldig galt med bildeopplastingen')),
                Array('type' => 'fileisimage', 'params' => Array(), 'message' => $app->_('Bildet du har lastet opp er ugyldig'))
            ),
            'isFile' => true
        );
    }
    if ($imageInfo) {
        $postParams['photographer'] = Array(
            'validators' => Array(
                Array('type' => 'string', 'params' => Array('minLength' => 1), 'message' => $app->_('Ugyldig bildetekst/fotograf'))
            )
        );
    }
    $fv = new FormValidator($postParams, 'edit-initiative-form');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isCommit = true;
        $isValidated = false;
        if ($app->getPostValue('operation') == 'cancel' || $app->getPostValue('submit') == 'Avbryt') {
            $app->log("cancelling initiative");
            if (array_key_exists('id', $initiative)) {
                $app->log("deleting initiative");
                $app->dao->deleteInitiative($initiative['id']);
            }
            if ($tempImage) {
                $app->log("deleting temp image");
                $app->dao->removeTemporaryImage($tempImage['id']);
            }
            $app->redirect('/'); // will terminate script after redirecting
        } else if ($app->getPostValue('submit') == 'Velg bilde fra flickr') {
            // for non-javascript users: submit (without validation) and redirect to flickr-selection page
            $initiative['location_id'] = $app->stripToNumeric($app->getPostValue('locationId'));
            $initiative['sender'] = $app->getPostValue('sender');
            $initiative['name'] = $app->getPostValue('name');
            $initiative['address'] = $app->getPostValue('address');
            $initiative['zipcode'] = $app->getPostValue('zipcode');
            $enddate = $app->getPostValue('enddate');
            if (preg_match('/^\d{2}\/\d{2}\/(\d{2}|\d{4})$/', $enddate) == 1) {
                list($d, $m, $y) = explode('/', $app->getPostValue('enddate'));
                if (strlen($y) == 2) {
                    $y = '20' . $y;
                }
                $initiative['end_date'] = mktime(23,59,59,$m,$d,$y);
            } else {
                $initiative['end_date'] = '';
            }
            $initiative['title'] = $app->getPostValue('title');
            $initiative['text'] = $app->getPostValue('description');
            $initiative['status'] = AppContext::INITIATIVE_STATUS_DRAFT;
            $initiative['user_id'] = $app->user['id'];
            $initiative['image_credits'] = $app->getPostValue('photographer');
            $savedInitiative = $app->dao->updateInitiative($initiative);
            if ($savedInitiative) {
                $app->redirect('/flickr/' . $savedInitiative['id']);
            }
        } else if ($app->getPostValue('submit') == 'Fjern bilde') {
            if (array_key_exists('id', $initiative)) {
                $initiative['image_file'] = '';
                $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_MISSING;
                $initiative['image_flickr_photo_id'] = '';
                $app->dao->updateInitiative($initiative);
                if ($tempImage) {
                    $app->dao->removeTemporaryImage($tempImage['id']);
                    $tempImage = Array();
                }
                $imageInfo = Array();
            }
            
        } else {
            $isValidated = true;
            $validationResult = $fv->validatePostParameters();
            $validationErrors = $validationResult['errors'];
            $validationMissing = $validationResult['missing'];
        }
    }
    
    if ($isCommit && $isValidated && count($validationErrors) + count($validationMissing) == 0) {
        $locationId = $app->stripToNumeric($app->getPostValue('locationId'));
        $location = $app->dao->getLocationById($locationId);
        $initiative['created_time'] = time();
        $initiative['location_id'] = $locationId;
        $initiative['sender'] = $app->getPostValue('sender');
        $initiative['name'] = $app->getPostValue('name');
        $initiative['address'] = $app->getPostValue('address');
        $initiative['zipcode'] = $app->getPostValue('zipcode');
        list($d, $m, $y) = explode('/', $app->getPostValue('enddate'));
        if (strlen($y) == 2) {
            $y = '20' . $y;
        }
        $initiative['end_date'] = mktime(23,59,59,$m,$d,$y);
        $initiative['title'] = $app->getPostValue('title');
        $initiative['text'] = $app->getPostValue('description');
        if ($location['auto_moderate_initiative']) {
            $initiative['status'] = AppContext::INITIATIVE_STATUS_OPEN;
        } else {
            $initiative['status'] = AppContext::INITIATIVE_STATUS_UNMODERATED;
        }
        $initiative['user_id'] = $app->user['id'];
        
        if (array_key_exists('image-upload', $_FILES)) {
            // image uploaded from non-javascript client
            $fileNameBase = time() . '_' . rand(100000000, 999999999);
            $imageOriginalName = $_FILES['image-upload']['name'];
            $imageExtensionIncDot = substr($imageOriginalName, strrpos($imageOriginalName, '.'));
            $originalFile = IMAGE_UPLOAD_DIR . $fileNameBase . $imageExtensionIncDot;
            if (move_uploaded_file($_FILES['image-upload']['tmp_name'], $originalFile)) {
                $variants = $app->getInitiativeImageVariants($fileNameBase, $imageExtensionIncDot);
                if (ImageUtils::convertImageToVariants($originalFile, $variants)) {
                    $initiative['image_file'] = $fileNameBase;
                    $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_LOCAL;
                    $app->dao->updateInitiative($initiative);
                } else {
                    $app->fail(500, 'Server error');
                }
            } else {
                $app->fail(500, 'Server error');
            }
            if ($tempImage) {
                $app->dao->removeTemporaryImage($tempImage['id']);
            }
            
        } else if ($tempImage) {
            $initiative['image_type'] = $tempImage['image_type'];
            $initiative['image_file'] = $tempImage['image_file'];
            $initiative['image_flickr_photo_id'] = $tempImage['image_flickr_photo_id'];
            $initiative['image_credits'] = $tempImage['image_flickr_image_credits'];
        }
        if ($tempImage) {
            $this->dao->removeTemporaryImage($tempImage['id']);
        }
        
        $savedInitiative = $app->dao->updateInitiative($initiative);
        
        // send notification to site admins
        $siteAdmins = $app->dao->getSiteAdmins();
        if (is_array($siteAdmins)) {
            foreach ($siteAdmins as $siteAdmin) {
                $magic = $app->generateUserAccessKey($siteAdmin);
                Mail::sendSiteAdminNewInitiativeMail($siteAdmin, $savedInitiative, $magic, $app->user['username']);
            }
        }
        Mail::sendLocationNewInitiativeMail($location, $savedInitiative, $app->user['username']);
        
        $app->redirect('/sak/' . $savedInitiative['id']);
    }
} else {
    $drafts = Array();
    $fv = new FormValidator(Array(), 'edit-initiative-form');
}
    
?>

<?php $app->renderFragment('widget/edit-initiative-info'); ?>

<?php
if ($drafts) {
    echo '<div id="main">';
    echo '<h1>Du har initiativer som ikke er fullført:</h1>';
    echo '<ul>';
    foreach ($drafts as $draft) {
        echo '<li><a href="/rediger-sak/' . $draft['id'] . '">' . ($draft['title'] ? $draft['title'] : 'Uten navn') . '</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>

<script type="text/javascript">
<!--
<?php echo $fv->getJavascriptValidator(); ?>
jQuery(document).ready(function() {
    minsak.initInitiativeForm(<?php echo array_key_exists('id', $initiative) ? $initiative['id'] : 0; ?>);
});
//-->
</script>

<!-- main-form -->
<form class="main-form<?php if (!$app->user) { ?> hidden<?php } ?>" id="edit-initiative-form" action="/rediger-sak<?php echo array_key_exists('id', $initiative) ? '/' . $initiative['id'] : ''; ?>" method="post">
    <fieldset>
        <!-- column -->
        <div class="column">
            <label for="recipient"><strong><?php echo $app->_('Mottaker av sak'); ?>:</strong><span class="star">*</span><span>Kommune eller fylke</span></label>
            <div id="initiative-location-selector-form-container"></div>
            <?php
                $data = Array('elementId'=>'initiative-location-selector-form-container', 'wrapForm' => false, 'mode' => 1);
                $locationId = $app->stripToNumeric($app->getPostValue('locationId', $app->getMapValue($initiative, 'location_id')));
                if (array_key_exists('locationId', $_POST)) {
                    $data['locationId'] = $locationId;
                }
                $app->renderFragment('widget/location-selector', $data);
            ?>
            <noscript>
                <select name="locationId">
                    <option>Velg mottaker</option>
                    <?php
                        $counties = $app->dao->getLocationsHierarchy();
                        foreach ($counties as $county) {
                            echo '<option value="' . $county['id'] . '"' . ($county['id'] == $locationId ? ' selected="selected"' : '') . '>' . $county['name'] . '</option>';
                            if (array_key_exists('children', $county)) {
                            foreach ($county['children'] as $municipality) {
                                echo '<option value="' . $municipality['id'] . '"' . ($municipality['id'] == $locationId ? ' selected="selected"' : '') . '>&nbsp;&nbsp;&nbsp;&nbsp;' . $municipality['name'] . '</option>';
                                }
                            }
                        }
                    ?>
                </select>
            </noscript>
            <?php echo $fv->getErrorBlock('locationId', $isCommit, $validationErrors, $validationMissing); ?>
            <div style="height:51px;"></div>

            <label for="sender"><strong><?php echo $app->_('Avsender'); ?>:</strong><span class="star">*</span><span><?php echo $app->_('Oppgi navnet ditt og navnet på foreningen du eventuelt representerer.'); ?></span></label>
            <span class="txt txt2"><input tabindex="21" class="default" id="sender" name="sender" type="text" value="<?php echo $app->getEscapedPostValue('sender', $app->getMapValue($initiative, 'sender', '')); ?>" /></span>
            <?php echo $fv->getErrorBlock('sender', $isCommit, $validationErrors, $validationMissing); ?>
            <div style="height:51px;"></div>
            <!-- row -->
            <div class="row">
                <label for="name"><strong><?php echo $app->_('Navn'); ?>:</strong><span class="star">*</span></label>
                <span class="txt"><input tabindex="22" class="default" id="name" name="name" type="text" value="<?php echo $app->getEscapedPostValue('name', $app->getMapValue($initiative, 'name', $app->user['name'])); ?>" /></span>
                <?php echo $fv->getErrorBlock('name', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <!-- row -->
            <div class="row">
                <label for="address"><strong>Adresse:</strong></label>
                <span class="txt"><input tabindex="23" class="default" id="address" name="address" type="text" value="<?php echo $app->getEscapedPostValue('address', $app->getMapValue($initiative, 'address', '')); ?>"/></span>
                <?php echo $fv->getErrorBlock('address', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <!-- row -->
            <div class="row">
                <label for="zipcode"><strong>Postnummer:</strong></label>
                <span class="txt txt9"><input tabindex="24" class="default" id="zipcode" name="zipcode" type="text" value="<?php echo $app->getEscapedPostValue('zipcode', $app->getMapValue($initiative, 'zipcode', '')); ?>" /></span>
                <?php echo $fv->getErrorBlock('zipcode', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <!-- row -->
            <div class="row">
                <strong style="float: left; clear: both; display: block; width: 205px; margin: 4px 0 0 2px;">E-postadresse:</strong>
                <span class="txt-plain"><?php echo $app->user['username']; ?></span>
            </div>
            <!-- row -->
            <div class="row date-row">
                <div style="padding-bottom:5px;">
                    <?php echo $app->_('Forslaget er automatisk åpent for undertegning i 30 dager. Dersom du ønsker at forslaget skal være åpent en kortere eller lengre periode, endrer du datoen her:'); ?>
                </div>
                <label style="margin-top:0px;" for="enddate"><strong>Fyll inn dato her (dd/mm/åå):</strong></label>
                <?php
                    $endDateValue = date('d/m/y', strtotime('+1 month'));
                    if (array_key_exists('enddate', $_POST)) {
                        $endDateValue = htmlspecialchars($_POST['enddate']);
                    } else if (array_key_exists('end_date', $initiative)) {
                        $endDateValue = date('d/m/y', $initiative['end_date']);
                    }
                ?>
                <span class="txt txt10"><input tabindex="26" id="enddate" name="enddate" class="datepicker<?php if ($app->getPostValue('enddate')) { echo ' default'; } ?>" type="text" value="<?php echo $endDateValue; ?>" /></span>
                <a tabindex="27" href="#" class="calendar" style="display:none;">calendar</a>
                <?php echo $fv->getErrorBlock('enddate', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <div id="imageupload-container" class="<?php echo $imageInfo ? 'hide' : 'hidden'; ?>">
                <strong class="title" style="float:none;"><?php echo $app->_('Last opp bilde'); ?></strong>
                <!-- text-tow -->
                <div class="text-tow">
                    <a id="initiative-upload-image-button" tabindex="28" class="button" href="#"><span>Last opp</span></a>
                    <span><?php echo $app->_('eller velg et bilde fra'); ?> <a tabindex="29" class="open-popup" href="#flickr-popup">flickr</a></span>
                </div>
                <div id="initiative-upload-image-status"></div>
            </div>
            <div class="invisible-js">
                <div <?php if ($imageInfo) { echo 'class="hide"'; } ?>>
                    <strong class="title" style="float:none;">Last opp bilde</strong>
                    <input style="float:none; display:block;" type="file" name="image-upload" />
                    <?php echo $fv->getErrorBlock('image-upload', $isCommit, $validationErrors, $validationMissing); ?>
                    Eller: <input style="" type="submit" name="submit" value="Velg bilde fra flickr"/>
                </div>
            </div>
            <div id="image-container" <?php if (!$imageInfo) { echo 'class="hide"'; } ?>>
            <strong class="title title2" style="float:none;"><?php echo $app->_('Bilde'); ?></strong>
            <!-- img-place -->
            <div class="img-place">
                <img src="<?php echo $app->getInitiativeImageURL($imageInfo, AppContext::INITIATIVE_IMAGE_SIZE_SMALL, ''); ?>" width="196" height="132" alt="image description" />
                <div class="text-holder">
                    <div class="button-holder">
                        <a id="remove-image-button" tabindex="30" class="button invisible-nonjs" href="#"><span><?php echo $app->_('Fjern bilde'); ?></span></a>
                        <div class="invisible-js"><input style="float:none;" type="submit" name="submit" value="Fjern bilde"/></div>
                    </div>
                    <p><?php echo $app->_('Forsikre deg om at du har tillatelse til å bruke bildet.'); ?></p>
                </div>
            </div>
            <label for="photographer"><strong><?php echo $app->_('Bildetekst / fotograf'); ?></strong></label>
            <span class="txt"><input tabindex="31" type="text" id="photographer" name="photographer" title="<?php echo $app->_('Bildetekst / fotograf'); ?>" value="<?php echo $app->getEscapedPostValue('photographer', $app->getMapValue($initiative, 'image_credits', '')); ?>" /></span>
            <?php echo $fv->getErrorBlock('photographer', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
        </div>
        <!-- column -->
        <div class="column">
            <label for="title"><strong>Tittel:</strong><span class="star">*</span></label>
            <span class="txt"><input tabindex="32" class="default" id="title" name="title" type="text" value="<?php echo $app->getEscapedPostValue('title', $app->getMapValue($initiative, 'title', '')); ?>" /></span>
            <?php echo $fv->getErrorBlock('title', $isCommit, $validationErrors, $validationMissing); ?>
            <div style="height:51px;"></div>
            
            <label for="description"><strong><?php echo $app->_('Beskriv saken'); ?>:</strong><span class="star">*</span></label><span><?php echo $app->_('Merk: Teksten kan ikke endres i etterkant'); ?></span>
            <!-- description -->
            <div class="describe">
                <div class="holder">
                    <div class="frame">
                        <textarea tabindex="33" class="default" id="description" name="description" cols="30" rows="10"><?php echo $app->getEscapedPostValue('description', $app->getMapValue($initiative, 'text', '')); ?></textarea>
                    </div>
                    <?php echo $fv->getErrorBlock('description', $isCommit, $validationErrors, $validationMissing); ?>
                </div>
            </div>
            <div class="placeholder">
                <?php
                    echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, null, true);
                ?>
                <?php echo $fv->getErrorBlock('recaptcha_challenge_field', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <!-- rules-row -->
            <div class="rules-row">
                <input tabindex="34" type="checkbox" id="rules" name="rules"<?php if ($app->getPostValue('rules', array_key_exists('id', $initiative))) { echo ' checked="checked"'; } ?>  />
                <label for="rules"><?php echo $app->_('Jeg har lest'); ?> <a tabindex="35" class="open-popup" href="#retningslinjer" target="_blank"><?php echo $app->_('retningslinjer for fremming av saker'); ?></a></label>
                <?php echo $fv->getErrorBlock('rules', $isCommit, $validationErrors, $validationMissing); ?>
            </div>
            <div class="button-row invisible-nonjs">
                <a tabindex="36" class="cancel" href="#" onclick="if (confirm('vil du avbryte initiativet?')) { jQuery('#cancel-initiative').click(); } return false;">Avbryt</a>
                <a style="margin-left:30px;" tabindex="37" class="submit" href="#" onclick="jQuery('#submit-initiative').click(); return false;">Opprett</a>
            </div>
            <div class="button-row invisible-nonjs">
            </div>
            <input type="hidden" id="edit-initiative-form-operation" name="operation" value="" />
            <div class="invisible-js">
                <input type="submit" id="cancel-initiative" name="submit" value="Avbryt"/>
                <input type="submit" id="submit-initiative" name="submit" value="Opprett" onclick="return <?php echo $fv->getJavascriptValidatorFunctionName();?>();"/>
            </div>
        </div>
    </fieldset>
</form>

<!-- lightbox -->
<div class="lightbox lightbox2 hidden" id="flickr-popup">
    <div class="holder">
        <div class="content">
            <!-- heading -->
            <div class="heading">
                <strong class="title">Bilder fra flickr</strong>
                <a href="#" class="close">Avbryt</a>
            </div>
            <p>Velg et passende illustrasjonsbilde til saken din! Bildene kan brukes fritt.</p>
            <!-- form -->
            <form id="flickr-search-form" action="#">
                <fieldset>
                    <div class="row">
                        <span class="txt"><input title="Søk etter bilde" type="text" /></span>
                        <input class="submit" type="submit" value="Søk" />
                    </div>
				</fieldset>
			</form>
			<!-- img-list -->
            <ul class="img-list" id="flickr-image-list">
			</ul>
            <!-- paging -->
			<div class="paging" id="flickr-paging">
                <a href="#" class="prev">Forrige</a>
				<a href="#" class="next">Neste</a>
                <ul>
                </ul>
                <span class="note">(viser 1-7 av 270)</span>
            </div>
        </div>
    </div>
    <div class="lightbox-b">&nbsp;</div>
</div>
<?php if (!$app->user) { ?>
<noscript>
<div id="lightbox-overlay" style="opacity: 0.65; background-color: rgb(0, 0, 0); position: absolute; overflow: hidden; top: 0px; left: 0px; z-index: 1000; width: 100%; height: 100%;"></div>
</noscript>
<div class="lightbox static unclosable" id="login-popup" style="z-index: 1100; position: absolute; top: 200px; left: 50%; margin-left: -332px;">
    <div class="holder">
        <div class="content">
            <!-- heading -->
            <div class="heading">
                <strong class="title">Logg inn</strong>
            </div>
            <p><?php echo $app->_('Du må logge inn før du kan registrere en sak'); ?></p>
            <!-- form -->
            <?php $app->renderFragment('widget/login-form', Array('redirect' => '/rediger-sak', 'register' => true, 'cancel' => true)); ?>
            <!-- paging -->
        </div>
    </div>
    <div class="lightbox-b">&nbsp;</div>
</div>
<?php } ?>

