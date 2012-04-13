/*
 * HEDGEHOG's Form Class, including a 'Real-Time' Validator
 * 
 * Note:    The validation provided here is meant only as a convenience
 *          for users - it SHOULD NOT be the last word in form security. 
 *          
 *          Hedgehog.AJAX() will escape the form data, but extra measures
 *          should be taken on the server-side to validate any and all data.
 * 
 */

(function(Hedgehog, $, undefined){
        
    Hedgehog.Form = (function(){
        var pub = {}; // store public properties and methods in this object
                
        $(document).ready(function(){
        
            // REAL-TIME VALIDATION
            $('form input,form textarea').blur(function(){              
                pub.realtime(this);              
            }).focus(function(){
                $(this).parent('.control-group').removeClass('error');
                $(this).siblings('.help-inline').remove();
            });
        
            // SUBMIT
            $('form').submit(function(){
                
                if(pub.legal(this) && $(this).hasClass('ajax-form')) {                                        
                    Hedgehog.AJAX(this);
                }
                                
                return false; // prevent automatic refresh
            });        
        });
        
        // VALIDATION METHODS
        
        pub.legal = function(form){                     
            var legal = [];
            
            // Check Required Fields
            ///////////////////////////
            var fields = $(form).find('[data-required="required"]');
            fields.each(function(){
                legal.push(required(this));
            });
            
            // Check Email Fields
            ///////////////////////////
            fields = $(form).find('[type="email"]');
            fields.each(function(){
                legal.push(email(this));
            });
            
            return (legal.indexOf(false) == -1);
        };
        
        pub.realtime = function(field){
            
            var req = ($(field).attr('data-required') == 'required') ? $(field) : null;
            required(req);
            
            var eml = ($(field).attr('type') == 'email') ? $(field) : null;
            email(eml);
            
        }
        
        function required(field) {
            if(field !== null) {
                var test = ($(field).val() != ''),
                    message = $(field).attr('name') + ' is required';
                                            
                warning(test, field, message);
                                
                return test;
            }          
        }
        
        function email(field) {
            if(field !== null) {                
                var pattern = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
                    message = 'invalid email',
                    test = pattern.test($(field).val());

                warning(test, field, message);

                return test;
            }         
        }
        
        function warning(test, field, message) {
            $(field).siblings('.help-inline').remove();
                        
            if (test === false) {
                var warning = $('<span class="help-inline">').html(message);
                $(field).parent('.control-group').addClass('error');
                $(field).after(warning);
            }           
        }
        
        
        // Expose the public properties and methods of Hedgehog.Form
        return pub;
    }());
}(window.Hedgehog = window.Hedgehog || {}, jQuery));