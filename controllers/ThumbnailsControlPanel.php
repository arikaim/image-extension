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

use Arikaim\Core\Controllers\ControlPanelApiController;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\File;

use Arikaim\Core\Controllers\Traits\FileUpload;

/**
 * Thumbnails contorl panel api controller
*/
class ThumbnailsControlPanel extends ControlPanelApiController
{
    use 
        FileUpload;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('media::admin.messages');
    }

    /**
     * Constructor
     * 
     * @param Container
     */
    public function __construct($container = null) 
    {
        parent::__construct($container);
        $this->setModelClass('MediaThumbnails');
        $this->setExtensionName('media');
    }

    /**
     * Create thumbnail image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function createController($request, $response, $data) 
    {          
        $this->onDataValid(function($data) { 
            $mediaUuid = $data->get('uuid');           
            $media = Model::Media('media')->findById($mediaUuid);   
            if (\is_object($media) == false) {
                $this->error('errors.id');
                return;
            }   

            $imageModule = $this->get('modules')->create('image');
            if (empty($imageModule) == true) {
                $this->error('errors.module');
                return;
            }   
            $width = $data->get('width');
            $height = $data->get('height');
            $thumbnail = $media->findOrCreateThumbnailModel($width,$height);
            $mediaFileName = $this->get('storage')->getFullPath($media->file_path);
            $thumbnailFileName = $this->get('storage')->getFullPath($thumbnail->file_path);

            $image = $imageModule->make($mediaFileName);
            $image->resize($width,$height);       
            $image->save($thumbnailFileName);

            $this->setResponse(File::exists($thumbnailFileName),function() use($data, $thumbnail) {                  
                $this
                    ->message('thumbnail.create')
                    ->field('uuid',$thumbnail->uuid)
                    ->field('file',$thumbnail->file);                                                   
            },'errors.thumbnail.create');
        });
        $data->validate();   
    }

    /**
     * Upload thumbnail file
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
            $model->setSlug($data['title']);
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
                $model = $model->findById($uuid);
                if (\is_object($model) == false) {
                    $this->error('errors.id');
                    return;
                }   
                $result = $model->update($data->toArray());
            } else {
                $data['user_id'] = $this->getUserId();              
                $result = $model->create($data->toArray());
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
     * Delete thumbnail
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
}
