/*
 * HEDGEHOG's Post Editor Class INCLUDING Admin Posts Functionality
 * 
 */

(function(Hedgehog, $, undefined){
    
    Hedgehog.PostEditor = (function(){
        var pub = {}; // store public properties and methods in this object
                
        pub.init = function(publishFunction) {                       
            
            // ALL non-RTE EDITING FUNCTIONALITY
            $(document).ready(function(){
               
                // ACTIVATE / DEACTIVATE pre-existing categories, projects, and @todo: tags.
                $('.articles').on('click','.post-category, .post-project',function(){
                    $(this).toggleClass('strikethru');
                })
                
                // RETRIEVE a List of All Categories and Projects (@todo: tags) for use in typeahead.
                var form = $('<form action="typeahead_postedit" data-submit-url="ajax">');                   
                Hedgehog.AJAX(form);
                
                var categories = {},
                    projects = {};
                
                // INITIALIZE typeahead once the ajax request has completed.
                $(document).bind('ajaxsaved', function(){
                    var response = Hedgehog.AJAX.saved;
                    
                    $.each(response[0], function(i,o){                        
                        categories[o['categories']] = o['id'];
                    });

                    $.each(response[1], function(i,o){
                        projects[o['title']] = o['slug'];
                    });
                          
                    $('.post-category-add').typeahead({'source': Object.keys(categories)});
                    $('.post-project-add').typeahead({'source': Object.keys(projects)});
                });
                
                // ADD NEW Categories, Projects, and @todo: tags.
                $('.post-category-add, .post-project-add').keypress(function(e){
                    if (e.keyCode == 13) {
                        var val = $(this).val().toLowerCase(),
                            id = categories[val],
                            classname = 'post-category';
                                               
                        if (e.srcElement.className.indexOf('category') == -1) {
                            val = $(this).val();
                            id = projects[val];
                            classname = 'post-project';
                        }
                        
                        // Create new entry
                        var li = $('<li><a class="' + classname + '" alt="' + val + '">' + val + '</a></li>'),
                            alts = [];
                        
                        // New entries don't have an id - store the value there instead
                        (id == undefined) ? li.children('a').attr('id',val) : li.children('a').attr('id',id);
                        
                        // Create an array of values to check against
                        $('.' + classname).each(function(){
                            alts.push($(this).attr('alt'));
                        });
                        
                        // If it's not already there, add it - Projects can't add new entries here
                        if (alts.indexOf(val) == -1) {
                            if (classname == 'post-category' || id != undefined)
                                $(this).val('').before(li);
                        }
                    }
                });

                // DATE

                $('.post-datetime').children('.btn').toggle(function(){
                    var unixDate = $(this).parents('.post-datetime').attr('data-datetime'),
                        textDate = $.datepicker.formatDate('M. d, yy', new Date(unixDate*1000)),
                    
                        input = $('<input class="input-small" type="text"/>');
                        $(input).val(textDate).datepicker({
                            dateFormat: 'M. d, yy', 
                            showButtonPanel: true,
                            onSelect: function(date){                               
                                var now = new Date,
                                    hrs = now.getHours(),
                                    mins = now.getMinutes(),
                                    secs = now.getSeconds();
                                    
                                date+= " " + hrs + ":" + mins + ":" + secs;
                                date = $.datepicker.formatDate('@',new Date(date))/1000;
                                
                                $('.post-datetime').attr('data-datetime',date);
                            }
                        });
                    
                    $(this).attr('data-defaultdatetime',unixDate).removeClass('btn-primary').addClass('btn-danger')
                        .html('cancel').siblings('strong').replaceWith(input);
                    $(this).siblings('em').hide();
                    
                }, function(){
                    var unixDate = $(this).attr('data-defaultdatetime'),
                        textDate = $.datepicker.formatDate('M. d, yy', new Date(unixDate*1000)),
                        
                        strong = $('<strong class="serif">');
                        $(strong).html(textDate);
                        
                    $(this).removeClass('btn-danger').addClass('btn-primary').html('change?')
                        .siblings('input').replaceWith(strong);
                    $(this).siblings('em').show();
                    
                    $(this).parents('.post-datetime').attr('data-datetime',unixDate);
                });

                // PUBLISHED STATUS - update the select color AND the publish button text
                //      Note: This also runs on admin/posts and /blog (when logged in)
                statusCheck($('.post-status'));              
                $('.post-status').change(function(){ 
                    statusCheck(this);
                    
                    // Quick Check to see if we're on the Admin Posts Page
                    //      Then Update the Post's status immediately
                    if($('.admin-posts').size() > 0) {
                        statusUpdate(this);
                    }              
                });
                // Position the Popover
                $('.editor-controls').find('.popover').offset({left:737,top:253});
                
                // COMMENTS STATUS - note: this is for the admin/posts page
                $('[data-comment-status="disabled"]').button('disabled');
                $('[data-comment-status="disabled"]').button('toggle');
                $('.comment-btn').click(function(){
                    var id = $(this).parents('tr').attr('id'),
                        val = $(this).html().toLowerCase(),
                        form = $('<form action="publish_post" data-submit-url="ajax">');

                        form.append('<input type="hidden" name="id" value="' + id + '" />')
                        form.append('<input type="hidden" name="comments" value="' + val + '" />');
                    
                    Hedgehog.AJAX(form);
                });
                
                // EMPTY TRASH - note: this is for the admin/posts page
                $('.post-emptytrash').click(function(){
                    var form = $('<form action="delete_post" data-submit-url="ajax">'),
                        ids = [];
                    
                    $('.trash-bin').find('tr').each(function(){
                        if ($(this).attr('id') != undefined)
                            ids.push($(this).attr('id'));
                    });
                
                    form.append('<input type="hidden" name="ids" value="' + ids.toString() + '" />');
                    
                    Hedgehog.AJAX(form);
                })
            });
            
            function statusCheck(select) {               
                $(select).each(function(){
               
                var val = $(this).val(),
                    publishBtn = $('section.editor-controls button[data-edit-action="publish"]');             

                (val == '') ? val = $(this).html() : val = val;

                switch (val) {
                    case "published":
                        $(this).css({'background-color':'#5e9b30','border-color':'#5e9b30'});
                        publishBtn.removeClass('btn-danger').addClass('btn-success').html('publish');                        
                        break;
                    case "draft":
                        $(this).css({'background-color':'#f89406','border-color':'#f89406'});
                        publishBtn.removeClass('btn-danger').addClass('btn-success').html('save');
                        break;
                    case "trash":
                        $(this).css({'background-color':'#b94a48','border-color':'#b94a48'});
                        publishBtn.removeClass('btn-success').addClass('btn-danger').html('trash');

                        $('.editor-controls').find('.popover').fadeIn(600,function(){
                            $(this).delay(2000).fadeOut(300);
                        });

                        break;
                }
                
                });               
            }
            
            function statusUpdate(select) {
                var val = $(select).val(),
                    id = $(select).parents('tr').attr('id'),
                    form = $('<form action="publish_post" data-submit-url="ajax">');
                    
                    form.append('<input type="hidden" name="id" value="' + id + '" />')
                    form.append('<input type="hidden" name="status" value="' + val + '" />');
                    
                    Hedgehog.AJAX(form);
            }
            
            // Initialize the RTE
            var callback = (publishFunction == undefined) ? Hedgehog.PostEditor.publish : publishFunction;
            
            Hedgehog.RTE.init(callback);
            
        };
        
        pub.publish = function(rteData) {
        
            // Gather all the post data, combine it with rteDATA, and create a DOM Form object...
       
            // Get Date
            var datetime = $('.post-datetime').attr('data-datetime');
       
            // Get Categories
            var categories = [];
            $('.post-category').not('.strikethru').each(function(){
                categories.push($(this).attr('id'));
            });
            
            // Get Project
            var project = [];
            $('.post-project').not('.strikethru').each(function(){
                project.push($(this).attr('id'));
            });
            
            // Get Tags
            var tags = [];
            $('.post-tag').each(function(){
                tags.push($(this).attr('id'));
            });
            
            // Get Status
            var status = $('.post-status').val();
            
            var data = {
                'id': $('article').attr('id'),
                'categories': categories.toString(),
                'project': project.toString(),
                'date': datetime,
                'status': status
                // add 'tag': tags when tag support is fully implemented.
            };                                  

            $.extend(data,rteData);
            
            var form = $('<form action="publish_post" data-submit-url="ajax">');

            $.each(data, function(field,value){
                if (value != '') {
                    var el = $('<input type="hidden" />');
                    el.attr('name',field).val(value);
                    form.append(el);
                }               
            });
            
            Hedgehog.AJAX(form);
          
        };
        
        // Expose the public properties and methods of Hedgehog.PostEditor()
        return pub;
    }());
    
}(window.Hedgehog = window.Hedgehog || {}, jQuery));

// INITIALIZE!

$(document).ready(function(){
   
   if ($('section.articles').length > 0 || $('section.admin-posts').length > 0)
       Hedgehog.PostEditor.init();
});