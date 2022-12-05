<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Extensions\Image\Models\ImageCollectionItems;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Slug;
use Arikaim\Core\Db\Traits\DateCreated;
use Arikaim\Core\Db\Traits\UserRelation;

/**
 * ImageCollections class
 */
class ImageCollections extends Model  
{
    use 
        Uuid,
        Slug,
        DateCreated,
        UserRelation,
        Find;
       
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'image_collections';

    /**
     * Fillable columns
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'slug',
        'description',
        'user_id',
        'date_created',
        'title'       
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Add image to collection
     *
     * @param integer     $id
     * @param string|null $collectionId
     * @return boolean
     */
    public function addImage(int $id, ?string $collectionId = null): bool
    {
        $model = (empty($collectionId) == false) ? $this->findCollection($collectionId) : $this;

        $result = $model->items()->create([
            'image_id' => $id
        ]);

        return ($result !== false);
    }

    /**
     * Collection items relation
     *
     * @return Relation|null
     */
    public function items()
    {
        return $this->hasMany(ImageCollectionItems::class,'collection_id');
    }

    /**
     * Find collection scope query
     *
     * @param Builder      $query
     * @param string       $slug
     * @param integer|null $userId
     * @return Builder
     */
    public function scopeFindCollectionQuery($query, string $slug, ?int $userId = null)
    {
        $userId = (empty($userId) == true) ? $this->user_id : $userId;
        $query->where('slug','=',$slug);
      
        return (empty($userId) == false) ? $query->where('user_id','=',$userId) : $query;         
    }

    /**
     * Find collection
     *
     * @param string       $slug
     * @param integer|null $userId
     * @return object|null
     */
    public function findCollection(string $slug, ?int $userId = null): ?object
    {
        $model = $this->findCollectionQuery($slug,$userId)->first();

        return ($model != null) ? $model : $this->findById($slug);
    }

    /**
     * Save collection
     *
     * @param string       $title
     * @param string|null  $slug
     * @param integer|null $userId
     * @return Model|false
     */
    public function saveCollection(string $title, ?string $slug = null, ?int $userId = null)
    {
        $slug = (empty($slug) == true) ? $this->createSlug($title) : $slug;
        $model = $this->findCollection($slug,$userId);
        $data = [
            'title'   => $title,
            'slug'    => $slug,
            'user_id' => $userId
        ];

        if ($model == null) {
            $created = $this->create($data);
            return ($created != null) ? $created : false;
        }
        
        return ($this->update($data) !== false) ? $this : false;
    }

    /**
     * Delete collection
     *
     * @return boolean
     */
    public function deleteCollection(): bool
    {
        // delete items
        $this->items()->delete();
        // delete
        return ($this->delete() !== false);
    }

}
