/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

class ImageCollectionsView extends View {
  
    init() {     
        this.loadMessages('image::admin.collections.messages',() => {           
        });      
        
        this.setItemSelector('row_');
        this.setItemsSelector('collections_rows');
        this.setItemComponentName('image::admin.collections.view.item');

        arikaim.ui.loadComponentButton('.create-collection');
        this.initRows();
    };

    initRows() {
        arikaim.ui.loadComponentButton('.collection-action-button');
       
        arikaim.ui.button('.delete-collection',(element) => {
            var uuid = $(element).attr('uuid');
            var title = $(element).attr('data-title');
            var message = arikaim.ui.template.render(this.getMessage('remove.content'),{ title: title });

            arikaim.ui.getComponent('delete_collection').open(function() {
                imageCollectionsControlPanel.delete(uuid,function(result) {
                    arikaim.ui.table.removeRow('#row_' + uuid);     
                    arikaim.ui.getComponent('toast').show(result.message);
                });               
            },message);      
        });
    };    
}

var collectionsView = new ImageCollectionsView();

arikaim.component.onLoaded(function() {
    collectionsView.init();
});