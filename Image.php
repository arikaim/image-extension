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
        $this->addApiRoute('PUT','/api/image/admin/update','ImageControlPanel','update','session');  
        $this->addApiRoute('DELETE','/api/image/admin/delete/{uuid}','ImageControlPanel','delete','session'); 
        $this->addApiRoute('PUT','/api/image/admin/status','ImageControlPanel','setStatus','session');         
        // thumbnails
        $this->addApiRoute('POST','/api/image/admin/thumbnail/create','ThumbnailsControlPanel','create','session');        
        $this->addApiRoute('POST','/api/image/admin/thumbnail/upload','ThumbnailsControlPanel','upload','session');     
        $this->addApiRoute('DELETE','/api/image/admin/thumbnail/{uuid}','ThumbnailsControlPanel','delete','session'); 
        // Api 
        $this->addApiRoute('GET','/api/image/view/{slug}','ImageApi','view',null);    
        $this->addApiRoute('GET','/api/image/view/thumbnail/{slug}/{uuid}','ImageApi','viewThumbnail',null);  

        // Create db tables
        $this->createDbTable('ImageSchema');   
        $this->createDbTable('ImageRelationsSchema');                    
        $this->createDbTable('ImageThumbnailsSchema');         
        // Relation map 
        $this->addRelationMap('image','Image');
        
        
        // Options       
        $this->createOption('media.comments',true);
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
