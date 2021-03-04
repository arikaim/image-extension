'use strict';

arikaim.component.onLoaded(function() {
    $('.image-dropdown').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/image/admin/list/{query}',   
            cache: false        
        },
        filterRemoteData: false         
    });
});