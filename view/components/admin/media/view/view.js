/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function MediaView() {
    var self = this;
    this.messages = null;

    this.init = function() {
        order.init('media_rows','media::admin.media.view.rows','media');
    
        paginator.init('media_rows',"media::admin.media.view.rows",'media'); 

        search.init({
            id: 'media_rows',
            component: 'media::admin.media.view.rows',
            event: 'media.search.load'
        },'media')  
        
        $('.status-filter').dropdown({          
            onChange: function(value) {      
                var searchData = {
                    search: {
                        status: value,                       
                    }          
                }              
                search.setSearch(searchData,'media',function(result) {                  
                    self.loadList();
                });               
            }
        });

        arikaim.events.on('media.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'mediaSearch');

        this.loadMessages();
    };

    this.loadMessages = function() {
        if (isObject(this.messages) == true) {
            return;
        }

        arikaim.component.loadProperties('media::admin.messages',function(params) { 
            self.messages = params.messages;
        }); 
    };

    this.loadList = function() {        
        arikaim.page.loadContent({
            id: 'media_rows',         
            component: 'media::admin.media.view.rows'
        },function(result) {
            self.initRows();  
            paginator.reload(); 
        });
    };

    this.initRows = function() {
       
        $('.status-dropdown').dropdown({
            onChange: function(value) {               
                var uuid = $(this).attr('uuid');
                media.setStatus(uuid,value);
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
                media.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#' + uuid);     
                });
            });
        });

        arikaim.ui.button('.featured-button',function(element) {
            var uuid = $(element).attr('uuid');
        
            media.setFeatured(uuid,'toggle',function(result) {
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
                component: 'media::admin.media.edit',
                params: { uuid: uuid }
            });          
        });
    };
};

var mediaView = new MediaView();

$(document).ready(function() {  
    mediaView.init();
    mediaView.initRows();  
}); 