'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.onSubmit("#media_details_form",function() {  
        return media.update('#media_details_form');
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});