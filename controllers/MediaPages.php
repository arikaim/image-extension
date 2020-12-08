<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Controllers;

use Arikaim\Core\Controllers\Controller;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\Http\Request;

use Arikaim\Core\Controllers\Traits\FileDownload;

/**
 * Media pages controller
*/
class MediaPages extends Controller
{    
    use FileDownload;

    /**
     * Show view page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showMediaFilePage($request, $response, $data) 
    {       
        $language = $this->getPageLanguage($data);
        $slug = $data->get('slug',null);

        $model = Model::Media('media')->findBySlug($slug);
        if (\is_object($model) == false) {
            return false;
        }

        if (empty(Request::getBrowserName()) == false) {
            $model->increment('views');
        }
        $metaTags = $model->getMetaTags($language);
        $data['mobile'] = $request->getAttribute('mobile');
        $data['media'] = $model->toArray();
        $data['view_url'] = $model->getViewUrl();
        $data['media_categories'] = $model->categories()->get()->toArray();
   
        $this->get('page')->head()
            ->param('media',$model->title)
            ->param('media_title',$model->title)
            ->param('media_description',$model->description)     
            ->setMetaTags($metaTags)       
            ->keywords($model->title,"video",$model->getCategoriesList())            
            ->applyTwitterProperty('title',$model->title)   
            ->applyTwitterProperty('description',$model->description)   
            ->applyOgProperty('title',$model->title)   
            ->applyOgProperty('description',$model->description)                
            ->ogUrl($this->getUrl($request))
            ->ogImage($model->thumbnail)
            ->ogType('movie') 
            ->twitterSite($this->getUrl($request))  
            ->twitterImage($model->thumbnail);               
    }
    
    /**
     * Show by category
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showCategoryPage($request, $response, $data) 
    {       
        $language = $this->getPageLanguage($data);
        $model = Model::CategoryTranslations('category',function($model) use($data) {                
            return $model->findByColumn($data['slug'],'slug');         
        });

        if (\is_object($model) == false) {
            return false;
        }
        $data['mobile'] = $request->getAttribute('mobile');
        $data['category'] = $model->category;
        $categoryTitle = Arrays::toString($model->category->getTitle());      
        $metaTags = $model->getMetaTags($language,$model->where('slug','=',$data['slug']));
        
        $this->get('page')->head()
            ->param('category',$categoryTitle)      
            ->param('description',$model->description)    
            ->setMetaTags($metaTags)          
            ->ogUrl($this->getUrl($request))                           
            ->applyOgProperty('title',$categoryTitle)   
            ->applyOgProperty('description',$model->description)      
            ->applyTwitterProperty('title',$categoryTitle)   
            ->applyTwitterProperty('description',$model->description)            
            ->twitterCard('website')
            ->twitterSite($this->getUrl($request));     
    }   

    /**
     * Show home page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showHomePage($request, $response, $data) 
    {                  
    }  

    /**
     * Show popular games page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showPopularPage($request, $response, $data) 
    {       
        $data['mobile'] = $request->getAttribute('mobile');

        $this->get('page')->head()       
            ->ogUrl($this->getUrl($request))           
            ->twitterCard('website')   
            ->twitterSite($this->getUrl($request));             
    }   
    
    /**
     * Show featured games page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showFeaturedPage($request, $response, $data) 
    {       
        $data['mobile'] = $request->getAttribute('mobile');

        $this->get('page')->head()       
            ->ogUrl($this->getUrl($request))           
            ->twitterCard('website') 
            ->twitterSite($this->getUrl($request));             
    }   
    
    /**
     *  Show games by tag
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function showTagsPage($request, $response, $data) 
    {             
        $data['mobile'] = $request->getAttribute('mobile');

        $data['tags'] = Model::TagsTranslations('tags',function($model) use($data) {                
            return $model->findByColumn($data['tag'],'word');           
        });
      
        if (\is_object($data['tags']) == false) {
            return false;
        }
               
        $this->get('page')->head()
            ->param('tag',ucfirst($data['tag']))          
            ->ogUrl($this->getUrl($request))           
            ->twitterCard('website') 
            ->twitterSite($this->getUrl($request));                     
    }   
}
