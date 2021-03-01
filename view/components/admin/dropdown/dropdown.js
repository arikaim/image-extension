'use strict';

arikaim.component.onLoaded(function() {
    $('#media_dropdown').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/image/list/dropdown/{query}',   
            cache: false        
        },
        filterRemoteData: false         
    });
});