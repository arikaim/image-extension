<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Media\Jobs;

use Arikaim\Core\Queue\Jobs\CronJob;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

use Arikaim\Core\Interfaces\Job\RecuringJobInterface;
use Arikaim\Core\Interfaces\Job\JobInterface;
use Arikaim\Core\Interfaces\Job\JobOutputInterface;
use Arikaim\Core\Interfaces\Job\JobLogInterface;

use Arikaim\Core\Queue\Traits\JobOutput;
use Arikaim\Core\Queue\Traits\JobLog;
use Exception;

/**
 * Import media cron job
 */
class ImportMediaJob extends CronJob implements RecuringJobInterface, JobInterface, JobOutputInterface, JobLogInterface
{
    use 
        JobOutput,
        JobLog;

    /**
     * Job output
     *
     * @var array
     */
    protected $output = [];

    /**
     * Log message
     *
     * @var string
     */
    protected $logMessage;

    /**
     * Constructor
     *
     * @param string|null $extension
     * @param string|null $name
     * @param integer $priority
     */
    public function __construct($extension = null, $name = null, $priority = 0)
    {
        parent::__construct($extension,$name,$priority);
        
        $this->runEveryMinute(5);
    }

    /**
     * Run job
     *
     * @return integer
     */
    public function execute()
    {
        $installed = 0;
        $maxGameInstall = (int)Arikaim::options()->get('arcade.job.max.install',5);        
        $fromPage = (int)Arikaim::options()->get('arcade.job.feeds.from.page',1);
        $toPage = (int)Arikaim::options()->get('arcade.job.feeds.to.page',5);
        $feed = Arikaim::options()->get('arcade.job.feeds.driver');

        $this->addOutput('Form Page',$fromPage,'from_page');
        $this->addOutput('To Page',$toPage,'to_page');
        $this->addOutput('Feed driver',$feed,'feed_driver');

        for ($page = $fromPage; $page < $toPage; $page++) {
            try {
                $installed += $this->installGames($feed,$page,$maxGameInstall);         
            } catch (Exception $th) {               
            }
            
            if ($installed > $maxGameInstall) {
                break;
            }
        }

        $this->addOutput('Games installed',$installed,'installed');
        $this->setLogMessage("Installed $installed games from feeds '$feed' ");
    
        return $installed;     
    }

    /**
     * Install games
     *
     * @param string $feed_name
     * @param integer $page
     * @param integer $maxGames
     * @return integer
     */
    public function installGames($feedName, $page, $maxGames)
    {
        $driver = Arikaim::driver()->create($feedName);
        $model = Model::Games('arcade');

        $driver->fetch($page);
        $items = $driver->getItems();
        $currentFeeditem = Arikaim::options()->get('arcade.current.feed.item',0);

        if (isset($items[$currentFeeditem]) == false) {
            // reset current feed item
            Arikaim::options()->set('arcade.current.feed.item',0);
            $currentFeeditem = 0;
        }

        $lastFeedItem = $currentFeeditem + $maxGames;
        $installed = 0;

        for ($index = $currentFeeditem; $index < $lastFeedItem; $index++) { 
            if (isset($items[$index]) == false) { 
                continue;
            }
            $item = $items[$index];
            $result = $model->hasGame($item['title']);
    
            if ($result == false) {
                $item['feed'] = $driver->getDriverName();
                $game = $model->saveGame($item);               
                $installed += (\is_object($game) == true) ? 1 : 0;
            }           
            if ($installed > $maxGames) {
                break;
            }          
        }
        Arikaim::options()->set('arcade.current.feed.item',$lastFeedItem);

        return $installed;
    }
}
