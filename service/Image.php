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
use Arikaim\Extensions\Image\Classes\ImageLibrary;

use Arikaim\Core\System\Error\Traits\TaskErrors;

/**
 * Player service class
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
     * Create thumbnail
     *
     * @param mixed $image
     * @param integer $width
     * @param integer $height
     * @return Model|null
     */
    public function createThumbnail($image, int $width, int $height)
    {
        $model = (\is_object($image) == true) ? $image : Model::Image('image')->findImage($image);      
        if (\is_object($model) == false) {
            $this->addError('errors.id');
            return null;
        }  

        $thumbnail = Model::ImageThumbnails('image');

        if ($thumbnail->hasThumbnail($width,$height,$model->id) == true) {
            $this->addError('errors.thumbnail.exist');
            return null;
        }   
        
        $image = $this->getService('image')->resize($model->getImagePath(),$width,$height);
        if (empty($image) == true) {
            $this->addError('errors.image.resize');
            return null;
        }   
    
        // save thumb image
        $fileName = ImageLibrary::createThumbnailFileName($model->file_name,$width,$height);
        $path = ImageLibrary::getThumbnailsStoragePath(false);
        $result = $this->getService('image')->save($image,$path,$fileName);
        if ($result === false) {
            $this->addError('errors.thumbnail.create');
            return null;
        }

        return $thumbnail->findOrCreate($width,$height,$model->id);          
    }
}
