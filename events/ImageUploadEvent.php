<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Events;

use Arikaim\Core\Interfaces\Events\EventInterface;
use Arikaim\Core\Events\Event;

/**
 * Base event class
*/
class ImageUploadEvent extends Event implements EventInterface
{
    /**
     * Init event
     *
     * @return void
     */
    protected function init(): void
    {
        $this->setName('image.upload');
        $this->setTitle('Image upload');
        $this->setDescription('Trigger after image is uplaoded.');
    }

    /**
     * Init event descriptor properties 
     *
     * @return void
     */
    protected function initDescriptor(): void
    {
    }
}
