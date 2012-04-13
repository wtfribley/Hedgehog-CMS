/*
 * HEDGEHOG's Custom AJAX Class
 * 
 */

(function(Hedgehog, $, undefined){
    
    /*
     *  Hedgehog.AJAX takes a DOM Form object, parses its data, and sends it to
     *  the URL indicated by the form's data-submit-url attribute. 
     */
    
    Hedgehog.AJAX = function(form) {
        
        var action = $(form).attr('action'), // the action attribute serves as the name of the callback.
            url = $(form).attr('data-submit-url') + '.php',
            data = "action=" + action + "&" + $(form).serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            cache: false,
            error: function(obj, ertype) {
                alert(ertype);
            },
            success: function(response) {
                response = JSON.parse(response);               
                
                // handler is the custom callback set in ajax.php
                // 
                //      ...usually corresponds to the form's action
                //         or 'reload' to simply reload the page.
                var handler = response['handler'];

                window["Hedgehog"]["AJAX"][handler](response['data']);             
            }
        });
        
        
        // RESPONSE HANDLERS
        ///////////////////////
        
        Hedgehog.AJAX.reload = function(response) {
           (response == null) ? window.location = window.location : window.location = response;           
        }
        
        Hedgehog.AJAX.warning = function(message) {            
            var el = '<h4 class="alert-heading textcenter"><strong>Oops!</strong></h4><p class="textcenter">';
            el+= message + '</p>';

            $(form).siblings('.ajax-alert-msg').html(el).animate({opacity: 1}, 300, function(){
                $(this).delay(3000).animate({opacity: 0}, 300);
            });
        }
        
        Hedgehog.AJAX.post_published = function(response) {
            if (response === true) {
                // Deactivate the RTE and remove all selections
                Hedgehog.RTE.deactivate('all');
                var sel = rangy.getSelection();
                sel.removeAllRanges();
            }
            else {
                // Pop up error Modal to notify of failure
                var modal = $('<div class="modal alert-error fade textcenter">');
                var html = '<div class="modal-header"><a class="close" data-dismiss="modal">x</a>';
                html+= '<h4><strong>Oops!</strong></h4></div>';
                html+= '<div class="modal-body"><p>There seems to have been an error while publishing this post...</p>';
                html+= '<p>No information is currently available about the problem.</p></div>';
                html+= '<div class="modal-footer"><a class="btn" data-dismiss="modal">Close</a></div>';
                
                $(modal).html(html);
                $(modal).modal();                
            }
        }
        
        Hedgehog.AJAX.project_published = function(response) {
            if (response === true) {
                // Deactivate the RTE and remove all selections
                Hedgehog.RTE.deactivate('all');
                var sel = rangy.getSelection();
                sel.removeAllRanges();
            }
            else {
                // Pop up error Modal to notify of failure
                var modal = $('<div class="modal alert-error fade textcenter">');
                var html = '<div class="modal-header"><a class="close" data-dismiss="modal">x</a>';
                html+= '<h4><strong>Oops!</strong></h4></div>';
                html+= '<div class="modal-body"><p>There seems to have been an error while publishing this project...</p>';
                html+= '<p>No information is currently available about the problem.</p></div>';
                html+= '<div class="modal-footer"><a class="btn" data-dismiss="modal">Close</a></div>';
                
                $(modal).html(html);
                $(modal).modal();
            }
        };
        
        Hedgehog.AJAX.save = function(response) {
            Hedgehog.AJAX.saved = response;
            $(document).trigger('ajaxsaved');
        }
        
        Hedgehog.AJAX.test = function(response) {
            console.log(response);
        }
    };
    
}(window.Hedgehog = window.Hedgehog || {}, jQuery));