'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#upload_form");

    var fileUpload = new FileUpload('#upload_form',{
        url: '/api/media/admin/upload',
        maxFiles: 1,
        allowMultiple: false,
        acceptedFileTypes: [],
        formFields: {            
            uuid: '#uuid',          
            title: '#title'           
        },
        onSuccess: function(result) {
            return arikaim.page.loadContent({
                id: 'media_content',
                params: { uuid: result.uuid },
                component: 'media::admin.media.edit'
            });
        }
    });
});