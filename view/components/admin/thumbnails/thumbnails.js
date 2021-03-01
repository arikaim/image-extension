/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ThumbnailsControlPanel() {

    this.create = function(formId,onSuccess,onError) {
        return arikaim.post('/api/media/admin/thumbnail/create',formId,onSuccess,onError);          
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/media/admin/thumbnail/' + uuid,onSuccess,onError);          
    };

    this.init = function() {    
        arikaim.ui.tab('.thumbnails-tab-item','thumbnails_content',['uuid']);
    };
};

var thumbnailsControlPanel = new ThumbnailsControlPanel();

arikaim.component.onLoaded(function() {
    thumbnailsControlPanel.init();
});