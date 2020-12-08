/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MediaImportView() {
    var self = this;

    this.init = function() {
        var driverName = $('#drivers_dropdown').dropdown('get value');
    
        $('#drivers_dropdown').dropdown({
            onChange: function(value) {             
                paginator.setPage(1,'media-feeds',function(result) {                
                });
                arikaim.page.loadContent({
                    id: 'feed_rows',
                    component: "media::admin.import.view.rows",
                    params: { driver_name: value }
                });    
            }
        });

        search.init({
            id: 'feed_rows',
            component: 'media::admin.import.view.rows',
            params: { driver_name: driverName },
            event: 'media.import.load'
        },'media.import')  
        
        arikaim.events.on('media.import.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'mediaSearch');   

        googleApiPaginator.init('feed_rows', { 
            name: 'media::admin.import.view.rows',
            params: { driver_name: driverName } 
        },'media-feeds'); 
    };

    this.initRows = function() {
        arikaim.ui.button('.install-button',function(element) {
            var driver = $(element).attr('driver-name');
            var videoId = $(element).attr('video-id');           
            var itemIndex = $(element).attr('index');
    
            return mediaFeeds.import(driver,videoId,function(result) {
                arikaim.page.toastMessage(result.message);                
                $('#install_icon_' + itemIndex).removeClass('hidden');
                $(element).hide();
            },function(error) {
                arikaim.page.toastMessage({
                    message: error,
                    class: 'error'
                });        
            });
        });    
    };
}

var mediaImportView = new MediaImportView();

$(document).ready(function() {
    mediaImportView.init();
});