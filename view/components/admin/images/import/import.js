'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.load-image',function(element) {
        var url = $('#url').val().trim();
        if (isEmpty(url) == true) {
            return true;
        }
   
        arikaim.ui.loadImage(url,function(image) {
            arikaim.page.loadContent({
                id: 'import_image_content',
                component: 'image::admin.images.import.form',
                params: { url: url }
            });    
        },function(image) {
            console.log('err');
        });
    });
});