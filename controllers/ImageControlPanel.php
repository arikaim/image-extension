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
use Arikaim\Extensions\Image\Classes\ImageLibrary;
use Arikaim\Extensions\Image\Controllers\Traits\ImageUpload;
use Arikaim\Core\Controllers\Traits\Status;

/**
 * Image contorl panel api controller
*/
class ImageControlPanel extends ControlPanelApiController
{
    use    
        ImageUpload,
        Status;

    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('image::admin.messages');
        $this->setExtensionName('image');
        $this->setModelClass('Image');
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
        $data->validate(true);   

        $private = $data->getBool('private',false);      
        $url = $data->get('url',null);
        $thumbnailWidth = $data->get('thumbnail_width',null);
        $thumbnailHeight = $data->get('thumbnail_height',null);
        $relationId = $data->get('relation_id',null);
        $relationType = $data->get('relation_type',null);
        $fileName = $data->getString('file_name',null);
        $destinationPath = $data->get('target_path',ImageLibrary::getImagesPath(false));
        $denyDelete = $data->get('deny_delete',null);       
        $fileName = (empty($fileName) == true) ? Url::getUrlFileName($url) : $fileName . '-' . Url::getUrlFileName($url);
    
        // import from url and save
        $image = $this->get('image.library')->import($url,$destinationPath . $fileName,$this->getUserId(),[
            'private'     => $private,
            'deny_delete' => $denyDelete
        ]);     

        if ($image == null) {
            $this->error('errors.import','Error import image');
            return false;
        }

        if (empty($thumbnailWidth) == false && empty($thumbnailHeight) == false) {
            // create thumbnail
            $this->get('image.library')->createThumbnail($image,$thumbnailWidth,$thumbnailHeight);
        }
        
        if (empty($relationId) == false && empty($relationType) == false) {
            // add relation
            $this->get('image.library')->saveRelation($image,$relationId,$relationType);
        }

        // fire event 
        $this->get('event')->dispatch('image.import',\array_merge($image->toArray(),$data->toArray()));
                        
        $this
            ->message('import')
            ->field('uuid',$image->uuid)
            ->field('url',$url)
            ->field('relation_id',$relationId)
            ->field('relation_type',$relationType)
            ->field('file',$image->file_name);                                 
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
            if ($model == null) {
                $this->error('errors.id','Not valid image id');
                return false;
            } 

            if ($model->deny_delete == true) {
                $this->error("Can't delete, image is protected.");
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
            $model = $model->where('base_name','like','%' . $search . '%')->take($size)->get();
          
            $this->setResponse(($model != null),function() use($model,$dataField) {     
                $items = [];
                foreach ($model as $item) {
                    $thumbnail = $item->thumbnail(64,64);
                    $imageUrl = (\is_object($thumbnail) == true) ? $this->getPageUrl($thumbnail->src) : null;

                    $items[] = [
                        'name'  => $item['base_name'],
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

    /**
     * Generate QR code
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function generateQrCodeController($request, $response, $data)
    {
        $this->onDataValid(function($data) { 
            $qrCodeData = $data->get('data','Test');
            $image = $this->get('qrcode')->render($qrCodeData);

            $this->setResponse(!empty($image),function() use($image) {                  
                $this
                    ->message('qrcode')
                    ->field('image',$image);                          
            },'errors.qrcode');

        });
        $data->validate();
    }

    /**
     * Update image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function updateController($request, $response, $data)
    {
        $this->onDataValid(function($data) { 
            $uuid = $data->get('uuid',null);
            $model = Model::Image('image')->findById($uuid);
            if ($model == null) {
                $this->error("Not valid image id.");
                return false;
            }

            $result = $model->update($data->toArray());
            $this->setResponse(($result !== false),function() use($uuid) {                  
                $this
                    ->message('update')
                    ->field('uuid',$uuid);                          
            },'errors.update');
        });
        $data->validate();
    }
}
