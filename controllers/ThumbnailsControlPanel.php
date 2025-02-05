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
        $this->setModelClass('ImageThumbnails');
        $this->setExtensionName('image');
    }

    /**
     * Create thumbnail image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Arikaim\Core\Validator\Validator $data
     * @return mixed
    */
    public function createController($request, $response, $data) 
    {          
        $data
            ->validate(true);   

        $uuid = $data->get('uuid');  
        $width = $data->get('width');
        $height = $data->get('height');         
        $aspectRatio = $data->getBool('aspect_ratio',false);      

        $thumbnail = $this->get('image.library')->createThumbnail($uuid,$width,$height,$aspectRatio);
        if ($thumbnail === false) {
            $this->error('errors.thumbnail.create');       
            return false;
        }

        $this->setResponse(($thumbnail != null),function() use($thumbnail) {                  
            $this
                ->message('thumbnail.create')
                ->field('uuid',$thumbnail->uuid)
                ->field('file_name',$thumbnail->file_name);                                                   
        },'errors.thumbnail.create');
    }

    /**
     * Delete thumbnail
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Arikaim\Core\Validator\Validator $data
     * @return mixed
    */
    public function deleteController($request, $response, $data)
    { 
        $data
            ->validate(true);

        $model = Model::ImageThumbnails('image')->findById($data['uuid']);  
        if ($model == null) {
            $this->error('errors.thumbnail.delete','Error delete thumbnail');
            return false;
        }

        $result = $model->deleteThumbnail();

        $this->setResponse($result,function() use($model) {                  
            $this
                ->message('thumbnail.delete')
                ->field('uuid',$model->uuid);                  
        },'errors.thumbnail.delete');
    }
}
