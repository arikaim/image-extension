<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * Media thumbnails table schema definition.
 */
class MediaThumbnailsSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'media_thumbnails';

    /**
     * Create table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function create($table) 
    {            
        // columns
        $table->id();
        $table->prototype('uuid');        
        $table->relation('media_id','media');
        $table->string('mime_type')->nullable(true);
        $table->string('file_size')->nullable(true);
        $table->string('file')->nullable(true);       
        $table->string('url')->nullable(true);       
        $table->integer('width')->nullable(true);
        $table->integer('height')->nullable(true);
        $table->string('key')->nullable(true);     
        $table->dateCreated();
        // indexes        
        $table->index('file');
        $table->unique(['media_id','key']);
        $table->unique(['media_id','width','height']);
    }

    /**
     * Update table
     *
     * @param \Arikaim\Core\Db\TableBlueprint $table
     * @return void
     */
    public function update($table) 
    {              
    }
}
