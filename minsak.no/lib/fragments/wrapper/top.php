<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no" xmlns:fb="http://ogp.me/ns/fb#">
<?php global $app; ?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <script src="/static/jquery-1.6.4.min.js" type="text/javascript"></script>
    <script src="/static/jquery.main.js" type="text/javascript"></script>
    <script src="/static/jquery.ui.datepicker-no.js" type="text/javascript"></script>
    <script src="/static/minsak.js" type="text/javascript"></script>
    <script src="/static/ajaxupload.js" type="text/javascript"></script>
    <script type="text/javascript">
        minsak.language = '<?php echo $app->currentLanguage; ?>';
    </script>
    <script type="text/javascript" src="https://apis.google.com/js/plusone.js">
      {lang: 'no'}
    </script>
    <link rel="stylesheet" href="/static/minsak.css" />
    <title><?php echo $app->pageTitle; ?></title>
    <link media="all" rel="stylesheet" type="text/css" href="/static/all.css" />
    <style type="text/css">
        .logo a{
            background:url(/static/images/<?php echo $app->_('logo.png'); ?>);
        }
    </style>
    <link media="all" rel="stylesheet" type="text/css" href="/static/jquery.ui.all.css" />
    <link media="print" rel="stylesheet" type="text/css" href="/static/print.css" />
    <!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="/static/ie.css" media="screen"/><![endif]-->
    <?php echo $app->htmlHeadExtra; ?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40932540-1', 'minsak.no');
  ga('send', 'pageview');

</script>
</head>
<body>
<div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
    	// https fix from: http://stackoverflow.com/questions/5212016/facebook-javascript-sdk-over-https-loading-non-secure-items
      	FB._https = (window.location.protocol == "https:");

      	FB.init({
          appId      : '<?php echo FACEBOOK_APP_ID; ?>', // App ID
          channelURL : '//<?php echo DOMAIN; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          oauth      : true, // enable OAuth 2.0
          xfbml      : true  // parse XFBML
        });
     	// https fix from: http://stackoverflow.com/questions/5212016/facebook-javascript-sdk-over-https-loading-non-secure-items
      	if (FB._https && window == window.parent) {
            if (FB._domain && FB._domain.staticfb && FB._domain.https_staticfb) {
                FB._domain.staticfb = FB._domain.https_staticfb;
            }
        }
        FB.Canvas.setAutoGrow();

        // Additional initialization code here
      };

      // Load the SDK Asynchronously
      (function(d){
         var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/en_US/all.js";
         d.getElementsByTagName('head')[0].appendChild(js);
       }(document));
    </script>
    <span class="top-shadow">&nbsp;</span>
    <!-- wrapper -->
    <div id="wrapper"<?php if (isset($data['wrapper_class'])) echo " class=\"{$data['wrapper_class']}\"";?>>
        <div class="w1">
            <!-- top-block -->
            <div class="top-block">
                <noscript>Denne siden fungerer best med JavaScript.</noscript>
                <a tabindex="1" class="skip" href="#content">Skip to Content</a>
                <form class="search" action="/sok" method="get">
                    <fieldset>
                        <span class="txt"><input accesskey="4" tabindex="6" title="Søk" type="text" name="title" /></span>
                        		<input type="hidden" name="location_id" value="<?php echo $app->currentLocation['id'];?>" />
        						<input tabindex="7" class="submit" type="submit" value="Søk" />
                    </fieldset>
                </form>
                <ul class="top-menu">
                    <li><a tabindex="2" href="/lang?code=nb&amp;redirect=<?php echo urlencode($app->url); ?>">Bokmål</a><a tabindex="3" href="/lang?code=nn&amp;redirect=<?php echo urlencode($app->url); ?>">Nynorsk</a></li>
                    <li><a tabindex="4" class="larger" id="increase" href="#">A+</a><a tabindex="5" class="smaller" id="decrease" href="#">A-</a></li>
				</ul>
				<div style="float:left; font-weight:bold; height:27px;">
				<?php if ($app->user) { ?>
					<span style="display: inline-block; padding-top: 8px; vertical-align:top;">Logget inn som: <?php echo $app->user['name']; ?></span>
					<a href="/logout?redirect=<?php echo urlencode($app->url); ?>" style="display:inline-block; padding-left:10px;"><img src="/static/images/btn-logout.gif" alt="Logg ut"/></a>
					<a href="/mine-saker" class="btn-minesaker" style="display:inline-block; padding-left: 10px;"><img src="/static/images/btn-minesaker.gif" alt="Mine saker"/></a>
				<?php } else { ?>
					<a href="/login?redirect=%2Fmine-saker" style="display:inline-block;"><img src="/static/images/btn-login.gif" alt="Logg inn"/></a>
				<?php } ?>
				</div>
			</div>
            <!-- header -->
            <div id="header">
                <div class="holder">
                    <!-- logo -->
                    <strong class="logo"><a tabindex="8" title="Gå til forsiden" href="/">Minsak.no</a></strong>
					<img class="logo-print" src="/static/images/<?php echo $app->_('logo-print.gif'); ?>" width="253" height="81" alt="Minsak.no" />
                    <strong class="text-be-heard-<?php echo $app->currentLanguage; ?>">BLI HØRT PÅ 1-2-3</strong>
					<!-- steps -->
                    <ul class="steps">
                        <li><span accesskey="q" tabindex="9" class="step1" href="/rediger-sak"><span class="inner">1</span><?php echo $app->_('Beskriv din sak'); ?></span></li>
                        <li><span accesskey="w" tabindex="10" class="step2" href="#"><span class="inner">2</span>Samle underskrifter</span></li>
                        <li><span accesskey="e" tabindex="11" class="step3" href="#"><span class="inner">3</span><?php echo $app->_('Få saken vurdert<br />i kommunestyret/fylkestinget'); ?></span></li>
                    </ul>
                </div>
            </div>
            <!-- breadcrumbs -->
           <?php $app->renderFragment('widget/breadcrumbs'); ?>
           <?php $app->renderFragment('widget/retningslinjer'); ?>



<?php
// if (!$app->user && $app->url != '/login' && $app->url != '/forgot-password') {
//     $app->renderFragment('widget/login-form');
// } else if ($app->user) {
//     echo ' <a href="/logout?redirect=' . urlencode($app->url) . '">Logg ut</a> ';
//     echo ' <a href="/profil">Profil</a> ';
//     if ($app->userIsSiteAdmin()) {
//         echo ' <a href="/user-admin">Administrer brukere</a> ';
//     }
// }
?>