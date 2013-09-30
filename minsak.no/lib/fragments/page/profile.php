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
$editUser = $app->user;

function validatePassword($password) {
	global $app;
	return $app->checkPasswordVsHash($password, $app->user['password']);
}

$postParams = Array(
    'username' => Array(
        'validators' => Array(
            Array('type' => 'email', 'params' => Array(), 'message' => 'Ugyldig e-post-adresse'),
        )
    ),
    'name' => Array(
        'validators' => Array(
            Array('type' => 'string', 'params' => Array('pattern' => '/\S\.?\s\S/'), 'message' => 'Ugyldig navn'),
        )
    ),
	'oldPassword' => Array(
		'validators' => Array(
			Array('type' => 'checkpassword', 'params' => Array('password' => 'password', 'verify' => 'passwordVerify', 'pwdValidationFunc' => 'validatePassword'), 'message' => 'Det gamle passordet ditt er feil')
		),
		'isCheckbox' => true // missing field is acceptable
	),
	'password' => Array(
		'validators' => Array(
			Array('type' => 'passwordchange', 'params' => Array('old' => 'oldPassword', 'verify' => 'passwordVerify'), 'message' => 'Ditt nye passord må være minst 6 tegn langt')
		),
		'isCheckbox' => true // missing field is acceptable
	),
	'passwordVerify' => Array(
		'validators' => Array(
			Array('type' => 'passwordverify', 'params' => Array('old' => 'oldPassword', 'password' => 'password'), 'message' => 'Du må taste inn ditt nye passord to ganger helt likt')
		),
		'isCheckbox' => true // missing field is acceptable
	)
);
$fv = new FormValidator($postParams, 'profile-form');

$message = false;
$isCommit = false;
$validationErrors = null;
$validationMissing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    $validationResult = $fv->validatePostParameters();
    $validationErrors = $validationResult['errors'];
    $validationMissing = $validationResult['missing'];
}

$formOldPasswordValue = $app->getEscapedPostValue('oldPassword');
$formPasswordValue = $app->getEscapedPostValue('password');
$formPasswordVerifyValue = $app->getEscapedPostValue('passwordVerify');

if ($isCommit && count($validationErrors) + count($validationMissing) == 0) {
    $createNew = !$app->user;
    if ($createNew) {
        $newPassword = $app->createPassword();
        $editUser['password'] = $app->hashPassword($newPassword);
    } else {
    	if (array_key_exists('password', $_POST) && $_POST['password'] != '') {
    		$newPasswordHash = $app->hashPassword($_POST['password']);
    		$editUser['password'] = $newPasswordHash;
    	}
    }
    $editUser['username'] = $_POST['username'];
    $editUser['name'] = $_POST['name'];
    $savedUser = $app->dao->saveUser($editUser);
    if ($savedUser) {
        if ($createNew) {
            Mail::sendNewUserEmail($savedUser, $newPassword);
            $app->redirect('/registered-user');
        } else {
            $message = "Brukeren er oppdatert";
        }
   		// unset the form values when user is updated
   		$formOldPasswordValue = '';
   		$formPasswordValue = '';
   		$formPasswordVerifyValue = '';
   		$app->updateUser($savedUser);
    } else {
        $message = 'Brukeren eksisterer allerede. Har du glemt passordet? Få et nytt <a href="/forgot-password">her</a>';
    }
}

?>

<div id="main">
<h1><?php echo $editUser ? 'Rediger profil' : 'Registrer ny bruker'; ?></h1>
<?php
if ($message) {
    echo '<p>' . $message . '</p>';
}
?>
<script type="text/javascript">
<?php echo $fv->getJavascriptValidator(); ?>
</script>

<form action="/profil" method="post" id="profile-form">
  <table>
  <tr>
  <td>
  <label for="name">Navn:</label>
  </td>
  <td>
  <input type="text" class="default" name="name" id="name" value="<?php echo $app->getEscapedPostValue('name', $app->getMapValue($editUser, 'name', '')); ?>" />
  <?php echo $fv->getErrorBlock('name', $isCommit, $validationErrors, $validationMissing); ?>
  </td>
  </tr>
  <tr>
  <td>
  <label for="username">Brukernavn/E-post:</label>
  </td>
  <td>
  <input type="text" class="default" name="username" id="username" value="<?php echo $app->getEscapedPostValue('username', $app->getMapValue($editUser, 'username', '')); ?>" />
  <?php echo $fv->getErrorBlock('username', $isCommit, $validationErrors, $validationMissing); ?>
  </td>
  </tr>

<?php if ($app->user) { ?>
  <tr>
  <td colspan="2" style="padding-top:10px;">Hvis du ønsker å endre passordet ditt, må du fylle inn de tre feltene nedenfor.</td>
  </tr>
  <tr>
  <td>
  <label for="oldPassword">Nåværende passord:</label>
  </td>
  <td>
  <input type="password" class="default" name="oldPassword" id="oldPassword" value="<?php echo $formOldPasswordValue; ?>" />
  <?php echo $fv->getErrorBlock('oldPassword', $isCommit, $validationErrors, $validationMissing); ?>
  </td>
  </tr>
  <tr>
  <td>
  <label for="password">Nytt passord:</label>
  </td>
  <td>
  <input type="password" class="default" name="password" id="password" value="<?php echo $formPasswordValue; ?>" />
  <?php echo $fv->getErrorBlock('password', $isCommit, $validationErrors, $validationMissing); ?>
  </td>
  </tr>
  <tr>
  <td>
  <label for="passordVerify">Gjenta nytt passord</label>
  </td>
  <td>
  <input type="password" class="default" name="passwordVerify" id="passwordVerify" value="<?php echo $formPasswordVerifyValue; ?>" />
  <?php echo $fv->getErrorBlock('passwordVerify', $isCommit, $validationErrors, $validationMissing); ?>
  </td>
  </tr>
<?php } ?>
  
  </table>
  <input type="submit" name="submit" value="<?php echo $editUser ? 'Lagre' : 'Registrer'; ?>" onclick="return true; return <?php echo $fv->getJavascriptValidatorFunctionName(); ?>();"/>
</form>
</div>
