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
     * Save collection
     *
     * @param string       $title
     * @param string|null  $slug
     * @param integer|null $userId
     * @return Model|false
     */
    public function saveCollection(string $title, ?string $slug = null, ?int $userId = null)
    {
        global $container;

        $userId = $userId ?? $container->get('access')->getId();

        return Model::ImageCollections('image')->saveCollection($title,$slug,$userId);
    }

    /** */
    public function addImageToCollection($image, string $collectionSlug, ?int $userId = null): bool
    {
        global $container;

        $userId = $userId ?? $container->get('access')->getId();
        $collection = Model::ImageCollections('image')->findCollection($collectionSlug,$userId);
        if ($collection == null) {
            return false;
        }

        $image = (\is_object($image) == true) ? $image : Model::Image('image')->findImage($image);
        if ($image == null) {
            return false;
        }

        return ($collection->addImage($image->id) !== false);
    }

    /**
     * Find image
     *
     * @param string $name
     * @param string|null $excludeId
     * @return Model|null
     */
    public function findImage(string $name, ?string $excludeId = null): ?object
    {
        return Model::Image('image')->findImage($name,$excludeId);
    }

    /**
     * Delete image
     *
     * @param string|int $uuid
     * @return bool
     */
    public function delete($uuid): bool
    {       
        $image = Model::Image('image')->findById($uuid);
        $result = ($image == null) ? false : $image->deleteImage();
           
        return ($result !== false);
    }

    /**
     * Get encoded image
     *
     * @param mixed $uuid
     * @return string|null
     */
    public function getEncodedImage($uuid): ?string
    {
        global $container;

        $image = Model::Image('image')->findById($uuid);    
        if ($image == null) {
            return null;
        }
        
        if (File::exists($image->file_name) == true) {           
            $data = File::read($image->file_name);
            return \base64_encode($data);
        }
        
        if ($container->get('storage')->has($image->file_name,'storage') == true) {
            $data = $container->get('storage')->read($image->file_name);
            return \base64_encode($data);
        }

        return null;
    }

    /**
     * Get view url
     *
     * @param string $path
     * @return string
     */
    public function getViewUrl(string $path): string
    {
        return ImageLibrary::getViewUrl($path);
    }

    /**
     * Save image relation
     *
     * @param Model|int $image
     * @param integer $relationId
     * @param string $relationType
     * @return Model|bool
     */
    public function saveRelation($image, int $relationId, string $relationType)
    {
        $imageId = (\is_object($image) == true) ? $image->id : $image;
        $model = Model::ImageRelations('image');

        return $model->saveRelation($imageId,$relationType,$relationId);
    }

    /**
     * Get image relations
     *
     * @param integer|null $relationId
     * @param string|null $relationType
     * @return Colleciton|null
     */
    public function getRelatedImages(?int $relationId, ?string $relationType)
    {
        if (empty($relationId) == true || empty($relationType) == true) {
            return null;
        }
        return Model::ImageRelations('image')->getRelationsQuery($relationId,$relationType)->get(); 
    }

    /**
     * Get related image
     *
     * @param integer|int $relationId
     * @param string|int $relationType
     * @return object|null
     */
    public function getRelatedImage(?int $relationId, ?string $relationType): ?object
    {
        if (empty($relationId) == true || empty($relationType) == true) {
            return null;
        }
        $model = Model::ImageRelations('image')->getRelationsQuery($relationId,$relationType)->first(); 

        return ($model == null) ? null : $model->image;
    }

    /**
     * Get image thumbnail
     *
     * @param integer|null $relationId
     * @param string $relationType
     * @param integer|null $width
     * @param integer|null $height
     * @param bool $create Create if not exist
     * @return Model|null
     */
    public function getThumbnail(?int $relationId, string $relationType, ?int $width, ?int $height, bool $create = true)
    {
        $width = (empty($width) == true) ? 64 : $width;
        $height = (empty($height) == true) ? 64 : $height;

        if (empty($relationId) == true) {
            return null;
        }

        $image = $this->getRelatedImage($relationId,$relationType);
        if ($image == null) {
            return null;
        }

        $thumbnail = $image->thumbnail($width,$height);
       
        if ((empty($thumbnail) == true) && ($create == true)) {
            $this->createThumbnail($image,$width,$height);
            $thumbnail = $image->thumbnail($width,$height);
        }

        return $thumbnail;
    }

    /**
     * Return true if relation exists
     *
     * @param integer $relationId
     * @param string $relationType
     * @return bool
     */
    public function hasRelatedImage(int $relationId, string $relationType): bool
    {
        return ($this->getRelatedImage($relationId,$relationType) !== null);      
    }

    /**
     * Get default images storage path
     *
     * @param boolean $relative
     * @param string|null $path
     * @return string
     */
    public function getDefaultImagesPath(bool $relative = false, ?string $path = null): string
    {
        return ImageLibrary::getImagesPath($relative,$path);
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
        if ($model == null) {
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
     * Resize and save image
     *
     * @param string $fileName
     * @param integer|null $userId
     * @param integer|null $width
     * @param integer|null $height
     * @param array $options
     * @return Model|null
     */
    public function resizeAndSave(
        string $fileName, 
        ?int $userId, 
        ?int $width = null, 
        ?int $height = null, 
        array $options = [],
        bool $protected = false)
    {
        if (empty($width) == false && empty($height) == false) {
            $image = $this->getService('image')->resize($fileName,$width,$height);
            $result = $this->getService('image')->save($image,$fileName,'');
            if ($result === false) {
                return null;
            }
        }

        return $this->save($fileName,$userId,$options,$protected);
    } 

    /**
     * Save image
     *
     * @param string $fileName
     * @param integer|null $userId
     * @param bool $options    
     * @return Model|null
     */
    public function save(string $fileName, ?int $userId, array $options = [], bool $protected = false): ?object
    {
        $path = ($protected == false) ? Path::getRelativePath($fileName,false) : $fileName;
        $model = Model::Image('image');   

        $imageId = $options['image_id'] ?? null;
        $image = (empty($imageId) == false) ? $model->findById($imageId) : null;
        
        $data = [
            'file_name'   => $path,
            'file_size'   => File::getSize($fileName),
            'mime_type'   => File::getMimetype($fileName),
            'base_name'   => File::baseName($fileName),
            'user_id'     => $userId,  
            'deny_delete' => $options['deny_delete'] ?? null,
            'category_id' => $options['category_id'] ?? null,
            'private'     => $options['private'] ?? null
        ];

        $size = $this->getService('image')->getSize($fileName);
        if (\is_array($size) == true) {
            $data['width'] = $size['width']; 
            $data['height'] = $size['height'];
        }
        
        if ($image !== null) {
            // edit existing image id
            $image->update($data);
            $this->createThumbnail($image,64,64);
            return $image;
        }

        if ($model->hasImage($path) == true) {
            // update
            $model = $model->findImage($path);
            $image = ($model->update($data) !== false) ? $model : null;          
        } else {
            // create
            $image = $model->create($data);         
        }

        if ($image != null) {
            $this->createThumbnail($image,64,64);
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
    public function import(string $url, string $fileName, ?int $userId, array $options = [], bool $protected = false): ?object
    {         
        Curl::downloadFile($url,$fileName);

        return $this->save($fileName,$userId,$options,$protected);
    }          
}
