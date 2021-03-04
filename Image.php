<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image;

use Arikaim\Core\Extension\Extension;

/**
 * Image extension
*/
class Image extends Extension
{
    /**
     * Install extension routes, events, jobs
     *
     * @return void
    */
    public function install()
    {
        // Control Panel
        $this->addApiRoute('POST','/api/image/admin/upload','ImageControlPanel','upload','session');    
        $this->addApiRoute('POST','/api/image/admin/import','ImageControlPanel','import','session');              
        $this->addApiRoute('DELETE','/api/image/admin/delete/{uuid}','ImageControlPanel','delete','session'); 
        $this->addApiRoute('GET','/api/image/admin/list/[{query}]','ImageControlPanel','getList','session');             
        // thumbnails
        $this->addApiRoute('POST','/api/image/admin/thumbnail/create','ThumbnailsControlPanel','create','session');              
        $this->addApiRoute('DELETE','/api/image/admin/thumbnail/{uuid}','ThumbnailsControlPanel','delete','session'); 
        // api 
        $this->addApiRoute('GET','/api/image/view/{slug}','ImageApi','view',null);    
        $this->addApiRoute('GET','/api/image/view/thumbnail/{slug}','ImageApi','viewThumbnail',null);  
        // create db tables
        $this->createDbTable('ImageSchema');   
        $this->createDbTable('ImageRelationsSchema');                    
        $this->createDbTable('ImageThumbnailsSchema');         
        // Relation map 
        $this->addRelationMap('image','Image');
        // protected storage folder
        $this->createStorageFolder('images',false);
        // public storage folder
        $this->createStorageFolder('images',true);
        $this->createStorageFolder('images/thumbnails',true);
          // Services
          $this->registerService('Image');
    }       

    /**
     * Post install actions
     *
     * @return void
     */
    public function postInstall()
    {
    }

    /**
     * Uninstall extension
     *
     * @return void
     */
    public function unInstall()
    {         
    }
}
