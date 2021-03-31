<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Service;

use Psr\Container\ContainerInterface;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Service\Service;
use Arikaim\Core\Service\ServiceInterface;
use Arikaim\Core\Utils\Curl;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;
use Arikaim\Extensions\Image\Classes\ImageLibrary;

use Arikaim\Core\System\Error\Traits\TaskErrors;

/**
 * Image service class
*/
class Image extends Service implements ServiceInterface
{
    use TaskErrors;

    /**
     * Constructor
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->setServiceName('image.library');
        $this->includeServices(['image']);

        parent::__construct($container);
    }

    /**
     * Get default images storage path
     *
     * @return string
     */
    public function getDefaultImagesPath(): string
    {
        return ImageLibrary::IMAGES_PATH;
    }

    /**
     * Create thumbnail
     *
     * @param mixed $image
     * @param integer $width
     * @param integer $height
     * @return bool
     */
    public function createThumbnail($image, int $width, int $height): bool
    {
        $model = (\is_object($image) == true) ? $image : Model::Image('image')->findImage($image);      
        if (\is_object($model) == false) {
            $this->addError('errors.id');
            return false;
        }  

        $thumbnail = Model::ImageThumbnails('image');
        $image = $this->getService('image')->resize($model->getImagePath(false),$width,$height);
        if (empty($image) == true) {
            $this->addError('errors.image.resize');
            return false;
        }   
    
        // save thumb image
        $fileName = ImageLibrary::createThumbnailFileName($model->file_name,$width,$height);
        $path = ImageLibrary::createThumbnailsPath($model->id,false);

        $result = $this->getService('image')->save($image,$path,$fileName);
        if ($result === false) {
            $this->addError('errors.thumbnail.create');
            return false;
        }

        return $thumbnail->saveThumbnail($width,$height,$model->id);          
    }

    /**
     * Save image
     *
     * @param string $fileName
     * @param integer|null $userId
     * @param bool $options    
     * @return Model|null
     */
    public function save(string $fileName, ?int $userId, array $options = [])
    {
        $relativePath = Path::getRelativePath($fileName,false);
        $model = Model::Image('image');       
        $data = [
            'file_name'   => $relativePath,
            'file_size'   => File::getSize($fileName),
            'mime_type'   => File::getMimetype($fileName),
            'base_name'   => File::baseName($fileName),
            'user_id'     => $userId,  
            'deny_delete' => $options['deny_delete'] ?? null,
            'private'     => $options['private'] ?? null
        ];

        $size = $this->getService('image')->getSize($fileName);
        if (\is_array($size) == true) {
            $data['width'] = $size['width']; 
            $data['height'] = $size['height'];
        }
        
        if ($model->hasImage($relativePath) == true) {
            // update
            $model = $model->findImage($relativePath);
            $image = ($model->update($data) !== false) ? $model : null;          
        } else {
            // create
            $image = $model->create($data);         
        }

        if (\is_object($image) == true) {
            $result = $this->createThumbnail($image,64,64);
            return $image;
        }

        return null;
    }

    /**
     * Import image from url
     *
     * @param string $url
     * @param string $fileName
     * @param integer|null $userId
     * @param bool|null $options   
     * @return Model|null
    */
    public function import(string $url, string $fileName, ?int $userId, array $options = [])
    {         
        Curl::downloadFile($url,$fileName);

        return $this->save($fileName,$userId,$options);
    }          
}
