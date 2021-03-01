/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ImageControlPanel() {

    this.add = function(formId,onSuccess,onError) {
        return arikaim.post('/api/image/admin/add',formId,onSuccess,onError);          
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/image/admin/delete/' + uuid,onSuccess,onError);          
    };

    this.updateMetaTags= function(formId,onSuccess,onError) {
        return arikaim.put('/api/image/admin/update/meta',formId, onSuccess, onError);          
    };

    this.update = function(formId,onSuccess,onError) {
        return arikaim.put('/api/image/admin/update',formId, onSuccess, onError);          
    };

    this.setStatus = function(uuid, status, onSuccess, onError) {          
        var data = { 
            uuid: uuid,
            status: status 
        };

        return arikaim.put('/api/image/admin/status',data,onSuccess,onError);      
    };

    this.init = function() {    
        arikaim.ui.tab();
    };
};

var imageControlPanel = new ImageControlPanel();

arikaim.component.onLoaded(function() {
    imageControlPanel.init();
});