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
        'file',
        'media_id',      
        'file_size',
        'mime_type',
        'key',
        'url',
        'width',
        'height',
        'size'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
   
    /**
     * Media relation
     *
     * @return Relation
     */
    public function media()
    {
        return $this->belongsTo(Image::class,'media_id');
    }

    /**
     * Gte smallest thumbnail
     *
     * @return Model|null
    */
    public function scopeSmall($query, $mediaId = null)
    {
        $query = (empty($mediaId) == false) ? $query->where('image_id','=',$mediaId) : $query;

        return $query->orderBy('width','asc')->first();
    }

    /**
     * Create file name
     *
     * @param string $fileName
     * @param string $width
     * @param string $height
     * @return string
     */
    public function createFileName($fileName, $width, $height)
    {
        $info = \pathinfo($fileName);

        return 'thumbnail-' . $info['filename'] . '-' . $width . 'x' . $height . '.' . $info['extension'];
    }

    /**
     * Creathe file name form media name
     *
     * @param string $fileName
     * @return void
     */
    public function resolveFileName($fileName)
    {
        $this->file = $this->createFileName($fileName,$this->width,$this->height);
    }

    /**
     * Find thumbnail query
     *
     * @param Builder $query
     * @param integer $id
     * @param string $width
     * @param string $height
     * @return Builder
     */
    public function scopeFindThumbnail($query, $id, $width, $height)
    {
        return $query->where('media_id',$id)->where('width','=',$width)->where('height','=',$height);
    }

    /**
     * Return true if thumbnail exist
     *
     * @param integer $id
     * @param string $width
     * @param string $height
     * @return boolean
     */
    public function hasThumbnail($id, $width, $height)
    {
        $query = $this->findThumbnail($id,$width,$height)->first();

        return \is_object($query);
    }

    /**
     * Create thumbnail model
     *
     * @param string $width
     * @param string $height
     * @return Model
     */
    public function findOrCreateThumbnailModel($width, $height)
    {
        $model = new ImageThumbnails();
        $query = $model->findThumbnail($this->id,$width,$height)->first();
        if (\is_object($query) == true) {
            return $query;
        }

        return $model->create([
            'media_id' => $this->id,
            'width'    => $width,
            'height'   => $height,
            'file'     => $model->createFileName($this->file,$width,$height) 
        ]);
    }
}
