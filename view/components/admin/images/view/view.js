/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ImagesView() {
    var self = this;
    this.messages = null;

    this.init = function() {
        paginator.init('images_rows',"image::admin.images.view.rows",'images'); 

        search.init({
            id: 'images_rows',
            component: 'image::admin.media.view.rows',
            event: 'image.search.load'
        },'image')  
        
        $('.status-filter').dropdown({          
            onChange: function(value) {      
                var searchData = {
                    search: {
                        status: value,                       
                    }          
                }              
                search.setSearch(searchData,'images',function(result) {                  
                    self.loadList();
                });               
            }
        });

        arikaim.events.on('image.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'mediaSearch');

        this.loadMessages();
    };

    this.loadMessages = function() {      
        if (isObject(this.messages) == false) {
            arikaim.component.loadProperties('image::admin.messages',function(params) { 
                self.messages = params.messages;
            }); 
        }
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
                imagesView.setStatus(uuid,value);
            }
        });    

        arikaim.ui.button('.restore-button',function(element) {
            var uuid = $(element).attr('uuid');
        });

        arikaim.ui.button('.delete-button',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');

            var message = arikaim.ui.template.render(self.messages.remove.content,{ title: title });
            modal.confirmDelete({ 
                title: self.messages.remove.title,
                description: message
            },function() {
                imagesView.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#' + uuid);     
                });
            });
        });

        arikaim.ui.button('.featured-button',function(element) {
            var uuid = $(element).attr('uuid');
        
            imagesView.setFeatured(uuid,'toggle',function(result) {
                if (result.featured == 1 || result.featured == '1') {    
                    $(element).removeClass('olive');                                         
                } else {                
                    $(element).addClass('olive');       
                }
            });
        });

        arikaim.ui.button('.edit-button',function(element) {
            var uuid = $(element).attr('uuid');    
            arikaim.ui.setActiveTab('#edit_media','.media-tab-item');
            arikaim.page.loadContent({
                id: 'media_content',
                component: 'image::admin.media.edit',
                params: { uuid: uuid }
            });          
        });
    };
};

var imagesView = new ImagesView();

arikaim.component.onLoaded(function() {
    imagesView.init();
    imagesView.initRows();  
}); 