/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
*/
'use strict';

function ImageApi() {

    this.setStatus = function(uuid, status, onSuccess, onError) {           
        var data = { 
            uuid: uuid, 
            status: status 
        };

        return arikaim.put('/api/image/status',data,onSuccess,onError);      
    };

    this.delete = function(uuid, onSuccess, onError) {
        return arikaim.delete('/api/image/delete/' + uuid,onSuccess,onError);      
    };
}
 
var imageApi = new ImageApi();
