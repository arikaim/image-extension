<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\MetaTags;

/**
 * Media language translations model class
 */
class MediaTranslations extends Model  
{
    use 
        Uuid,      
        MetaTags,
        Find;
       
    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'media_translations';

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'media_id',
        'display_name',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'language'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
}
