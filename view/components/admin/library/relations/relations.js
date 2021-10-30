'use strict';

function ImagesLibraryRelatins() {
    var self = this;
   
    this.init = function() {        
    };

    this.initRows = function() {  
        
        arikaim.ui.button('.image-details',function(element) {
            $('#image_details').fadeIn(500);
            var uuid = $(element).attr('uuid');

            arikaim.page.loadContent({
                id: 'image_details',
                component: 'image::admin.library.details',
                params: { uuid: uuid }
            });   
        });
       
        arikaim.ui.button('.delete-image-relation',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('remove.content'),{ title: title });

            modal.confirmDelete({ 
                title: self.getMessage('remove.title'),
                description: message
            },function() {
                imageControlPanel.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#' + uuid);     
                });
            });
        });       
    };
};

var imagesLibraryRelations = createObject(ImagesLibraryRelatins,ControlPanelView);

arikaim.component.onLoaded(function() { 
    arikaim.ui.tab('.images-library-tab-item','images_library_content',['relation_id','relation_type']);  
});