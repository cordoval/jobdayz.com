<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * @category   Isw
 * @package    Zend_Helper
 * @copyright  Copyright (c) 2011-2021 Isw
 */

class Isw_Image_Resize
{
    const TYPE_JPG = 'jpg';
    const TYPE_GIF = 'gif';
    const TYPE_PNG = 'png';
    
    public function filenameToMime ($filename)
    {
        $types = array(
	        'jpg' => self::TYPE_JPG,
	        'gif' => self::TYPE_GIF,
	        'png' => self::TYPE_PNG
        );
        
        foreach ($types as $extension => $mime){
	        if (preg_match('/.'.$extension.'$/', $filename)) {
		        return $mime;
	        }
        }
        throw new Zend_Exception('Unknown image type.');
    }
    
    /**
     * Returns a PHP image resource
     */
    public function readImage ($filename, $type = NULL)
    {
        if ( is_null($type) ) {
	        $type = $this->filenameToMime($filename);
        }
        switch ($type) {
	        case self::TYPE_JPG:
		        return imagecreatefromjpeg($filename);
		        break;
	        case self::TYPE_GIF:
		        return imagecreatefromgif($filename);
		        break;
	        case self::TYPE_PNG:
		        return imagecreatefrompng($filename);
		        break;
	        default:
		        break;
        }
    }
    /**
     * Doesn't return anything
     */
    public function writeImage ($image, $filename = NULL, $type = NULL)
    {
        if ( is_null($type) ) {
	        $type = $this->filenameToMime($filename);
        }
        switch ($type) {
	        case self::TYPE_JPG:
		        return imagejpeg($image, $filename);
		        break;
	        case self::TYPE_GIF:
		        return imagegif($image, $filename);
		        break;
	        case self::TYPE_PNG:
		        return imagepng($image, $filename);
		        break;
	        default:
		        break;
        }
    }

    public function resize ($image, $width = NULL, $height = NULL, $fit = true)
    {
       $fit = (bool) $fit;
        // if no bounding box supplied, then return the original image
        if ($width === NULL && $height === NULL)
        {
	        return $image;
        }
        $origX = $newX = imagesx($image);
        $origY = $newY = imagesy($image);
        $origR = $origY / $origX;
        // if height only was specified
        if ($width === NULL && $height !== NULL)
        {
	        if ($origY >= $height || $fit == true) 
	        {
		        $newY = $height;
		        $newX = $newY / $origY * $origX;
	        }
        }
        // if width only was specified
        if ($width !== NULL && $height === NULL)
        {
	        if ($origX >= $width || $fit == true)
	        {
		        $newX = $width;
		        $newY = $newX / $origX * $origY;
	        }
        }
        // if width and height specified
        if ($width !== NULL && $height !== NULL)
        {
	        // if the image fits and $fit is off, just return the original
	        // image (since it fits in the bounding box)
	        if ($origX < $width && $origY < $height && $fit == false)
	        {
		        return $image;
	        }
	        $newR = $height / $width;
	        // we now need to work out whether to restrain by width or by height
	        if ($origR > $newR)
	        {
		        $newY = $height;
		        $newX = $newY / $origY * $origX;
	        }
	        else
	        {
		        $newX = $width;
		        $newY = $newX / $origX * $origY;
	        }		
        }
        // now we know the new image's dimensions
        $new = imagecreatetruecolor($newX, $newY);
        imagecopyresampled($new, $image, 0, 0, 0, 0, $newX, $newY, $origX, $origY);
        return $new;
    }
}
