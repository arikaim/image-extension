<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Models\Traits;

use Arikaim\Extensions\Image\Models\Image;

/**
 * Image relation trait
 *      
*/
trait ImageRelation 
{    
    /**
     * Get image relation
     *
     * @return Relation|null
     */
    public function image()
    {
        return $this->belongsTo(Image::class,'image_id');
    }

    /**
     * Return true if image relation exist
     *
     * @return boolean
     */
    public function hasImage(): bool
    {
        return (empty($this->image_id) == true) ? false : ($this->image->first() !== null);
    }
}
