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
if (!$app->user) {
    $app->fail(404, 'Siden finnes ikke');
}
if (count($app->urlParts) < 2) {
    $app->fail(404, 'Siden finnes ikke');
}
$initiativeId = intval($app->urlParts[1]);
$initiative = $app->dao->getInitiativeById($initiativeId);
if (!$initiative || $initiative['user_id'] != $app->user['id'] || $initiative['status'] != 'draft') {
    $app->fail(404, 'Siden finnes ikke');
}
if (count($app->urlParts) > 2) {
    $flickrImageId = $app->urlParts[2];
    $flickrImageCredits = $app->getMapValue($_REQUEST, 'credits', '');

    $flickrUrl = 'https://www.flickr.com/services/rest/?method=flickr.photos.getSizes&format=php_serial&api_key=' . FLICKR_APP_KEY . '&photo_id=' . urlencode($flickrImageId);
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
    $error = false;
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
                        $initiative['image_file'] = $fileNameBase;
                        $initiative['image_type'] = AppContext::INITIATIVE_IMAGE_TYPE_FLICKR;
                        $initiative['image_flickr_photo_id'] = $flickrImageId;
                        $initiative['image_credits'] = $flickrImageCredits;
                        $app->dao->updateInitiative($initiative);
                    } else {
                        $app->fail(500, 'Server error');
                    }
                } else {
                    // handle error .. couldn't download image
                    $app->fail(500, 'Server error');
                }
            } else {
                // handle error .. can't find suitable size
                $app->fail(500, 'Server error');
            }
        } else {
            // handle error .. data format not as expected
            $app->fail(500, 'Server error');
        }
    } else {
        // handle error .. couldn't get data from flickr
        $app->fail(500, 'Server error');
    }
    
    $app->redirect('/rediger-sak/' . $initiativeId);
}

$searchTerm = $app->getRequestValue('search', '');
$flickrUrl = 'https://www.flickr.com/services/rest/?method=flickr.photos.search&format=php_serial&license=1,2,3,4,5,6&extras=license,url_t&per_page=150&nojsoncallback=1&api_key=' . FLICKR_APP_KEY . '&text=' . urlencode($searchTerm);
$failed = false;
try {
    $ch = curl_init($flickrUrl);
    ob_start();
    curl_exec($ch);
    curl_close($ch);
} catch (Exception $e) {
    $failed = true;
}
$serial = ob_get_clean();
$data = false;
if (!$failed && is_string($serial)) {
    $data = unserialize($serial);
}

?>
<div id="main">
<h1>Velg bilde fra flickr</h1>
<form action="<?php echo $app->url; ?>">
<input type="text" name="search" value="<?php echo $app->getRequestValue('search'); ?>" />
<input type="submit" name="submit" value="Søk" />
</form>
<?php
if ($data && array_key_exists('photos', $data) && array_key_exists('photo', $data['photos'])) {
    echo '<ul>';
    foreach ($data['photos']['photo'] as $photo) {
        $id = $photo['id'];
        $credits = $photo['title'];
        $src = str_replace('http:', 'https:', $photo['url_t']);
        $width = $photo['width_t'];
        $height = $photo['height_t'];
        echo '<li style="display:inline;">';
        echo '<span style="display:inline-block;width:100px;height:100px;padding:5px;">';
        echo '<a href="' . $app->url . '/' . $id . '?credits=' . urlencode($credits) . '">';
        echo '<img src="' . $src . '" alt="' . htmlspecialchars($credits) . '"/>';
        echo '</a>';
        echo '</span>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo 'Kunne ikke hente bilder fra flickr';
}
?>

</div>
