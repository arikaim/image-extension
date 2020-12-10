/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function ThumbnailsView() {
    var self = this;
    this.messages = null;

    this.init = function() {
        $('#thumbnail_size_dropdown').dropdown({
            onChange: function(value, text, item) { 
                $('#width').val($(item).attr('data-width'));
                $('#height').val($(item).attr('data-height'));
                $('#size').val($(item).attr('data-size'));

                if (value == 'custom') {
                    $('.size-field').show();
                } else {                    
                    $('.size-field').hide();
                }
            }
        });

        arikaim.ui.form.addRules("#create_thumbnail_form",{});

        arikaim.ui.form.onSubmit("#create_thumbnail_form",function() {  
            return thumbnailsControlPanel.create('#create_thumbnail_form');
        },function(result) {
            arikaim.ui.form.showMessage(result.message);
        });
    };

    this.loadMessages = function() {
        if (isObject(this.messages) == true) {
            return;
        }

        arikaim.component.loadProperties('media::admin.messages',function(params) { 
            self.messages = params.messages;
        }); 
    };

    this.initRows = function() {
       
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
    };
};

var thumbnailsView = new ThumbnailsView();

$(document).ready(function() {  
    thumbnailsView.init();  
    thumbnailsView.initRows();  
}); 