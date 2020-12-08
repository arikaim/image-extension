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
use Arikaim\Core\Controllers\Traits\SoftDelete;

/**
 * Image api controller
*/
class ImageApi extends ApiController
{
    use 
        FileDownload,
        SoftDelete;
            
    /**
     * Constructor
     * 
     * @param Container
     */
    public function __construct($container = null) 
    {
        parent::__construct($container);
        $this->setModelClass('Image');
        $this->setExtensionName('image');
    }

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('media>messages');
    }

    /**
     * View thumbnail
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function viewThumbnailFile($request, $response, $data) 
    {  
    }

    /**
     * Create thumbnail
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function createThumbnailController($request, $response, $data) 
    {  
    }

    /**
     * View media file
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function view($request, $response, $data) 
    {            
        $slug = $data->get('slug',null);
        $model = Model::Media('media')->findBySlug($slug);
     
        // not valid slug
        if (\is_object($model) == false) {
            $this->error('Not valid slug');
            return false;
        }

        $userDetails = Model::UserDetails('users')->findOrCreate($model->user_id);      
        $userStoragePath = $userDetails->getUserStoragePath(true);

        $mediaFile = $model->getMediaFilePath($userStoragePath,true);
        
        return $this->downloadFile($response,$mediaFile);
    }  
}
