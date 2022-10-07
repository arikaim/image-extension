'use strict';

arikaim.component.onLoaded(function() { 
    arikaim.ui.tab('.images-library-tab-item','images_library_content',['relation_id','relation_type','type']); 
    
    arikaim.events.on('image.upload',function(result) {   
        arikaim.events.emit('image.library.main.use',{
            image_id: result.id
        });
    });
});