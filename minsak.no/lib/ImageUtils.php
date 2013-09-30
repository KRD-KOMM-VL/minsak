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
 * Class containing utility functions for images
 */
class ImageUtils {
    
    /**
     * Convert an uploaded image to a list of variants
     * $variants is an Array with the following format:
     * Array(
     *     'small' => Array('filename' => filename, 'width' => 50, 'height' => 50),
     *     'medium' => Array('filename' => filename, 'width' => 100, 'height' => 100),
     *     'large' => Array('filename' => filename, 'width' => 200, 'height' => 200),
     * );
     * For each of the variant, an image will be created with the given filename, width and height.
     * The images will be scaled to fill the specified rectangle
     *
     * @param String $originalFile the full path to the original image file
     * @param Array $variants list of variants
     */
    public static function convertImageToVariants($originalFile, $variants) {

        $imageInfo = getimagesize($originalFile);
        if ($imageInfo === FALSE) {
        	$this->log('convertImageToVariants: can\'t get image info for ' . $originalFile);
            $this->fail(500, 'Could not load image info'); // Should not happen, checked by FormValidator
        }
        
        switch ($imageInfo[2]) {
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($originalFile);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($originalFile);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($originalFile);
                break;
            default:
                $this->fail(500, 'Unsupported input image type'); // Should not happen, checked by FormValidator 
        }
        if (!$image) {
            $this->fail(500, 'Could not load image file');
        }
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        if ($imageWidth < 1 || $imageHeight < 1) {
            $this->fail(500, 'Image is too small');
        }
        $aspect = $imageWidth / $imageHeight;
        
        if ($aspect < 0.01 || $aspect > 100) {
            $this->fail(500, 'Image aspect ratio is too extreme');
        }
        
        foreach ($variants as $variant) {
            if (array_key_exists('width', $variant) && array_key_exists('height', $variant) && array_key_exists('filename', $variant)) {

                $newAspect = $variant['width'] / $variant['height'];
                if ($aspect > $newAspect) {
                    // image is wider than target, so crop vertically, maintain height
                    $srcHeight = $imageHeight;
                    $srcY = 0;
                    $srcWidth = $newAspect * $srcHeight;
                    $srcX = ($imageWidth - $srcWidth)/2;
                }
                else {
                    // image is taller than target, so crop horizontally, maintain width
                    $srcWidth = $imageWidth;
                    $srcX = 0;
                    $srcHeight = $srcWidth / $newAspect;
                    $srcY = ($imageHeight - $srcHeight)/2;
                }
                
                // printf("Copying from (%d,%d) [%d x %d] -> (%d,%d) [%d x %d]", $srcX, $srcY, $srcWidth, $srcHeight, 0, 0, $variantsize[0], $variantsize[1]));
                
                $newImage = imagecreatetruecolor($variant['width'], $variant['height']);
                if (!$newImage) {
                    $this->fail(500, 'Failed to allocate image copy for post-processing');
                }
                if (!imagecopyresampled($newImage, $image, 0, 0, $srcX, $srcY, $variant['width'], $variant['height'], $srcWidth, $srcHeight)) {
                    $this->fail(500, 'Failed to resample image');
                }
                
                $outname = IMAGE_UPLOAD_DIR . $variant['filename'];
                if (!imagejpeg($newImage, $outname, 90)) {
                    $this->fail(500, 'Failed to write resampled image');
                }
                
            }
        }
        return true;
    }
}