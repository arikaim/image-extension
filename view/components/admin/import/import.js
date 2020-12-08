/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MediaFeedsControlPanel() {
    var self = this;

    this.import = function(driverName, videoId, onSuccess, onError) {
        var data = { 
            driver_name: driverName, 
            video_id: videoId           
        };

        return arikaim.put('/api/media/admin/video/import',data,onSuccess,onError); 
    };

    this.init = function() {    
        arikaim.ui.tab('.import-tab-item','import_content',['category','empty']);
        
        arikaim.events.on('driver.config',function(element,name,category) {  
            arikaim.ui.setActiveTab('#drivers_config','.import-tab-item');
            drivers.loadConfig(name,'import_content');
        },'driversList',self);
    };
};

var mediaFeeds = new MediaFeedsControlPanel();

$(document).ready(function() {  
    mediaFeeds.init();
});