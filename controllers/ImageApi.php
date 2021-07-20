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

use Arikaim\Extensions\Image\Controllers\Traits\ImageUpload;

/**
 * Image api controller
*/
class ImageApi extends ApiController
{
    use       
        ImageUpload;

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
        $uuid = $data->get('uuid',null);
        $image = Model::Media('media')->findById($uuid);
     
        // not valid slug
        if (\is_object($image) == false) {
            $this->error('Not valid image id.');
            return false;
        }
  
        return $this->viewImage($response,$image->src);
    }  
}
