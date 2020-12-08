<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Subscribers;

use Arikaim\Core\Events\EventSubscriber;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;

/**
 * DasboardSubscriber class
 */
class DasboardSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {       
        $this->subscribe('dashboard.get.items');
    }
    
    /**
     * Subscriber code executed.
     *
     * @param EventInterface $event
     * @return void
     */
    public function execute($event)
    {     
        return 'media::admin.dashboard';
    }
}
