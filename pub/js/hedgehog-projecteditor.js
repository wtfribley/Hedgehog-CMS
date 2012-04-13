/*
 * HEDGEHOG's Project Editor Class INCLUDING Drag n' Drop functionality for adding
 * media elements. Also includes the front end of Hedgehog's Upload Manager.
 * 
 */

(function(Hedgehog, $, undefined){
    
    Hedgehog.ProjectEditor = (function(){
        var pub = {}; // store public properties and methods in this object
        
        pub.init = function(publishFunction) {
          
            // Initialize the RTE
            var callback = (publishFunction == undefined) ? Hedgehog.ProjectEditor.publish : publishFunction;

            Hedgehog.RTE.init(callback);
          
        };
        
        pub.publish = function(rteData) {
          
          var data = {
              id: $('.project').attr('data-project-id'),
              content: $('.project-content').html()
          }
          
          $.extend(data,rteData)
          
          var form = $('<form action="publish_project" data-submit-url="ajax">');

            $.each(data, function(field,value){
                if (value != '') {
                    var el = $('<input type="hidden" />');
                    el.attr('name',field).val(value);
                    form.append(el);
                }               
            });
                        
            Hedgehog.AJAX(form);
          
        }; 
      
     // Expose the public properties and methods of Hedgehog.ProjectEditor
        return pub;
    }());
    
}(window.Hedgehog = window.Hedgehog || {}, jQuery));

// INITIALIZE!

$(document).ready(function(){
   
   if ($('section.project').length > 0)
       Hedgehog.ProjectEditor.init();
});