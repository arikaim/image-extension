'use strict';

arikaim.component.onLoaded(function() {   
    safeCall('imagesLibraryRelations',function(obj) {
        obj.initRows();
    },true);  
    
    $('.library-image').dimmer({
        transition: 'fade up',
        on: 'hover'
    });
}); 