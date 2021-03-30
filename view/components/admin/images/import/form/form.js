'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#import_image_form");
    $('.private-image').checkbox({
        onChecked: function() {
            $(this).val(1);
        },
        onUnchecked: function() {
            $(this).val(false);
        }
    });

    var checked = $('.private-image').checkbox('is checked');
    $('#private').val(checked);
    
    arikaim.ui.form.onSubmit("#import_image_form",function() {  
        return arikaim.post('/api/admin/image/import','#import_image_form',function(result) {
            return arikaim.page.loadContent({
                id: 'image_content',
                params: { uuid: result.uuid },
                component: 'image::admin.images.view'
            });
        });
    });
});