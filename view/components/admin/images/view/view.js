/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

class ImagesView extends View {
   
    self = this;

    init() {

        $('.users-dropdown').on('change',function() {
            var selected = $(this).val();
                    
            search.setSearch({
                search: {
                    user_id: selected,                       
                }          
            },'images',function(result) {                  
                self.loadList();
            });    
        });
        
        search.init({
            id: 'image_rows',
            component: 'image::admin.images.view.rows',
            event: 'image.search.load'
        },'images');
        
        arikaim.events.on('image.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'imageSearch');

        this.loadMessages('image::admin.messages');
        this.initRows();  
    };

    loadList() {        
        arikaim.page.loadContent({
            id: 'image_rows',         
            component: 'image::admin.images.view.rows'
        },(result) => {
            this.initRows();  
            //paginator.reload(); 
        });
    };

    initRows() {
        arikaim.ui.loadComponentButton('.image-action');

        $('.status-dropdown').on('change', function() {
            var val = $(this).val();      
            var uuid = $(this).attr('uuid');

            imageControlPanel.setStatus(uuid,val);          
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
    };
};

var imagesView = new ImagesView();

arikaim.component.onLoaded(function() {
    imagesView.init();
}); 