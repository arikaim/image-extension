/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ThumbnailsControlPanel() {
    this.create = function(formId,onSuccess,onError) {
        return arikaim.post('/api/admin/image/thumbnail/create',formId,onSuccess,onError);          
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/admin/image/thumbnail/' + uuid,onSuccess,onError);          
    };
};

var thumbnailsControlPanel = new ThumbnailsControlPanel();
