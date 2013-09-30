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
$editUser = $app->getMapValue($data, 'editUser', Array());
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
    )
);
$fv = new FormValidator($postParams, 'user-admin-profile-form');

$isCommit = false;
$validationErrors = null;
$validationMissing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isCommit = true;
    $validationResult = $fv->validatePostParameters();
    $validationErrors = $validationResult['errors'];
    $validationMissing = $validationResult['missing'];
}

if ($isCommit && count($validationErrors) + count($validationMissing) == 0) {
    $createNew = !$editUser;
    if ($createNew) {
        $newPassword = $app->createPassword();
        $user['password'] = $app->hashPassword($newPassword);
    }
    $editUser['username'] = $_POST['username'];
    $editUser['name'] = $_POST['name'];
    $editUser['isSiteAdmin'] = $app->getPostValue('isSiteAdmin', 0);
    $editUser['isModerator'] = $app->getPostValue('isModerator', 0);
    $savedUser = $app->dao->saveUser($editUser);
    var_dump($savedUser);
    if ($savedUser) {
        if ($createNew) {
            Mail::sendNewUserEmail($editUser, $newPassword);
        }
        $app->redirect('/user-admin');
    } else {
        echo "Oppdatering av bruker feilet";
    }
}

?>

<script type="text/javascript">
<?php echo $fv->getJavascriptValidator(); ?>
</script>

<div id="main">
<h1><?php echo ($editUser ? 'Rediger bruker' : 'Ny bruker'); ?></h1>

<form id="user-admin-profile-form" action="/user-admin/profile/<?php echo $editUser['id']; ?>" method="post">
  <label for="name">Navn</label>
  <input type="text" name="name" id="name" value="<?php echo $app->getEscapedPostValue('name', $app->getMapValue($editUser, 'name', '')); ?>" />
  <?php echo $fv->getErrorBlock('name', $isCommit, $validationErrors, $validationMissing); ?>
  <label for="username">Brukernavn/E-post</label>
  <input type="text" name="username" id="username" value="<?php echo $app->getEscapedPostValue('username', $app->getMapValue($editUser, 'username', '')); ?>" />
  <?php echo $fv->getErrorBlock('username', $isCommit, $validationErrors, $validationMissing); ?>
  <label for="isSiteAdmin">Nettstedsadministrator</label>
  <input type="checkbox" name="isSiteAdmin" value="1" id="isSiteAdmin"<?php if ($app->getEscapedPostValue('isSiteAdmin', $app->getMapValue($editUser, 'isSiteAdmin'))) { echo ' checked="checked"'; } ?> />
  <label for="isModerator">Moderator</label>
  <input type="checkbox" name="isModerator" value="1" id="isModerator"<?php if ($app->getEscapedPostValue('isModerator', $app->getMapValue($editUser, 'isModerator'))) { echo ' checked="checked"'; } ?> />
  <input type="submit" name="submit" value="Submit" onclick="return <?php echo $fv->getJavascriptValidatorFunctionName();?>();"/>
</form>
</div>