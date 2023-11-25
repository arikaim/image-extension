'use strict';

arikaim.component.onLoaded(function() { 
    var uuid = $('#category_image').attr('uuid');

    arikaim.events.on('image.library.main.use',function(result) {   
        category.update({
            image_id: result.image_id,
            uuid: uuid
        },function(result) {
            return arikaim.page.loadContent({
                id: 'main_image_content',           
                component: 'category::admin.edit.images.main.image',
                params: { 
                    uuid: uuid                   
                }
            });  
        });
    },'categoryMainImageUse');   
});