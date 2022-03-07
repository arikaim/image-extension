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
    
        arikaim.component.loadLibrary('filepond:preview',function(result) {      
            $.fn.filepond.registerPlugin(FilePondPluginImagePreview);
            $.fn.filepond.setDefaults({
                allowImagePreview: true,
                imagePreviewHeight: 128
            }); 

            var fileUpload = new FileUpload('#upload_form',{
                url: '/api/image/upload',
                maxFiles: 1,
                allowMultiple: false,
                acceptedFileTypes: [],      
                formFields: {            
                    private_image: '#private_image',
                    target_path: '#target_path',
                    create_target_path: '#create_target_path',
                    deny_delete: '#deny_delete',
                    file_name: '#file_name',
                    relation_id: '#relation_id',
                    relation_type: '#relation_type',
                    resize_width: '#resize_width',
                    resize_height: '#resize_height'                             
                },
                onSuccess: function(result) { 
                    arikaim.events.emit('image.upload',result);   
                    callFunction(self.onSuccess,result);               
                }
            });     
            
            arikaim.ui.button('.upload-button',function(element) {               
                return $('#file').filepond('processFiles')
            });

        });
    };
};

var imageUpload = new ImageUpload();

arikaim.component.onLoaded(function() {
    imageUpload.init();
});