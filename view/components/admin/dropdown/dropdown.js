'use strict';

$(document).ready(function() {  
    $('#media_dropdown').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/media/list/dropdown/{query}',   
            cache: false        
        },
        filterRemoteData: false         
    });
});