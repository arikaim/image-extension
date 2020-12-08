<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Models\Schema;

use Arikaim\Core\Db\Schema;

/**
 * Image database table schema definition.
 */
class ImageSchema extends Schema  
{    
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = 'image';

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
        $table->status();
        $table->slug();
        $table->string('title')->nullable(true);   
        $table->text('description')->nullable(true);       
        $table->string('mime_type')->nullable(true);
        $table->string('file_size')->nullable(true);
        $table->string('src')->nullable(true);
        
        $table->integer('views')->nullable(false)->default(0);       
        $table->dateCreated();
        $table->dateDeleted();
        // indexes        
        $table->unique('src'); 
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
