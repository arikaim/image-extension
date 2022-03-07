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
        $image = Model::Image('image')->findById($uuid);
     
        // not valid image uuid or id 
        if (\is_object($image) == false) {
            $this->error('Not valid image id.');
            return false;
        }
  
        return $this->viewImage($response,$image->src);
    } 
    
    /**
     * View protected image thumbnail
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function viewThumbnail($request, $response, $data) 
    {            
        $uuid = $data->get('uuid',null);
        $image = Model::ImageThumbnails('image')->findById($uuid);
     
        // not valid image uuid or id 
        if (\is_object($image) == false) {
            $this->error('Not valid image thumbnail id.');
            return false;
        }
  
        return $this->viewImage($response,$image->src);
    } 

    /**
     * Set image status
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function setStatus($request, $response, $data) 
    {   
        $uuid = $data->get('uuid',null);
        $status = $data->getInt('status',0);
        $image = Model::Image('image')->findById($uuid);
     
        // not valid image id
        if (\is_object($image) == false) {
            $this->error('Not valid image id.');
            return false;
        }

        // check for image user match to current logged user
        $this->requireUser($image->user_id);
       
        $result = $image->setStatus($status);

        $this->setResponse($result,function() use($image) {                  
            $this
                ->message('status')
                ->field('uuid',$image->uuid) 
                ->field('file_name',$image->file_name)  
                ->field('uuid',$status);                  
        },'errors.status');
    }

    /**
     * Delete image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function delete($request, $response, $data) 
    {   
        $uuid = $data->get('uuid',null);
        $image = Model::Image('image')->findById($uuid);
     
        // not valid image id
        if (\is_object($image) == false) {
            $this->error('Not valid image id.');
            return false;
        }

        // check for image user match to current logged user
        $this->requireUser($image->user_id);
      
        if ($image->deny_delete == true) {
            $this->error("Can't delete, image is protected.");
            return false;
        }

        $result = $image->deleteImage();

        $this->setResponse($result,function() use($image) {                  
            $this
                ->message('delete')
                ->field('uuid',$image->uuid) 
                ->field('file_name',$image->file_name);            
        },'errors.delete');
    }
}
