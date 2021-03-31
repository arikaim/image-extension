<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Classes;

use Arikaim\Core\Utils\Path;
use Arikaim\Core\Utils\File;

/**
 * Image library class
 */
class ImageLibrary
{
    const IMAGES_PATH = 'images' . DIRECTORY_SEPARATOR;
    const VIEW_PROTECTED_IMAGE_URL = '/api/image/view/';
    
    const THUMBNAILS_PATH = 'public' . DIRECTORY_SEPARATOR . Self::IMAGES_PATH . 'thumbnails' . DIRECTORY_SEPARATOR;
    const THUMBNAILS_FILE_NAME_PREFIX = 'thumbnail-';

    /**
     * Get images storage path
     *   
     * @param boolean $relative
     * @param bool $private
     * @return string
     */
    public static function getImagesPath(bool $relative = true, bool $private = false): string
    {
        if ($relative == false) {
            return Path::STORAGE_PATH . Self::IMAGES_PATH;
        }

        return ($private == false) ? 'public' . DIRECTORY_SEPARATOR . Self::IMAGES_PATH : Self::IMAGES_PATH;    
    }

    /**
     * Get thumbnails storage path
     *
     * @param boolean $relative    
     * @return string
    */
    public static function getThumbnailsPath($imageId, bool $relative = true): string
    {
        if ($relative == false) {
            return Path::STORAGE_PATH . Self::THUMBNAILS_PATH . $imageId . DIRECTORY_SEPARATOR;
        }

        return Self::THUMBNAILS_PATH . $imageId . DIRECTORY_SEPARATOR;
    }

    /**
     * Create thumbails path for image
     *
     * @param string|int $imageId    
     * @return string
     */
    public static function createThumbnailsPath($imageId): string
    {
        $path = Self::getThumbnailsPath($imageId,false);
        if (File::exists($path) == false) {
            // create
            File::makeDir($path);
        }
      
        return $path;
    }   

    /**
     * Create thumbnail file name
     *
     * @param string $fileName
     * @param string $width
     * @param string $height
     * @return string
     */
    public static function createThumbnailFileName(string $fileName, string $width, string $height): string
    {
        $info = \pathinfo($fileName);

        return Self::THUMBNAILS_FILE_NAME_PREFIX . $info['filename'] . '-' . $width . 'x' . $height . '.' . $info['extension'];
    }
}
