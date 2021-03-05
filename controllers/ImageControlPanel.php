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
use Arikaim\Core\Http\Url;
use Arikaim\Core\Controllers\Traits\FileUpload;

/**
 * Image contorl panel api controller
*/
class ImageControlPanel extends ControlPanelApiController
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
        $this->loadMessages('image::admin.messages');
    }

    /**
     * Import image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function importController($request, $response, $data) 
    {          
        $this->onDataValid(function($data) {
            $private = $data->getBool('private',false);      
            $url = $data->get('url',null);
            $fileName = $data->getString('file_name',null);

            $model = Model::Image('image');     
            if ($private == true) {                
                $model->createPrivateStoragePath($this->getUserId());
            }

            $fileName = (empty($fileName) == true) ? Url::getUrlFileName($url) : $fileName;
        
            // import from url and save
            $image = $this->get('image.library')->import($url,$fileName,$this->getUserId(),$private);     

            $this->setResponse(\is_object($image),function() use($image) {                  
                $this
                    ->message('import')
                    ->field('uuid',$image->uuid)
                    ->field('file',$image->file_name);                                  
            },'errors.import');   

        });
        $data->validate();   
    }

    /**
     * Upload image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function uploadController($request, $response, $data) 
    {          
        $this->onDataValid(function($data) use ($request) { 
            $private = $data->getBool('private',false);          
            $model = Model::Image('image');                      
            if ($private == true) {                
                $model->createPrivateStoragePath($this->getUserId());
            }
            
            $destinationPath = $model->getStoragePath(true,$this->getUserId(),$private);
            $files = $this->uploadFiles($request,$destinationPath);

            // process uploaded files        
            foreach ($files as $item) {               
                if (empty($item['error']) == false) continue;

                $image = $this->get('image.library')->save($item['name'],$this->getUserId(),$private);               
            }
        
            $this->setResponse(\is_object($image),function() use($image) {                  
                $this
                    ->message('upload')
                    ->field('uuid',$image->uuid)
                    ->field('file',$image->file_name);                                  
            },'errors.upload');   
        });
        $data->validate();   
    }

    /**
     * Delete image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function deleteController($request, $response, $data)
    { 
        $this->onDataValid(function($data) { 
            $model = Model::Image('image')->findById($data['uuid']); 
            if (\is_object($model) == false) {
                $this->error('errors.id');
                return false;
            } 

            $result = $model->deleteImage();

            $this->setResponse($result,function() use($model) {                  
                $this
                    ->message('delete')
                    ->field('uuid',$model->uuid);                  
            },'errors.delete');
        });       
        $data->validate();
    }

    /**
     * Get images list
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function getList($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {          
            $search = $data->get('query','');
            $dataField = $data->get('data_field','uuid');
            $size = $data->get('size',15);
            
            $model = Model::Image('image');
            $model = $model->where('file_name','like','%' . $search . '%')->take($size)->get();
          
            $this->setResponse(\is_object($model),function() use($model,$dataField) {     
                $items = [];
                foreach ($model as $item) {
                    $thumbnail = $item->thumbnail(64,64);
                    $imageUrl = (\is_object($thumbnail) == true) ? $this->getPageUrl($thumbnail->src) : null;

                    $items[] = [
                        'name'  => $item['file_name'],
                        'image' => $imageUrl,
                        'value' => $item[$dataField]
                    ];
                }
                $this                    
                    ->field('success',true)
                    ->field('results',$items);  
            },'errors.list');
        });
        $data->validate();

        return $this->getResponse(true); 
    }
}
