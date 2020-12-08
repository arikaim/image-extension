'use strict';

$(document).ready(function() {     
    safeCall('mediaImportView',function(obj) {
        obj.initRows();
    },true);   
}); 