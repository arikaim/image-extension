<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Utils\File;
use Arikaim\Extensions\Image\Models\ImageThumbnails;
use Arikaim\Extensions\Image\Classes\ImageLibrary;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\UserRelation;
use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Db\Traits\FileTypeTrait;

/**
 * Image db model class
 */
class Image extends Model  
{
    const IMAGES_STORAGE_PATH = 'images' . DIRECTORY_SEPARATOR;
    const VIEW_PROTECTED_IMAGE_URL = '/api/image/view/';

    use Uuid,     
        Find, 
        DateCreated,  
        UserRelation, 
        FileTypeTrait;
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'image';

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [           
    ];

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'private', 
        'file_size',
        'mime_type',
        'file_name', 
        'url',
        'width',
        'height',      
        'user_id'       
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
   
    /**
     * Create thumbnail model
     *   
     * @param integer $width
     * @param integer $height
     * @return Model|null
    */
    public function createThumbnail(int $width, int $height)
    {
        return ImageThumbnails::createThumbnail($width,$height,$this->image_id);      
    }

    /**
     * storaget_path attribute GET
     *
     * @return string
     */
    public function getStoragePathAttribute()
    {
        return $this->getStoragePath(true,$this->user_id,$this->private);
    } 

    /**
     * src attribute
     *
     * @return string
     */
    public function getSrcAttribute()
    {
        if (empty($this->url) == false) {
            return $this->url;
        }
        $path = ($this->private === true) ? ImageLibrary::VIEW_PROTECTED_IMAGE_URL : $this->getStoragePath(true);

        return $path . $this->file_name;
    }

    /**
     * Create user images storage folder
     *
     * @param int|null $userId
     * @param bool $privateStorage
     * @return boolean
     */
    public function createUserImagesStoragePath(?int $userId = null, bool $privateStorage = false): bool
    {
        $userId = (empty($userId) == true) ? $this->user_id : $userId;
        $path = $this->getStoragePath(false,$userId,$privateStorage);

        if (File::exists($path) == false) {
            return File::makeDir($path);
        }

        return true;
    }

    /**
     * Get images storage path
     *
     * @param mixed|null $userId
     * @param boolean $relative
     * @param bool|null $private
     * @return string
     */
    public function getStoragePath(bool $relative = true, $userId = null, ?bool $private = null): string
    {
        $userId = (empty($userId) == true) ? $this->user_id : $userId;
        $private = (\is_null($private) == true) ? $this->private : $private;
        
        return ImageLibrary::getStoragePath($relative,$userId,$private);
    }

    /**
     * Get image path
     *
     * @param boolean $relative
     * @return string
     */
    public function getImagePath(bool $relative = true): string
    {
        return $this->getStoragePath($relative) . $this->file_name;
    }

    /**
     * Get user images query
     *
     * @param Builder $query
     * @param int|null $userId
     * @return Builder
     */
    public function scopeUserImagesQuery($query, ?int $userId)
    {
        $userId = (empty($userId) == true) ? $this->user_id : $userId;

        return $query->where('user_id','=',$userId);
    }

    /**
     * Thumbnails relation
     *
     * @return Relation|null
     */
    public function thumbnails()
    {
        return $this->hasMany(ImageThumbnails::class,'image_id');
    }

    /**
     * Thumbnail
     *
     * @param integer $width
     * @param integer $height
     * @return Model|null
     */
    public function thumbnail(int $width, int $height)
    {
        return $this->thumbnails->where('width','=',$width)->where('height','=',$height)->first();
    }

    /**
     * Get smallest thumbnail
     *
     * @return Model|null
     */
    public function thumbnailSmall()
    {
        return $this->thumbnails()->orderBy('width','asc')->first();
    }

    /**
     * Get large thumbnail
     *
     * @return Model|null
     */
    public function thumbnailLarge()
    {
        return $this->thumbnails()->orderBy('width','desc')->first();
    }

    /**
     * Find image
     *
     * @param string $name
     * @param integer|null $userId
     * @param string|null $excludeId
     * @return Model|null
     */
    public function findImage(string $name, ?int $userId = null, ?string $excludeId = null)
    {
        $userId = (empty($userId) == true) ? $this->user_id : $userId;

        // by id, uuid
        $query = $this->where(function($query) use ($name,$excludeId) {
            $query->where('uuid','=',$name);
            if (empty($excludeId) == false) {
                $query->where('uuid','<>', $excludeId);
            }
        })->orWhere(function($query) use ($name,$userId,$excludeId) {
            $query->where('file_name','=',$name);
            $query->where('user_id','=',$userId);
            if (empty($excludeId) == false) {
                $query->where('uuid','<>', $excludeId);
            }
        })->orWhere(function($query) use ($name,$userId,$excludeId) {
            $query->where('slug','=',$name);
            $query->where('user_id','=',$userId);
            if (empty($excludeId) == false) {
                $query->where('uuid','<>', $excludeId);
            }
        })->orWhere(function($query) use ($name,$excludeId) {
            $query->where('url','=',$name);
            if (empty($excludeId) == false) {
                $query->where('uuid','<>', $excludeId);
            }
        });
        
        return $query->first(); 
    } 

    /**
     * Check if image file exists
     *
     * @param string $title
     * @param int|int $userid
     * @param string|null $excludeId
     * @return boolean
     */
    public function hasImage(string $title, ?int $userId = null, ?string $excludeId = null): bool
    {
        return (bool)\is_object($this->findImage($title,$userId,$excludeId));
    }

    /**
     * Delete image and relations 
     *
     * @param string|null $name
     * @param int|null $userId
     * @return boolean
     */
    public function deleteImage(?string $name = null, ?int $userId = null): bool
    {
        $model = (empty($name) == true) ? $this : $this->findImage($name,$userId);
        if (\is_null($model) == true) {
            return false;
        }

        $this->thumbnails()->deleteThumbnail();
        // Delete relations

        $this->deleteImageFile($model);

        return (bool)$model->delete();        
    } 

    /**
     * Delete image file 
     *
     * @param Model|null $model
     * @return boolean
    */
    public function deleteImageFile($model = null): bool
    {
        $model = (\is_null($model) == true) ? $this : $model;
        if (empty($model->file_name) == true) {
            return false;
        }
        $path = $model->getStoragePath(false,null,$model->private) . $model->file_name;

        return (File::exists($path) == true) ? File::delete($path) : true;         
    }     
}
