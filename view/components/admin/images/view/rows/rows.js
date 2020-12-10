'use strict';

$(document).ready(function() {     
    safeCall('mediaView',function(obj) {
        obj.initRows();
    },true);   
}); 