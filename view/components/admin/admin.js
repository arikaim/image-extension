/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ImageControlPanel() {
    
    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/image/admin/delete/' + uuid,onSuccess,onError);          
    };

    this.init = function() {    
        arikaim.ui.tab();
    };
};

var imageControlPanel = new ImageControlPanel();

arikaim.component.onLoaded(function() {
    imageControlPanel.init();
});