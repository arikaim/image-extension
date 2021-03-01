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

use Arikaim\Extensions\Image\Models\ImageThumbnails;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Arikaim;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Slug;
use Arikaim\Core\Db\Traits\UserRelation;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Db\Traits\FileTypeTrait;

/**
 * Media db model class
 */
class Media extends Model  
{
    use Uuid,     
        Find,
        Slug,
        DateCreated,  
        UserRelation,   
        FileTypeTrait,
        Status;
    
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
        'title',
        'private',
        'status',     
        'slig',
        'file_size',
        'mime_type',
        'file_name',
        'title',
        'slug',     
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
     * Get images storage path
     *
     * @param integer $userId
     * @param boolean $relative
     * @return string
     */
    public function getStoragePath(int $userId, bool $relative = true): string
    {
        $path = 'images' . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR;
        $path = (empty($this->private) == true) ? 'public' . DIRECTORY_SEPARATOR . $path : $path;

        return ($relative == true) ? $path : Path::STORAGE_PATH . $path;
    }

    /**
     * Get user images query
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeUserImagesQuery($query, int $userId)
    {
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
     * @param integer $userId
     * @param string|null $excludeId
     * @return Model|null
     */
    public function findImage(string $name, int $userId = null, ?string $excludeId = null)
    {
        $userId = (empty($userId) == true) ? Arikaim::get('access')->getid() : $userId;
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
     * @param int $userid
     * @param string|null $excludeId
     * @return boolean
     */
    public function hasImage(string $title, int $userId, ?string $excludeId = null): bool
    {
        return (bool)\is_object($this->findImage($title,$userId,$excludeId));
    }

    /**
     * Delete image and relations 
     *
     * @param string $name
     * @param int $userId
     * @return boolean
     */
    public function deleteImage(string $name, int $userId)
    {
        $model = $this->findImage($name,$userId);
        if (\is_null($model) == true) {
            return false;
        }

        // Delete thumbnail
        $this->thumbnails()->delete();

        

        return $model->delete();
    } 

    /**
     * Get media page url
     *
     * @param boolean $full
     * @param string|null $customPath 
     * @return string
     */
    public function getViewUrl($customPath = null)
    {
        $path = (empty($customPath) == true) ? '/api/media/view/' : $customPath;

        return $path . $this->slug;
    }

    /**
     * Create media files assets folder
     *
     * @return boolean
     */
    public function createUploadPath($userPath)
    {
        $path = $this->getMediaFilesPath($userPath);
        
        return (File::exists($path) == false) ? File::makeDir($path) : true;       
    }

    /**
     * Get media files path
     *
     * @param boolean $relative
     * @return string
     */
    public function getMediaFilesPath($userPath, $relative = false)
    {
        $path = $userPath . 'media' . DIRECTORY_SEPARATOR;

        return ($relative == true) ? $path : Path::STORAGE_PATH . $path;
    }

    /**
     * Get media file path
     *
     * @param string $userPath
     * @param boolean $relative
     * @return string
     */
    public function getMediaFilePath($userPath, $relative = false)
    {
        return $this->getMediaFilesPath($userPath,$relative) . $this->file;
    }
}
