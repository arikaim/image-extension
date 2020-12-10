'use strict';

$(document).ready(function() {   
    arikaim.ui.form.onSubmit("#meta_tags_form",function() {     
        var language = $('#choose_language').dropdown('get value');
        $('#language').val(language);

        return media.updateMetaTags('#meta_tags_form');
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});