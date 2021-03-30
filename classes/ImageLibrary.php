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

/**
 * Image library class
 */
class ImageLibrary
{
    const IMAGES_STORAGE_PATH      =  Path::STORAGE_PUBLIC_PATH . 'images' . DIRECTORY_SEPARATOR;
    const VIEW_PROTECTED_IMAGE_URL = '/api/image/view/';
    
    const THUMBNAILS_STORAGE_PATH     = Self::IMAGES_STORAGE_PATH . 'thumbnails' . DIRECTORY_SEPARATOR;
    const THUMBNAILS_FILE_NAME_PREFIX = 'thumbnail-';

    /**
     * Get images storage path
     *   
     * @param boolean $relative
     * @param string $folder
     * @param bool $private
     * @return string
     */
    public static function getStoragePath(bool $relative = true, string $folder = '', bool $private = false): string
    {
        $path = (empty($private) == true) ? 'public' . DIRECTORY_SEPARATOR . Self::IMAGES_STORAGE_PATH : Self::IMAGES_STORAGE_PATH;

        return ($relative == true) ? $path . $folder : Path::STORAGE_PATH . $path . $folder;
    }

    /**
     * Get thumbnails storage path
     *
     * @param boolean $relative    
     * @return string
    */
    public static function getThumbnailsStoragePath(bool $relative = true): string
    {
        return ($relative == false) ? Self::THUMBNAILS_STORAGE_PATH : 'public/images/thumbnails/';
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

        return ImageLibrary::THUMBNAILS_FILE_NAME_PREFIX . $info['filename'] . '-' . $width . 'x' . $height . '.' . $info['extension'];
    }
}
