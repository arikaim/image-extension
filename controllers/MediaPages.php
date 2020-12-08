<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Controllers;

use Arikaim\Core\Controllers\Controller;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Http\Request;

use Arikaim\Core\Controllers\Traits\FileDownload;

/**
 * Image pages controller
*/
class ImagePages extends Controller
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
}
