'use strict';

function ImagesLibrary() {
    var self = this;
   
    this.init = function() {
        paginator.init('image_rows',"image::admin.images.view.rows",'images'); 

        search.init({
            id: 'image_rows',
            component: 'image::admin.images.view.rows',
            event: 'image.search.load'
        },'images')  
        
        arikaim.events.on('image.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'imageSearch');

        this.loadMessages('image::admin.messages');
    };

    this.loadList = function() {        
        arikaim.page.loadContent({
            id: 'image_rows',         
            component: 'image::admin.images.view.rows'
        },function(result) {
            self.initRows();  
            paginator.reload(); 
        });
    };

    this.initRows = function() {
        $('.status-dropdown').dropdown({
            onChange: function(value) {               
                var uuid = $(this).attr('uuid');
                imageControlPanel.setStatus(uuid,value);
            }
        });    

        arikaim.ui.button('.image-relations-button',function(element) {
            var uuid = $(element).attr('uuid');
            arikaim.ui.setActiveTab('#image_relations','.image-tab-item');

            return arikaim.page.loadContent({
                id: 'image_content',
                component: 'image::admin.images.relations',
                params: { uuid: uuid }
            });     
        });

        arikaim.ui.button('.delete-button',function(element) {
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

        arikaim.ui.button('.thumbnails-button',function(element) {
            var uuid = $(element).attr('uuid');    
            arikaim.ui.setActiveTab('#thumbnails_image','.image-tab-item');
            
            arikaim.page.loadContent({
                id: 'image_content',
                component: 'image::admin.thumbnails',
                params: { uuid: uuid }
            });          
        });
    };
};

var imagesLibrary = createObject(ImagesLibrary,ControlPanelView);

arikaim.component.onLoaded(function() { 
    arikaim.ui.tab('.images-library-tab-item','images_library_content',['relation_id','relation_type']);  
});