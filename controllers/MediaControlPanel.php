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

use Arikaim\Core\Controllers\ControlPanelApiController;
use Arikaim\Core\Db\Model;
use Arikaim\Extensions\Media\Classes\Import;
use Arikaim\Core\Controllers\Traits\Status;
use Arikaim\Core\Controllers\Traits\FileUpload;

/**
 * Image contorl panel api controller
*/
class ImageControlPanel extends ControlPanelApiController
{
    use 
        Status,
        FileUpload;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('image::admin.messages');
    }

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
     * Import video file
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function importVideoController($request, $response, $data) 
    {          
        $this->onDataValid(function($data) use ($request) { 
            $videoId = $data->get('video_id');
            $driverName = $data->get('driver_name');
           
            $driver = $this->get('driver')->create($driverName);
            $video = Import::importMovie($driver,$videoId,$this->getUserId());

            $this->setResponse(\is_object($video),function() use($video) {                  
                $this
                    ->message('import')
                    ->field('uuid',$video->uuid);                  
            },'errors.import');
        });
        $data
            ->addRule('text:min=2','video_id')
            ->validate();   
    }

    /**
     * Upload media file
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function uploadController($request, $response, $data) 
    {          
        $this->onDataValid(function($data) use ($request) { 
            $uuid = $data->get('uuid');          
            $model = Model::Media('media');                      
            if ($model->hasMedia($data['title']) == true) {
                $this->error("errros.exists");
                return;
            }
           
            $model->initUuid();
            if (empty($uuid) == false) {
                $model = $model->findById($uuid);
                if (\is_object($model) == false) {
                    $this->error('errors.id');
                    return;
                }  
            }
            if ($model->createUploadPath() === false) {
                $this->error("errros.path");
                return;
            }
            $destinationPath = $model->getMediaFilesPath(true);
 
            $files = $this->uploadFiles($request,$destinationPath);
            // process uploaded files
            foreach ($files as $item) {               
                if (empty($item['error']) == false) continue;

                $data['file'] = $item['name'];
                $fileName = $destinationPath . $data['file'];
                $data['file_size'] = $this->get('storage')->getSize($fileName);
                $data['mime_type'] = $this->get('storage')->getMimetype($fileName);                  
            }
          
            if (empty($data['file']) == true) {
                $this->error('errors.upload');
                return;
            }  
           
            if (empty($uuid) == false) {           
                // update  
                $result = $model->update($data->toArray());
            } else {
                // create
                $data['user_id'] = $this->getUserId();
                $data['uuid'] = $model->uuid;              
                $model = $model->create($data->toArray());
                $result = \is_object($model);
                $uuid = $model->uuid;
            }
            
            $this->setResponse($result,function() use($data,$uuid) {                  
                $this
                    ->message('upload')
                    ->field('uuid',$uuid)
                    ->field('file',$data['file']);                                  
            },'errors.upload');   

        });
        $data
            ->addRule('text:min=2','title')
            ->validate();   
    }

    /**
     * Update media
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updateController($request, $response, $data) 
    {    
        $this->onDataValid(function($data) { 
            $model = Model::Media('media')->findById($data['uuid']);
            $data['featured'] = $data->get('featured',0);
            $result = ($model->hasMedia($data['title'],$data['uuid']) == true) ? false : $model->update($data->toArray());
        
            $this->setResponse($result,function() use($model) {                  
                $this
                    ->message('update')
                    ->field('uuid',$model->uuid);                  
            },'errors.add');
        });        
        $data
            ->addRule('text:min=2','title')
            ->validate();   
    }

    /**
     * Delete media
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deleteController($request, $response, $data)
    { 
        $this->onDataValid(function($data) { 
            $model = Model::Media('media')->findById($data['uuid']);  
            $result = $model->deleteMedia();

            $this->setResponse($result,function() use($model) {                  
                $this
                    ->message('delete')
                    ->field('uuid',$model->uuid);                  
            },'errors.delete');
        });       
        $data->validate();
    }

    /**
     * Set media featured
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function setFeaturedController($request, $response, $data)
    {
        $this->onDataValid(function($data) { 
            $model = Model::Media('media')->findById($data['uuid']);  
            $featured = $data->get('featured','toggle');
            $result = ($featured == 'toggle') ? $model->toggle('featured') : $model->update(['featured' => $featured]);           
             
            $this->setResponse($result,function() use($model) {                  
                $this
                    ->message('featured')
                    ->field('uuid',$model->uuid)
                    ->field('featured',$model->featured);     
            },'errors.featured');
        });       
        $data->validate();
    }
}
