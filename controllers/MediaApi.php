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

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Db\Model;

use Arikaim\Core\Controllers\Traits\FileDownload;
use Arikaim\Core\Controllers\Traits\FileUpload;
use Arikaim\Core\Controllers\Traits\SoftDelete;

/**
 * Media api controller
*/
class MediaApi extends ApiController
{
    use 
        FileDownload,
        SoftDelete,
        FileUpload;
    
    /**
     * Constructor
     * 
     * @param Container
     */
    public function __construct($container = null) 
    {
        parent::__construct($container);
        $this->setModelClass('Media');
        $this->setExtensionName('media');
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
            $title = $data->get('title');       
            $description = $data->get('description');  
            $category = $data->get('category',null);        
            $userId = $this->getUserId();

            $userDetails = Model::UserDetails('users')->findOrCreate($userId);      
            $userStoragePath = $userDetails->getUserStoragePath(true);

            $model = Model::Media('media');                      
            if ($model->hasMedia($data['title']) == true) {
                $this->error("errros.exists");
                return;
            }
           
            if ($model->createUploadPath($userStoragePath) === false) {
                $this->error("errros.path");
                return;
            }
            $destinationPath = $model->getMediaFilesPath($userStoragePath,true);
 
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
           
            // create
            $data['user_id'] = $userId;
            $data['status'] = 4; // Pending  

            $media = $model->create($data->toArray());
            $result = \is_object($media);
            
            if (empty($category) == false) {
                // save catgory relation              
                $relations = Model::CategoryRelations('category');
                $relations->saveRelation($category,'media',$media->id);               
            }

            $this->setResponse($result,function() use($model,$data) {                  
                $this
                    ->message('upload')
                    ->field('uuid',$model->uuid)
                    ->field('file',$data['file']);                                  
            },'errors.upload');   

        });
        $data
            ->addRule('text:min=2','title')
            ->validate();   
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

    /**
     * Get games dropdown list items
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function getDropdownList($request, $response, $data) 
    {         
        $this->onDataValid(function($data) {   
            $search = $data->get('query','');
            $size = $data->get('size',15);

            $query = Model::Media('media');
            $model = $query->where('title','like','%' . $search . '%')->take($size)->get();

            $this->setResponse(\is_object($model),function() use($model) {     
                $items = [];
                foreach ($model as $item) {
                    $items[]= [
                        'name' => $item['title'],
                        'value' => $item['uuid']
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
