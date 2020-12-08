'use strict';

$(document).ready(function() {   
    $('#choose_language').dropdown({
        onChange: function(value) {
            var uuid = $('#meta_content').attr('media-uuid');
            arikaim.page.loadContent({
                id: 'meta_content',
                component: 'media::admin.media.edit.meta.form',
                params: { 
                    uuid: uuid,
                    language: value 
                }
            });
        }
    });    
});