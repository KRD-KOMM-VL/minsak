<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php

class Mail {

    /**
     * Send email with new password to user
     * @param Array $user the user as an associative array
     * @param string $password the new password
     */
    public static function sendForgotPasswordEmail($user, $password) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'Minsak.no: ditt nye passord.';
        $siteUrl = BASE_URL . 'login';
        $message = <<< EOF
<html>
  <body>
    <p>Her er ditt nye passord på <a href="$siteUrl">Minsak.no</a>: $password</p>
    <p><a href="$siteUrl">Logg inn på tjenesten her</a></p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send email to new user
     * @param Array $user the user as an associative array
     * @param string $password the password
     */
    public static function sendNewUserEmail($user, $password) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'En bruker har blitt opprettet for deg på Minsak.no';
        $siteUrl = BASE_URL; 
        $loginUrl = BASE_URL . 'login';
        $message = <<< EOF
<html>
  <body>
    <p>Hei!</p>
    <p>Det har nå blitt opprettet en bruker for deg på <a href="$siteUrl">minsak.no</a>.</p>
	<p>Logg inn: <a href="$loginUrl">$loginUrl</a><br/>
	Brukernavn: $email<br/>
    Passord: $password</p>
    <p>Ved spørsmål, send en e-post til <a href="mailto:support@minsak.no">support@minsak.no</a>.</p>
	<p>Lykke til!</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Notify site admins that initiatives are awaiting moderation
     * @param unknown_type $user the admin user
     * @param unknown_type $magicKey the magic key to use for auto-login
     */
    public static function sendSiteAdminModerationMail($user, $magicKey) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'Saker venter moderering på minsak.no';
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer&key=' . $magicKey;
        $message = <<< EOF
<html>
  <body>
    <p>Minsak.no har saker som venter på moderering.</p>
    <p><a href="$moderationUrl">Denne lenken gir deg passordfri tilgang i 24 timer</a>. Deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang.</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send email to site admin with information about new initiative
     * @param Array $user the site admin recipient as an associative array
     * @param Array $initiative the initiative as an associative array
     * @param string $magicKey the magic key for auto-login
     * @param string $userEmail the email address of the initiative owner
     */
    public static function sendSiteAdminNewInitiativeMail($user, $initiative, $magicKey, $userEmail) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'En ny sak er opprettet på minsak.no';
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer/sak/' . $initiative['id'] . '&key=' . $magicKey;
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeData = Mail::createInitiativeData($initiative, $userEmail);
        $message = <<< EOF
<html>
  <body>
    <h2>En ny sak er opprettet på minsak.no</h2>
    <p><a href="$initiativeUrl">Se saken</a></p>
    <p><a href="$moderationUrl">Moderer saken</a> (Passordfri tilgang i 24 timer, deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang)</p>
    <h2>Informasjon:</h2>
    $initiativeData
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send email to location with information about new initiative
     * @param Array $location the location as an associative array
     * @param Array $initiative the initiative as an associative array
     * @param string $userEmail the email address of the initiative owner
     */
    public static function sendLocationNewInitiativeMail($location, $initiative, $userEmail) {
        $email = $location['email_address'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'En ny sak er opprettet på minsak.no';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeData = Mail::createInitiativeData($initiative, $userEmail);
        $message = <<< EOF
<html>
  <body>
    <h2>En ny sak er opprettet på minsak.no</h2>
    <p><a href="$initiativeUrl">Se saken</a></p>
    <h2>Informasjon:</h2>
    $initiativeData
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Notify site admins that an initiative has changed status
     * @param Array $user the admin user as an associative array
     * @param Array $initiative the initiative as an associative array
     * @param string $oldStatus the previous status
     * @param string $newStatus the new status
     * @param string $magicKey the magic key used for auto-login
     */
    public static function sendSiteAdminInitiativeChangedStatus($user, $initiative, $oldStatus, $newStatus, $magicKey) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'En sak har oppdatert status på minsak.no';
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer/sak/' . $initiative['id'] . '&key=' . $magicKey;
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $message = <<< EOF
<html>
  <body>
    <p>En sak på minsak.no har oppdatert status fra "$oldStatus" til "$newStatus"</p>
    <p><a href="$initiativeUrl">Se saken</a></p>
    <p><a href="$moderationUrl">Moderer saken</a> (Passordfri tilgang i 24 timer, deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang)</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Notify site admins that an initiative has been commented
     * @param Array $user the admin user as an associative array
     * @param Array $initiative the initiative as an associative array
     * @param Array $comment the comment as an associative array
     * @param string $name the name of the commenter
     * @param string $text the text of the comment
     * @param string $magicKey the magic key used for auto-login
     */
    public static function sendSiteAdminInitiativeCommented($user, $initiative, $comment, $name, $text, $magicKey) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'En sak har blitt kommentert på minsak.no';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer/kommentar/' . $comment['id'] . '&key=' . $magicKey;
        $name = htmlspecialchars($name);
        $text = htmlspecialchars($text);
        $message = <<< EOF
<html>
  <body>
    <p>En sak på minsak.no har blitt kommentert</p>
    <p><a href="$initiativeUrl">Se saken</a></p>
    <p><a href="$moderationUrl">Moderer kommentaren</a> (Passordfri tilgang i 24 timer, deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang)</p>
    <dl>
    	<dt>Navn:</dt>
    	<dd>$name</dd>
    	<dt>Tekst:</dt>
    	<dd>$text</dd>
    </dl>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send a mail to a moderator who has moderation tasks pending
     * @param Array $user the user as an associative array
     * @param String $magicKey the magic login key for the user
     */
    public static function sendModeratorMail($user, $magicKey) {
        $email = $user['username'];
        $headers = Mail::createDefaultHeaders();
        $subject = 'Saker venter moderering på minsak.no';
        $siteUrl = BASE_URL;
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer&key=' . $magicKey;
        $message = <<< EOF
<html>
  <body>
    <p>Minsak.no har saker som venter på moderering.</p>
    <p><a href="$moderationUrl">Denne lenken gir deg passordfri tilgang i 24 timer</a>. Deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang.</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }

    /**
    * Send a mail to a initiative owner who has signature moderation tasks pending
    * @param Array $initiative the initiative as an associative array
    * @param String $email the initiative owners email
    * @param String $magicKey the magic login key for the user
    */
    public static function sendInitiativeScreeningMail($initiative, $email, $magicKey) {
        $headers = Mail::createDefaultHeaders();
        $subject = 'Signaturer venter på moderering på minsak.no';
        $siteUrl = BASE_URL;
        $moderationUrl = BASE_URL . 'sesam?redirect=/moderer-signaturer/' . $initiative['id'] . '&key=' . $magicKey;
        $message = <<< EOF
<html>
  <body>
    <p>Din sak på Minsak.no har mottatt nok signaturer til å bli godkjent, men du må først gå igjennom dem og luke ut de som ikke holder mål.</p>
    <p><a href="$moderationUrl">Denne lenken gir deg passordfri tilgang i 24 timer</a>. Deretter kan du fremdeles bruke lenken, men må oppgi passordet for å få tilgang.</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send a "share this via email" mail
     * @param string $initiativeUrl the initiative url
     * @param string $email the recipient
     * @param string $from the sender
     * @param string $msg the custom message
     */
    public static function sendInitiativeEmail($initiativeUrl, $email, $from, $msg) {
        $headers = Mail::createDefaultHeaders();
        $subject = $from . ' har delt en sak med deg';
        $from = htmlspecialchars($from);
        $msg = htmlspecialchars($msg);
        $message = <<<EOF
<html>
  <body>
    <p>$from har delt en sak med deg og oppfordrer deg om å skrive under på forslaget som skal sendes til kommunen.</p>
    <p><a href="$initiativeUrl">Se saken her</a></p>
    <p>melding fra $from</p>
    <p>$msg</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Submit the initiative
     * @param Array $initiative the initiative as an associative array
     * @param Array $signatures the signatures as an array of associative arrays
     * @param String $email the municipality/county recipient
     * @param String $userEmail the initiative owners email address
     */
    public static function sendInitiativeCompletedMail($initiative, $signatures, $email, $userEmail) {
        $headers = Mail::createDefaultHeaders();
        $subject = 'En ny sak er klar fra minsak.no';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeTitle = htmlspecialchars($initiative['title']);
        $initiativeText = htmlspecialchars($initiative['text']);
        $initiativeData = Mail::createInitiativeData($initiative, $userEmail);
        $signaturesTable = Mail::createSignaturesTable($signatures);
        $message = <<<EOF
<html>
  <body>
    <p>En sak har blitt sendt inn via minsak.no</p>
    <h2>Informasjon:</h2>
    $initiativeData
    <h2>Sakens innhold:</h2>
    <p style="font-weight:bold">$initiativeTitle</p>
    <p>$initiativeText</p>
    <h2>Signaturer:</h2>
    $signaturesTable
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Inform the initiative owner that the initiative has been submitted
     * @param Array $initiative the initiative as an associative array
     * @param Array $signatures the signatures as an array of associative arrays
     * @param String $email the initiative owners email
     * @param String $initiativeReceiver the email county/municipality address the initiative is sent to
     */
    public static function sendInitiativeCompletedReceiptMail($initiative, $signatures, $email, $initiativeReceiver) {
        $headers = Mail::createDefaultHeaders();
        $subject = 'Du har sendt inn en sak via minsak.no';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeTitle = htmlspecialchars($initiative['title']);
        $initiativeText = htmlspecialchars($initiative['text']);
        $initiativeReceiver = htmlspecialchars($initiativeReceiver);
        $initiativeData = Mail::createInitiativeData($initiative, $email);
        $signaturesTable = Mail::createSignaturesTable($signatures);
        $message = <<<EOF
<html>
  <body>
    <p>Din sak registrert på minsak.no har blitt sendt inn til kommunen/fylket på addressen $initiativeReceiver</p>
    <p>Dette er en kopi av mailen som ble sendt inn</p>
    <h2>Informasjon:</h2>
    $initiativeData
    <h2>Sakens innhold:</h2>
    <p style="font-weight:bold">$initiativeTitle</p>
    <p>$initiativeText</p>
    <h2>Signaturer:</h2>
    $signaturesTable
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    private static function createSignaturesTable($signatures) {
        $table = '<table>';
        $table .= '<tr><th style="text-align:left">Navn</th><th style="text-align:left">Adresse 1</th><th style="text-align:left">Adresse 2</th><th style="text-align:left">Postnummer</th><tr>';
        foreach ($signatures as $signature) {
            $table .= '<tr>';
            $table .= '<td>' . htmlspecialchars($signature['name']) . '</td>';
            $table .= '<td>' . htmlspecialchars($signature['address1']) . '</td>';
            $table .= '<td>' . htmlspecialchars($signature['address2']) . '</td>';
            $table .= '<td>' . htmlspecialchars($signature['area_code']) . '</td>';
        }
        $table .= '</table>';
        return $table;
    }
    
    /**
     * Create html table with initiative data
     * @param Array $initiative the initiative as an associative array
     * @param string $userEmail the initiative owner's email address
     */
    private static function createInitiativeData($initiative, $userEmail) {
        global $app;
        $locInfo = $app->dao->getLocationExtendedInfoById($initiative['location_id']);
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $result = '<table>';
        $result .= '<tr><th style="text-align:left">Mottaker:</th><td>' . htmlspecialchars($locInfo['extra']['name']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Opprettet:</th><td>' . htmlspecialchars(date('d.m.Y', $initiative['created_time'])) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Bruker ID:</th><td>' . $initiative['user_id'] . '</td></tr>';
        $result .= '<tr><th style="text-align:left">E-post:</th><td>' . $userEmail . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Navn:</th><td>' . htmlspecialchars($initiative['name']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Avsender:</th><td>' . htmlspecialchars($initiative['sender']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Adresse:</th><td>' . htmlspecialchars($initiative['address']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Postnummer:</th><td>' . htmlspecialchars($initiative['zipcode']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Tittel:</th><td>' . htmlspecialchars($initiative['title']) . '</td></tr>';
        $result .= '<tr><th style="text-align:left">Lenke til saken:</th><td><a href="' . $initiativeUrl . '">' . $initiativeUrl . '</a></td></tr>';
        $result .= '</table>';
        return $result;
    }
    
    /**
     * Inform the initiative owner that his initiative has been opened/accepted by the moderator
     * @param Array $initiative the initiative as an associative array
     * @param String $email the initiative owners email
     */
    public static function sendInitiativeOpenedEmail($initiative, $email) {
        $headers = Mail::createDefaultHeaders();
        $subject = 'Din sak på minsak.no er åpnet';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeTitle = htmlspecialchars($initiative['title']);
        $message = <<<EOF
<html>
  <body>
    <p>Din sak er nå åpnet og klart for signaturinnsamling</p>
    <p><a href="$initiativeUrl">$initiativeTitle</a></p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Inform the initiative owner that his initiative has been rejected by the moderator
     * @param Array $initiative the initiative as an associative array
     * @param String $email the initiative owners email
     */
    public static function sendInitiativeRejectedEmail($initiative, $email) {
        $headers = Mail::createDefaultHeaders();
        $subject = 'Din sak på minsak.no har blitt avvist';
        $initiativeUrl = BASE_URL . 'sak/' . $initiative['id'];
        $initiativeTitle = htmlspecialchars($initiative['title']);
        $message = <<<EOF
<html>
  <body>
    <p>Din sak med tittel $initiativeTitle har blitt avvist</p>
  </body>
</html>
EOF;
        Mail::send($email, $subject, $message, $headers);
    }
    
    /**
     * Send an email
     * @param String $email the recipient email address
     * @param String $subject the email subject
     * @param String $message the email message body
     * @param String $headers the email headers (@see createDefaultHeaders) 
     */
    private static function send($email, $subject, $message, $headers) {
        global $app;
        if (defined(ML_TO_ADDRESS)) {
            $app->log('Sending copy of mail to ' . ML_TO_ADDRESS);
            mail(ML_TO_ADDRESS, Mail::createUtf8Subject('This is a copy of a mail sent to ' . $email), 'Subject: ' . $subject . ":\n\n" . $message, $headers);
        }
        $app->log('Sending mail to ' . $email);
        mail($email, Mail::createUtf8Subject($subject), $message, $headers);
    }
    
    /**
     * Build utf-8 subject string
     * @param String $subject the subject
     * @return String the utf-8-ified subject
     */
    private static function createUtf8Subject($subject) {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
    
    /**
     * Create default mail headers for html mail and utf-8 encoding
     * @return String the headers
     */
    private static function createDefaultHeaders() {
        return 'MIME-Version: 1.0' . "\r\n" .
        	   'Content-type: text/html; charset=UTF-8' . "\r\n" .
        	   'From: ' . MAIL_FROM_ADDRESS . "\r\n";
    }
    
}

?>
