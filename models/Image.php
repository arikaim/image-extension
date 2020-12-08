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

use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Extensions\Media\Models\ImageThumbnails;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Slug;
use Arikaim\Core\Db\Traits\UserRelation;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Db\Traits\SoftDelete;
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
        SoftDelete,
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
        'position',
        'description',     
        'src',
        'file_size',
        'mime_type',
        'status',
        'title',
        'uuid',
        'date_deleted',   
        'slug',           
        'user_id'       
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
   
    /**
     *  Custom media path suffix
     */
    protected $customMediaPathSuffix = '';

    /**
     * Get user images query
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeUserImagesQuery($query, $userId)
    {
        return $query->where('user_id','=',$userId);
    }

    /**
     * Thumbnails relation
     *
     * @return Relation
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
     * Check if media file exists
     *
     * @param string $title
     * @param string $excludeUuid
     * @return boolean
     */
    public function hasMedia($title, $excludeUuid = null)
    {
        if (empty($title) == true) {
            return false;
        }

        $model = $this->where('title','=',$title);
        if (empty($excludeUuid) == false) {
            $model = $model->where('uuid','<>', $excludeUuid);
        }      

        return (bool)\is_object($model->first());
    }

    /**
     * Delete media and relations 
     *
     * @param string|integer $id
     * @return boolean
     */
    public function deleteMedia($id = null)
    {
        $id = (empty($id) == true) ? $this->id : $id;
        $model = $this->findById($id);     
        if (\is_object($model) == false) {
            return false;
        }
        // Delete Translations
        $model->removeTranslations();

        // Delete thumbnail
        $this->thumbnails()->delete();

        // Delete category relations
        $categoryRelations = DbModel::create('CategoryRelations','category');
        if (\is_object($categoryRelations) == true) {
            $query = $categoryRelations->getRelationsQuery($model->id,'media');
            $query->delete();
        }
        
        // Delete tags relations
        $tagsRelations = DbModel::create('TagsRelations','tags');
        if (\is_object($tagsRelations) == true) {
            $query = $tagsRelations->getRelationsQuery($model->id,'media');
            $query->delete();
        }

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
