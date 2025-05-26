(function($) {
    GO_Cloud_Server = {
        
        doc         : $(document),
        input_domain: null,
        input_token : null,
        is_item     : false,
        scope_form  : null,
        scope_list  : null,
        scope_item  : null,

        init: function() {
            GO_Cloud_Server._action();
            GO_Cloud_Server._subscribed_sites();
        },

        _action: function() {

            // Input domain
            GO_Cloud_Server.input_domain = $('input#domain');
            // Input token
            GO_Cloud_Server.input_token = $('input#token');
            GO_Cloud_Server._generate_token(null);
            // Scope
            GO_Cloud_Server.scope_form = $('.add-new-site-form');
            // Site list
            GO_Cloud_Server.scope_list = $('.site-list');

            // Generate token
            GO_Cloud_Server.doc.on(
                'click',
                '.generate-token',
                GO_Cloud_Server._generate_token
            );

            // Add new site
            GO_Cloud_Server.doc.on(
                'click',
                '.add-new-site',
                GO_Cloud_Server.add
            );

            // Site status change
            GO_Cloud_Server.doc.on(
                'change',
                '.site-status',
                GO_Cloud_Server._status_change
            );

            // Copy access token to clipboard
            GO_Cloud_Server.doc.on(
                'click',
                '.site-item .token',
                GO_Cloud_Server._copy_to_clipboard
            );

            // Trash item
            GO_Cloud_Server.doc.on(
                'click',
                '.trash-item',
                GO_Cloud_Server._trash
            );
        },

        _server: function(data, callback) {
            GO_Cloud_Server._before();
            $.ajax({
                url     : cloud_server.ajaxurl,
                type    : 'post',
                dataType: 'json',
                data    : data
            }).done(callback);
        },

        _generate_token: function(e) {
            if (e !== null) 
                e.preventDefault();
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let token = '';
            for(var i = 0; i < 80; i++) {
                token += chars[Math.floor(Math.random() * chars.length)];
            }
            GO_Cloud_Server.input_token.val( token );
        },

        add: function(e) {
            e.preventDefault();   
            GO_Cloud_Server.is_item = false;
            GO_Cloud_Server._server({
                action: 'go_add_new_site',
                domain: GO_Cloud_Server.input_domain.val(),
                token : GO_Cloud_Server.input_token.val()
            }, function(response) {
                console.log(response);   
                
                GO_Cloud_Server.input_domain.val('');
                GO_Cloud_Server._generate_token(null);
                GO_Cloud_Server._subscribed_sites();
                GO_Cloud_Server._after();
                GO_Cloud_Server._success('New site added.');
            });         
        },

        _subscribed_sites: function() {
            GO_Cloud_Server.is_item = false;
            GO_Cloud_Server._server({
                action: 'go_subscribed_sites'
            }, function(response) {
                GO_Cloud_Server.scope_list.html(
                    response.sites
                );
                GO_Cloud_Server._after();
            });
        },

        _status_change: function() { 
            $('.site-item').addClass('disabled');           
            const checkbox = $(this);
            checkbox.removeClass('disabled');
            GO_Cloud_Server.scope_item = checkbox.closest('.site-item');
            GO_Cloud_Server.is_item = true;
            GO_Cloud_Server._server({
                action : 'go_site_status_change',
                status : checkbox.is(':checked') ? 'active' : 'inactive',
                site_id: checkbox.data('site')
            }, function() {                
                GO_Cloud_Server._after();                
                $('.site-item').removeClass('disabled');           
            });
        },

        _before: function() {
            if (GO_Cloud_Server.is_item) {
                GO_Cloud_Server.scope_item.addClass('loading');
            } else {
                GO_Cloud_Server.scope_form.addClass('loading');
                GO_Cloud_Server.scope_list.addClass('loading');
            }            
        },

        _after: function() {
            if (GO_Cloud_Server.is_item) {
                GO_Cloud_Server.scope_item.removeClass('loading');
            } else {
                GO_Cloud_Server.scope_form.removeClass('loading');
                GO_Cloud_Server.scope_list.removeClass('loading');
            }            
            setTimeout(function() {
                $('.go-success-popup').fadeOut('fast', function() {
                    $('.go-success-popup').remove();
                });                
            }, 1000);
        },

        _copy_to_clipboard: function(e) {
            e.preventDefault();           
            
            let aux = document.createElement("input");            
            aux.setAttribute("value", $(this).text());            
            document.body.appendChild(aux);
            aux.select();            
            document.execCommand("copy");
            document.body.removeChild(aux);
            alert('API token copied.');
        },

        _trash: function(e) {
            e.preventDefault();
            const site = $(this);
            if (confirm('You sure to remove ' + site.data('domain')+'?')) {
                GO_Cloud_Server.is_item = false;
                GO_Cloud_Server._server({
                    action: 'go_remove_site',
                    site  : site.data('site')
                }, function(response) {
                    console.log(response);   
                    
                    GO_Cloud_Server.input_domain.val('');
                    GO_Cloud_Server._generate_token(null);
                    GO_Cloud_Server._subscribed_sites();
                    GO_Cloud_Server._after();
                    GO_Cloud_Server._success('Site removed.');
                });
            }
        },

        _success: function(message) {
            $('body').append(`
                <div class="go-success-popup" style="position: fixed;top: 0;left: 0;z-index: 999999;width: 100%;height: 100%;background-color: rgba(000, 000, 000, 0.5);display: flex;align-items: center;justify-content: center;">
                    <div class="message" style="display: flex;flex-direction: column;align-items: center;justify-content: center;width: 200px;height: 100px;background-color: #fff;border-radius: 10px;padding: 10px;">
                        <svg style="width:40px;height:auto;" id="Layer_1" style="enable-background:new 0 0 612 792;" version="1.1" viewBox="0 0 612 792" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><style type="text/css">.st0{fill:#41AD49;}</style><g><path class="st0" d="M562,396c0-141.4-114.6-256-256-256S50,254.6,50,396s114.6,256,256,256S562,537.4,562,396L562,396z    M501.7,296.3l-241,241l0,0l-17.2,17.2L110.3,421.3l58.8-58.8l74.5,74.5l199.4-199.4L501.7,296.3L501.7,296.3z"/></g></svg>
                        <div style="font-size:14px;">`+message+`</div>
                    </div>
                </div>
            `);
        }
    }
    GO_Cloud_Server.init();
})(jQuery)