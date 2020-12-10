'use strict';

$(document).ready(function() {
    var fileUpload = new FileUpload('#game_images_form',{
        url: '/api/arcade/admin/upload/file',
        maxFiles: 1,
        allowMultiple: false,
        acceptedFileTypes: ['image/png', 'image/jpeg', 'image/gif'],
        formFields: {            
            uuid: '#uuid',
            type: '#file_type'
        },
        onSuccess: function(result) {
            return arikaim.page.loadContent({
                id: 'game_images_list',
                params: { url: result.thumbnail_url },
                component: 'arcade::admin.games.edit.images.thumbnail'
            });
        }
    });
});