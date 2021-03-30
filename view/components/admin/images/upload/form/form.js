/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ImageUpload() {
    var self = this;
    this.onSuccess = null;

    this.init = function() {
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
                url: '/api/admin/image/upload',
                maxFiles: 1,
                allowMultiple: false,
                acceptedFileTypes: [],      
                formFields: {            
                    private: '#private',
                    target_path: '#target_path',
                    file_name: '#file_name'                             
                },
                onSuccess: function(result) { 
                    callFunction(self.onSuccess,result);               
                }
            });        
        });
    };
};

var imageUpload = new ImageUpload();

arikaim.component.onLoaded(function() {
    imageUpload.init();
});