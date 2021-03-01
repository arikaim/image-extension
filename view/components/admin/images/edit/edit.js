'use strict';

arikaim.component.onLoaded(function() {
    $('#select_media').dropdown({
        apiSettings: {     
            on: 'now',      
            url: arikaim.getBaseUrl() + '/api/media/list/dropdown/{query}',   
            cache: false        
        },
        onChange: function(value, text, choice) {
            if (isEmpty(value) == true) {
                $('#media_edit_tabs').html('');
            } else {
                return arikaim.page.loadContent({
                    id: 'media_edit_tabs',
                    params: { uuid: value },
                    component: 'media::admin.media.edit.tabs'
                });
            }           
        },
        filterRemoteData: false         
    }); 
});