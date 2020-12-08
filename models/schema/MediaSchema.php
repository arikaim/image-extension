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
 * Media database table schema definition.
 */
class MediaSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'media';

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
        $table->userId();
        $table->position();
        $table->status();
        $table->slug();
        $table->string('title')->nullable(false);   
        $table->string('display_name')->nullable(true);      
        $table->text('description')->nullable(true);       
        $table->string('mime_type')->nullable(true);
        $table->string('file_size')->nullable(true);
        $table->string('file')->nullable(true);
        $table->string('provider')->nullable(true);
        $table->string('video_id')->nullable(true);
        $table->string('duration')->nullable(true);        
        $table->integer('featured')->nullable(true);              
        $table->integer('views')->nullable(false)->default(0);       
        $table->text('options')->nullable(true);  
        $table->dateCreated();
        $table->dateDeleted();
        // indexes        
        $table->unique('title'); 
        $table->index('views');
        $table->index('mime_type');
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

    /**
     * Insert or update rows in table
     *
     * @param Seed $seed
     * @return void
     */
    public function seeds($seed)
    {   
    }
}
