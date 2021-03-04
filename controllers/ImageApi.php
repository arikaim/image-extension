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

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Db\Model;

use Arikaim\Core\Controllers\Traits\FileDownload;

/**
 * Image api controller
*/
class ImageApi extends ApiController
{
    use 
        FileDownload;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('image::messages');
    }

    /**
     * View protected image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function view($request, $response, $data) 
    {            
        $slug = $data->get('slug',null);
        $image = Model::Media('media')->findBySlug($slug);
     
        // not valid slug
        if (\is_object($image) == false) {
            $this->error('Not valid image slug');
            return false;
        }
  
        return $this->viewImage($response,$image->src);
    }  
}
