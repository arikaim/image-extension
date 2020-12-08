'use strict';

$(document).ready(function() {
    arikaim.ui.form.addRules("#media_settings");

    arikaim.ui.form.onSubmit("#media_settings",function() {  
        var perPage = $('#items_per_page').val();
        return options.save('media.items.per.page',perPage);
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});