/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MediaControlPanel() {

    this.add = function(formId,onSuccess,onError) {
        return arikaim.post('/api/media/admin/add',formId,onSuccess,onError);          
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/media/admin/delete/' + uuid,onSuccess,onError);          
    };

    this.updateMetaTags= function(formId,onSuccess,onError) {
        return arikaim.put('/api/media/admin/update/meta',formId, onSuccess, onError);          
    };

    this.update = function(formId,onSuccess,onError) {
        return arikaim.put('/api/media/admin/update',formId, onSuccess, onError);          
    };

    this.setStatus = function(uuid, status, onSuccess, onError) {          
        var data = { 
            uuid: uuid,
            status: status 
        };

        return arikaim.put('/api/media/admin/status',data,onSuccess,onError);      
    };

    this.setFeatured = function(uuid, featured, onSuccess, onError) {   
        var data = { 
            uuid: uuid,
            featured: featured 
        };
        
        return arikaim.put('/api/media/admin/featured',data,onSuccess,onError);      
    };

    this.init = function() {    
        arikaim.ui.tab();
    };
};

var media = new MediaControlPanel();

$(document).ready(function() {
    media.init();
});