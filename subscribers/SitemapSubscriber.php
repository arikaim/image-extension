<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Subscribers;

use Arikaim\Core\Events\EventSubscriber;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Routes\Route;

/**
 * Sitemap subscriber class
 */
class SitemapSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {       
        $this->subscribe('sitemap.pages');
    }
    
    /**
     * Subscriber code executed.
     *
     * @param EventInterface $event
     * @return void
     */
    public function execute($event)
    {     
        $params = $event->getParameters();

        if ($params['page_name'] == 'arcade>category') {
            return $this->getCategoryPages($params);       
        }    
        if ($params['page_name'] == 'arcade>game') {
            return $this->getGamesPages($params);       
        }  
        if ($params['page_name'] == 'arcade>tag') {
            return $this->getTagsPages($params,100);       
        }  
        if ($params['page_name'] == 'arcade>type') {
            return $this->getGamesPerTypePages($params);       
        }  

        $url = Route::getRouteUrl($params['pattern']);

        return (empty($url) == false) ? [$url] : null;  
    }

    /**
     * Get category pages url
     *
     * @param array $route
     * @return array
     */
    public function getCategoryPages($route)
    {
        $pages = [];
        $category = Model::Category('category',function($model) {                
            return $model->getActive()->get();           
        });
        foreach ($category as $item) {
            $slug = $item->translation('en')->slug;
            $url = Route::getRouteUrl($route['pattern'],[
                'slug'     => $slug
            ]);
            $pages[] = $url;
        }     

        return $pages;
    }

    /**
     * Get games per type pages url   TODO
     *
     * @param array $route
     * @return array
     */
    public function getGamesPerTypePages($route)
    {
        $pages = [];
        $gamesType = Model::Games('arcade',function($model) {                
            return $model->getGamesType();          
        });
        foreach ($gamesType as $key => $value) {
            $url = Route::getRouteUrl($route['pattern'],[
                'type'     => $key               
            ]);
            $pages[] = $url;

           
        }     

        return $pages;
    }

    /**
     * Get game pages url
     *
     * @param array $route
     * @return array
     */
    public function getGamesPages($route)
    {
        $pages = [];
        $games = Model::Games('arcade')->getActive()->get();               
         
        foreach ($games as $item) {          
            $url = Route::getRouteUrl($route['pattern'],[
                'slug'     => $item->slug
            ]);
            $pages[] = $url;
        }      

        return $pages;
    }

     /**
     * Get tags pages url
     *
     * @param array $route
     * @param integer $maxItems
     * @return array
     */
    public function getTagsPages($route, $maxItems = null)
    {
        $pages = [];
        $tags = Model::Tags('tags',function($model) use($maxItems) {   
            $query = $model->orderBy('id');            
            return (empty($maxItems) == true) ? $query->get() : $query->take($maxItems)->get();           
        });         
               
        foreach ($tags as $item) {     
            $word = $item->translation('en')->word;     
            $url = Route::getRouteUrl($route['pattern'],[
                'tag'      => $word
            ]);
            $pages[] = $url;
        }      

        return $pages;
    }
}
