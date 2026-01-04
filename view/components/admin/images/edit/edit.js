'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit("#update_image_form",function() {  
        return imageControlPanel.update('#update_image_form');
    },function(result) {          
        arikaim.ui.form.showMessage(result.message);        
    });
});