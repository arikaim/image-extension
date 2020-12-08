'use strict';

$(document).ready(function() {
    $('#install_games_job').accordion({});

    $('#drivers_dropdown').dropdown({
        onChange: function(value) {                    
            options.save('arcade.job.feeds.driver',value);
        }
    });

    $('#install_games_toggle').checkbox({
        onChecked: function(value) {         
            var uuid = $(this).attr('uuid');           
            jobs.enable(uuid,function(result) {
                jobs.load(uuid,'install_games_job',function(result) {
                    $('#install_games_job').accordion({});
                });   
                arikaim.ui.show('#feed_settings');        
            });
        },
        onUnchecked: function(value) {    
            var uuid = $(this).attr('uuid');
            jobs.disable(uuid,function(result) {
                jobs.load(uuid,'install_games_job',function(result) {
                    $('#install_games_job').accordion({});
                });
                arikaim.ui.hide('#feed_settings');
            });
        }
    });

    arikaim.ui.form.onSubmit("#job_settings_form",function() {  
        var fromPage = $('#from_page').val();        
        var toPage = $('#to_page').val();
        var currentFeedItem = $('#current_feed_item').val();
        var maxInstall = $('#max_install').val();

        options.save('arcade.current.feed.item',currentFeedItem);
        options.save('arcade.job.feeds.to.page',toPage);
        options.save('arcade.job.max.install',maxInstall);

        return options.save('arcade.job.feeds.from.page',fromPage);
    },function(result) {
        arikaim.ui.form.showMessage(result.message);
    });
});