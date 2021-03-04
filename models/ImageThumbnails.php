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
use Arikaim\Extensions\Image\Classes\ImageLibrary;
use Arikaim\Extensions\Image\Models\Image;
use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\DateCreated;

/**
 * Image thumbnails db model class
 */
class ImageThumbnails extends Model  
{
    use Uuid,           
        Find,
        DateCreated; 
       
    /**
     * Table name
     *
     * @var string
    */
    protected $table = 'image_thumbnails';

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [        
        'file_name',
        'image_id',            
        'mime_type',     
        'url',
        'width',
        'height'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
   
    /**
     * Delete thumbnail
     *
     * @param string|null $name
     * @param int|null $userId
     * @return boolean
     */
    public function deleteThumbnail(?string $name = null): bool
    {
        $name  = $name ?? $this->id;
        $model = $this->findById($name);
        if (\is_object($model) == false) {
            $model = $this->where('file_name','=',$name)->first();
            if (\is_object($model) == false) {
                return false;
            }
        }
        
        $path = ImageLibrary::getThumbnailsStoragePath(false) . $model->file_name;
        if (File::exists($path) == true) {
            File::delete($path);
        }    
        
        return (bool)$model->delete();
    }

    /**
     * Image relation
     *
     * @return Relation|null
     */
    public function image()
    {
        return $this->belongsTo(Image::class,'image_id');
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
       
        return ImageLibrary::getThumbnailsStoragePath(true) . $this->file_name;
    }

    /**
     * Get image thumbnails
     *
     * @param int|null $imageId
     * @return Builder|null
    */
    public function scopeImageThumbnails($query, ?int $imageId = null)
    {
        $imageId = $imageId ?? $this->image_id;
        
        return $query->where('image_id','=',$imageId)->orderBy('width','asc');      
    }

    /**
     * Find thumbnail query
     *
     * @param Builder $query    
     * @param integer $width
     * @param integer $height
     * @param integer|null $imageId
     * @return Builder
     */
    public function scopeFindThumbnail($query, int $width, int $height, ?int $imageId = null)
    {
        $imageId = $imageId ?? $this->image_id;

        return $query->where('image_id','=',$imageId)->where('width','=',$width)->where('height','=',$height);
    }

    /**
     * Return true if thumbnail exist
     *
     * @param integer|null $imageId
     * @param integer $width
     * @param integer $height
     * @return boolean
     */
    public function hasThumbnail(int $width, int $height, ?int $imageId = null): bool
    {
        $imageId = $imageId ?? $this->image_id;
        $query = $this->findThumbnail($width,$height,$imageId)->first();

        return \is_object($query);
    }

    /**
     * Create thumbnail model
     *
     * @param integer|null $imageId
     * @param integer $width
     * @param integer $height
     * @return Model
     */
    public function findOrCreate(int $width, int $height, ?int $imageId = null)
    {
        $imageId = $imageId ?? $this->image_id;
       
        $model = $this->findThumbnail($width,$height,$imageId)->first();
        if (\is_object($model) == true) {
            return $model;
        }

        return $this->createThumbnail($width,$height,$imageId);
    }

    /**
     * Create thumbnail model
     *
     * @param integer|null $imageId
     * @param integer $width
     * @param integer $height
     * @return Model|null
    */
    public function createThumbnail(int $width, int $height, ?int $imageId = null)
    {
        $imageId = $imageId ?? $this->image_id;
      
        $image = new Image();
        $image = $image->findByid($imageId);
        if (\is_object($image) == false) {
            return null;
        }

        return $this->create([
            'image_id'  => $imageId,
            'width'     => $width,
            'height'    => $height,
            'mime_type' => $image->mime_type,
            'file_name' => ImageLibrary::createThumbnailFileName($image->file_name,(string)$width,(string)$height) 
        ]);
    }
}
