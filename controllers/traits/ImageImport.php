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

use Arikaim\Core\Http\Url;
use Arikaim\Extensions\Image\Classes\ImageLibrary;

/**
 * Image import from url trait
*/
trait ImageImport
{
    /**
     * Import image
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function import($request, $response, $data) 
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
}