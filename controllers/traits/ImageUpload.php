<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Controllers\Traits;

use Arikaim\Extensions\Image\Classes\ImageLibrary;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Controllers\Traits\FileUpload;
use Arikaim\Core\Utils\Slug;

/**
 * Image upload trait
*/
trait ImageUpload
{
    use FileUpload;

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
            $fileName = $data->get('file_name',null);           
            $denyDelete = $data->getString('deny_delete',null);   
            $private = $data->getBool('private_image',false);                                     
            $destinationPath = $data->get('target_path',ImageLibrary::getImagesPath(false));
            $createDestinationPath = $data->getBool('create_target_path',false);
            $relationId = $data->get('relation_id',null);
            $relationType = $data->get('relation_type',null);
            $thumbnailWidth = $data->get('thumbnail_width',null);
            $thumbnailHeight = $data->get('thumbnail_height',null);
            $resizeWidth = $data->get('resize_width',null);
            $resizeHeight = $data->get('resize_height',null);
            $categoryId = $data->get('category_id',null);

            if (File::exists($destinationPath) == false && $createDestinationPath == true) {
                File::makeDir($destinationPath);
                File::setWritable($destinationPath);
            }
            
            if (File::exists($destinationPath) == false ) {
                $this->error('Target path not exists.');
                return false;
            };
            File::setWritable($destinationPath);

            $files = $this->uploadFiles($request,$destinationPath,false,true,$fileName);
           
            // process uploaded files        
            foreach ($files as $item) {               
                if (empty($item['error']) == false) continue;
               
                if (empty($resizeWidth) == false && empty($resizeHeight) == false) {
                    $image = $this->get('image.library')->resizeAndSave($destinationPath . $item['name'],$this->getUserId(),
                    $resizeWidth,
                    $resizeHeight,[
                        'private'     => $private,
                        'category_id' => (empty($categoryId) == true) ? null : $categoryId,
                        'deny_delete' => $denyDelete
                    ],$private);  
                } else {
                    $image = $this->get('image.library')->save($destinationPath . $item['name'],$this->getUserId(),[
                        'private'     => $private,
                        'category_id' => (empty($categoryId) == true) ? null : $categoryId,
                        'deny_delete' => $denyDelete
                    ],$private); 
                }                             
            }
        
            if (empty($relationId) == false && empty($relationType) == false) {
                // add relation
                $this->get('image.library')->saveRelation($image,$relationId,$relationType);
            }

            if (empty($thumbnailWidth) == false && empty($thumbnailHeight) == false) {
                // create thumbnail
                $this->get('image.library')->createThumbnail($image,$thumbnailWidth,$thumbnailHeight);
            }
            
            $this->setResponse(\is_object($image),function() use($image,$data, $private) {   
                // fire event 
                $params = \array_merge($image->toArray(),$data->toArray());
                $this->get('event')->dispatch('image.upload',$params);
                $this
                    ->message('upload')
                    ->field('uuid',$image->uuid)
                    ->field('id',$image->id)
                    ->field('image_src',$image->src)
                    ->field('private',$private)
                    ->field('file',$image->file_name);                                  
            },'errors.upload');   
        });
        $data->validate();   
    }
}
