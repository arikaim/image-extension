/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ThumbnailsView() {
    var self = this;
   
    this.init = function() {
        this.loadMessages('image::admin.messages');
    };

    this.initRows = function() {
        arikaim.ui.button('.delete-thumbnail',function(element) {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(self.getMessage('thumbnail_remove.content'),{ title: title });

            modal.confirmDelete({ 
                title: self.getMessage('thumbnail_remove.title'),
                description: message
            },function() {
                thumbnailsControlPanel.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                });
            });
        });
    };
};

var thumbnailsView = createObject(ThumbnailsView,ControlPanelView);

arikaim.component.onLoaded(function() {
    thumbnailsView.init();  
    thumbnailsView.initRows();  
}); 