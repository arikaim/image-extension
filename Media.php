<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media;

use Arikaim\Core\Extension\Extension;
use Arikaim\Core\Db\Model;

/**
 * media extension
*/
class Media extends Extension
{
    /**
     * Install extension routes, events, jobs
     *
     * @return void
    */
    public function install()
    {
        // Pages
        // Home page
        $this->addHomePageRoute('/','MediaPages','showHome','media>home',null,'mediaHomePage',true);
        $this->addPageRoute('/video/{slug}','MediaPages','showMediaFile','media>view',null,'showMediaFile',true);
        $this->addPageRoute('/view/{slug}/videos','MediaPages','showCategory','media>category',null,'showCategory',true);
        $this->addPageRoute('/popular/videos','MediaPages','showPopular','media>popular',null,'popularVideos',true);
        $this->addPageRoute('/featured/videos','MediaPages','showFeatured','media>featured',null,'featuredVideos',true); 
        $this->addPageRoute('/tags/{tag}/videos','MediaPages','showTags','media>tag',null,'videosTags',true);
      
        // Api 
        $this->addApiRoute('GET','/api/media/view/{slug}','MediaApi','view',null);   
        $this->addApiRoute('GET','/api/media/list/dropdown/[{query}]','MediaApi','getDropdownList');   
        $this->addApiRoute('GET','/api/media/view/thumbnail/{slug}/{uuid}','MediaApi','viewThumbnail',null);  
        $this->addApiRoute('POST','/api/media/upload','MediaApi','upload');  
        $this->addApiRoute('PUT','/api/media/update','MediaApi','update');  
        $this->addApiRoute('PUT','/api/media/status','MediaApi','setStatus','session'); 
        $this->addApiRoute('DELETE','/api/media/delete/{uuid}','MediaApi','softDelete','session'); 
     
        // Control Panel
        $this->addApiRoute('GET','/api/media/admin/view/{uuid}','MediaControlPanel','view','session');  
        $this->addApiRoute('POST','/api/media/admin/upload','MediaControlPanel','upload','session');        
        $this->addApiRoute('PUT','/api/media/admin/update','MediaControlPanel','update','session');    
        $this->addApiRoute('DELETE','/api/media/admin/delete/{uuid}','MediaControlPanel','delete','session'); 
        $this->addApiRoute('PUT','/api/media/admin/status','MediaControlPanel','setStatus','session'); 
        $this->addApiRoute('PUT','/api/media/admin/featured','MediaControlPanel','setFeatured','session');            
        $this->addApiRoute('PUT','/api/media/admin/update/meta','MediaControlPanel','updateMetaTags','session');
        $this->addApiRoute('PUT','/api/media/admin/video/import','MediaControlPanel','importVideo','session');    
        // thumbnails
        $this->addApiRoute('POST','/api/media/admin/thumbnail/create','ThumbnailsControlPanel','create','session');        
        $this->addApiRoute('POST','/api/media/admin/thumbnail/upload','ThumbnailsControlPanel','upload','session');     
        $this->addApiRoute('DELETE','/api/media/admin/thumbnail/{uuid}','ThumbnailsControlPanel','delete','session'); 
     
        // Create db tables
        $this->createDbTable('MediaSchema');    
        $this->createDbTable('MediaTranslationsSchema');    
        $this->createDbTable('MediaThumbnailsSchema');         
        // Relation map 
        $this->addRelationMap('media','Media');
        
        // Options 
        $this->createOption('media.items.per.page',50);
        $this->createOption('media.job.feeds.driver','youtube-api');
       
        $this->createOption('media.comments',true);
    }       

    /**
     * Post install actions
     *
     * @return void
     */
    public function postInstall()
    {
        // Create categories
        $items = Extension::loadJsonConfigFile('media-categories.json','media');

        Model::Category('category',function($model) use($items) {
            return $model->createFromArray($items,null,'en','media');
        }); 

        // Create ads left and  right panels
        Model::Ads('ads',function($model) {
            $model->createAd('Left Panel','','Left ad panel');
            $model->createAd('Right Panel','','Right ad panel');
            $model->createAd('Play Panel','','Play page ad panel');           
        });
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
