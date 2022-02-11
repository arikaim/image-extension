'use strict';

function ImagesLibrary() {
    var self = this;
   
    this.init = function() {
        paginator.init('image_library_rows',"image::admin.library.view.items",'images.library'); 

        this.loadMessages('image::admin.messages');
    };

    this.initRows = function() {
        arikaim.ui.button('.add-image-relation',function(element) {
            var relationType = $(element).attr('relation-type');
            var relationId = $(element).attr('relation-id');
            var imageId = $(element).attr('image-id');
           
            relations.add('ImageRelations','image',imageId,relationType,relationId,function(result) {            
                arikaim.ui.setActiveTab('#images_library_relations_tab','.images-library-tab-item');               
                return arikaim.page.loadContent({
                    id: 'images_library_content',
                    component: 'image::admin.library.relations',
                    params: { 
                        relation_id: relationId,
                        relation_type: relationType
                    }
                });  
            });            
        });
    };
};

var imagesLibrary = createObject(ImagesLibrary,ControlPanelView);

arikaim.component.onLoaded(function() { 
    arikaim.ui.tab('.images-library-tab-item','images_library_content',['relation_id','relation_type']);  
});