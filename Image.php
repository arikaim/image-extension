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
use Arikaim\Extensions\Image\Classes\ImageLibrary;

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
        $this->addApiRoute('POST','/api/admin/image/upload','ImageControlPanel','upload','session');    
        $this->addApiRoute('POST','/api/admin/image/import','ImageControlPanel','import','session');              
        $this->addApiRoute('DELETE','/api/admin/image/delete/{uuid}','ImageControlPanel','delete','session'); 
        $this->addApiRoute('GET','/api/admin/image/list/{data_field}/[{query}]','ImageControlPanel','getList','session');             
        // thumbnails
        $this->addApiRoute('POST','/api/admin/image/thumbnail/create','ThumbnailsControlPanel','create','session');              
        $this->addApiRoute('DELETE','/api/admin/image/thumbnail/{uuid}','ThumbnailsControlPanel','delete','session'); 
        // api 
        $this->addApiRoute('GET','/api/image/view/{uuid}','ImageApi','view',null);    
        $this->addApiRoute('GET','/api/image/view/thumbnail/{slug}','ImageApi','viewThumbnail',null);  
        // create db tables
        $this->createDbTable('ImageSchema');   
        $this->createDbTable('ImageRelationsSchema');                    
        $this->createDbTable('ImageThumbnailsSchema');   
        // Console Commands
        $this->registerConsoleCommand('DeleteImages');          
        // Relation map 
        $this->addRelationMap('image','Image');
        // protected storage folder
        $this->createStorageFolder(ImageLibrary::IMAGES_PATH,false);
        // public storage folder
        $this->createStorageFolder(ImageLibrary::IMAGES_PATH,true);
        $this->createStorageFolder(ImageLibrary::IMAGES_PATH . 'thumbnails',true);
        // Services
        $this->registerService('Image');
        // Events
        $this->registerEvent('image.upload','Trigger after image is uplaoded.');
        $this->registerEvent('image.import','Trigger after image is imported from remote url.');
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
