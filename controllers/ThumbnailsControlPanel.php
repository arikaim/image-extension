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
use Arikaim\Extensions\Image\Classes\ImageLibrary;

/**
 * Thumbnails contorl panel api controller
*/
class ThumbnailsControlPanel extends ControlPanelApiController
{
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
        $this->setModelClass('ImageThumbnails');
        $this->setExtensionName('image');
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
            $uuid = $data->get('uuid');  
            $width = $data->get('width');
            $height = $data->get('height');         

            $thumbnail = $this->get('image.library')->createThumbnail($uuid,$width,$height);
            if (\is_null($thumbnail) == true) {
                $errors = $this->get('image.library')->getErrors();
                $this->addErrors($errors);
                return false;
            }
           
            $this->setResponse(\is_object($thumbnail),function() use($thumbnail) {                  
                $this
                    ->message('thumbnail.create')
                    ->field('uuid',$thumbnail->uuid)
                    ->field('file_name',$thumbnail->file_name);                                                   
            },'errors.thumbnail.create');
        });
        $data->validate();   
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
            $model = Model::ImageThumbnails('image')->findById($data['uuid']);  
            if (\is_object($model) == false) {
                $this->error('errors.thumbnail.delete');
                return false;
            }

            $result = $model->deleteThumbnail();

            $this->setResponse($result,function() use($model) {                  
                $this
                    ->message('thumbnail.delete')
                    ->field('uuid',$model->uuid);                  
            },'errors.thumbnail.delete');
        });       
        $data->validate();
    }
}
