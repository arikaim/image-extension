'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#upload_form");
    $('.private-image').checkbox({
        onChecked: function() {
            $(this).val(1);
        },
        onUnchecked: function() {
            $(this).val(false);
        }
    });

    var checked = $('.private-image').checkbox('is checked');
    $('#private').val(checked);

    arikaim.component.loadLibrary('filepond:preview',function(result) {
    
        $.fn.filepond.registerPlugin(FilePondPluginImagePreview);
        $.fn.filepond.setDefaults({
            allowImagePreview: true,
            imagePreviewHeight: 128
        });

        var fileUpload = new FileUpload('#upload_form',{
            url: '/api/image/admin/upload',
            maxFiles: 1,
            allowMultiple: false,
            acceptedFileTypes: [],      
            formFields: {            
                private: '#private'                            
            },
            onSuccess: function(result) {      
                return arikaim.page.loadContent({
                    id: 'image_content',
                    params: { uuid: result.uuid },
                    component: 'image::admin.images.view'
                });
            }
        });
    });
});