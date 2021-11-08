'use strict';

arikaim.component.onLoaded(function() { 
    arikaim.ui.button('.close-button',function(element) {
        $('#image_details').fadeOut(500);
    });
});