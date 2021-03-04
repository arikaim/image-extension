'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.form.addRules("#upload_form");
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

});