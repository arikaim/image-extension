<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Image\Jobs;

use Arikaim\Core\Queue\Jobs\Job;
use Arikaim\Core\Arikaim;

use Arikaim\Core\Interfaces\Job\JobInterface;

/**
 * Fetch image job
 */
class ImportImageJob extends Job implements JobInterface
{
    /**
     * Run job
     *
     * @return mixed
     */
    public function execute()
    {       
        $url = $this->params['url'] ?? null;
        $destination = $this->params['destination'] ?? null;
        if (empty($url) == true || empty($destination) == true) {
            return false;
        }

        return Arikaim::getService('image.library')->import($url,$destination);
    }    
}
