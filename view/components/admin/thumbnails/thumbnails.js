/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ThumbnailsControlPanel() {
    this.create = function(formId,onSuccess,onError) {
        return arikaim.post('/api/image/admin/thumbnail/create',formId,onSuccess,onError);          
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/image/admin/thumbnail/' + uuid,onSuccess,onError);          
    };

    this.init = function() {
        $('.image-dropdown').on('change',function() {
            var selected = $('.image-dropdown').dropdown("get value");
            
            arikaim.page.loadContent({
                id: 'image_details_content',
                component: 'image::admin.thumbnails.details',             
                params: { uuid: selected }
            });  
        });
    }
};

var thumbnailsControlPanel = new ThumbnailsControlPanel();

arikaim.component.onLoaded(function() {
    thumbnailsControlPanel.init();
});