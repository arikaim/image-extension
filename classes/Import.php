<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Classes;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Text;

/**
 * Import media 
 */
class Import
{
    /**
     * Import movie 
     *
     * @param object $driver
     * @param string $videoId
     * @param int $userId
     * @return Model|null
     */
    public static function importMovie($driver, $videoId, $userId)
    {
        if (empty($videoId) == true) {
            return false;
        }

        $media = Model::Media('media');   
        $mediaThumbnails = Model::MediaThumbnails('media');   

        $driverName = $driver->getDriverName();
        $videoResult = $driver->getService()->videos->listVideos('snippet,contentDetails',[
            'id' => $videoId
        ]);      
       
        $title = $videoResult->items[0]->snippet->title ?? '';
        $title = Text::cleanText($title);
        if ($media->hasMedia($title) == true || empty($title) == true) {        
            return false;
        }

        $description = $videoResult->items[0]->snippet->description ?? null;
        $thumbnails = $videoResult->items[0]->snippet->thumbnails ?? [];
        $tags = $videoResult->items[0]->snippet->tags ?? [];
        $duration = $videoResult->items[0]->contentDetails->duration ?? null;
        $categoryId = $videoResult->items[0]->snippet->categoryId ?? null;
        $category = null;

        // fetch category
        $categories = $driver->getService()->videoCategories->listVideoCategories('snippet',[
            'regionCode' => 'US'
        ]);

        foreach($categories->items as $item) {
            if ($item->id == $categoryId) {
                $category = $item->snippet->title;
            }              
        }
        
        $video = $media->create([
            'title'       => $title,
            'description' => Text::cleanText($description),
            'video_id'    => $videoId,
            'provider'    => $driverName,
            'duration'    => $duration,
            'user_id'     => $userId
        ]);
        
        if (\is_object($video) == false) {
            return null;
        }

        // tags
        $tagsItems = Model::Tags('tags',function($model) use($tags) {                    
            return $model->addTags($tags);
        });  

        // tags relations
        $tagsRelations = Model::TagsRelations('tags',function($model) use($tagsItems,$video) {    
            if (\is_array($tagsItems) == true) {
                $items = $model->saveRelations($tagsItems,'media',$video->id);            
                return $items; 
            }                        
            return [];   
        });     

        // add categories
        $categories = Model::Category('category',function($model) use($category) {
            return $model->createFromArray([$category],null,'en','media');
        });

        // category relations
        Model::CategoryRelations('category',function($model) use($categories,$video) {                            
            $items = $model->saveRelations($categories,'media',$video->id);
            return $items;       
        });  

        // create thumbnails
        foreach ($thumbnails as $key => $item) { 
            $thumbnail = $mediaThumbnails->create([
                'media_id' => $video->id,
                'key'      => $key,
                'url'      => $item->url,
                'width'    => $item->width, 
                'height'   => $item->height
            ]);
        }

        return $video;
    }
}
