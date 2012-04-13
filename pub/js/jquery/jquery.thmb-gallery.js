/**
 *  A jQuery Plugin that creates simple gallery functionality from an unordered list
 *  of thumbnails - preferably one using Bootstrap from Twitter's .thumbnail class and
 *  a custom .overlay class which is revealed on hover.
 *  
 *  The plugin either turns the thumbnails into links or - if given a jQuery selector - runs an
 *  Ajax request and updates the selected target with the response.
 *  
 *  It also makes use of the hashchange plugin to maintain back-button support and linkability.
 */

(function($){
    
    var methods = {
        init : function(options) {           
            
            var rtrn = this.each(function(){                                                 
                
                var $gallery = $(this);
                
                function mseover(el){
                    $(el).stop().animate({opacity:1},300);
                }
                function mseout(el){
                    $(el).stop().animate({opacity:0},300);
                }
                
                $gallery.find('.thumbnail').each(function(){
                    var $thmb = $(this),
                        target = $thmb.attr('data-target'),
                        thmbID = $thmb.attr('data-project-id'),
                        action = $thmb.attr('data-action');
                        
                    var duplicate = false;
                        
                    $thmb.find('.overlay').on('mouseover',function(){
                        mseover(this);
                    }).on('mouseout',function(){
                        mseout(this);
                    }).click(function(evt, close){                       
                        if (duplicate === false) {                          
                        
                        // Just Link to the Target if it's a URI or URL (if this is a triggered event, don't redirect)
                        if ((target.indexOf('/') == 0 || target.indexOf('http') == 0) && evt.isTrigger == undefined)
                            window.location = target;
                        
                        // The Target is a DOM Object, lets fill it via AJAX
                        else if (target.indexOf('/') == 0 || target.indexOf('http') == 0)
                            return false;
                        else if ($(target).length) {
                            var $target = $(target),
                                contentID = $target.find('section.project').attr('data-project-id'),
                                data = 'action='+action+'&id='+thmbID;
                            
                            // We Clicked On a New Thumbnail
                            if (thmbID != contentID && close == undefined) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'ajax.php',
                                    data: data,
                                    cache: false,
                                    error: function(obj, ertype) {
                                        alert(ertype);
                                    },
                                    success: function(response) {
                                        response = JSON.parse(response);               
                                        var html = response['data'],
                                            $html = $(html).css({display: 'none'}),
                                            height = 400;
                                            
                                        // Little hack to get the proper height...
                                        $target.html(html).after($html);
                                        height = Math.max($target.next().height(), height);
                                        $target.next().remove();
                                        
                                        // if we're loading an iframe, let's fade it in.
                                        $('iframe').hide().load(function(){
                                            $(this).fadeIn(600);
                                        });                                       
                                        
                                        $target.stop().animate({height: height}, 500);
                                    }
                                });
                                
                                $gallery.find('.overlay').on('mouseout',function(){
                                    mseout(this);
                                }).not(this).trigger('mouseout');
                                $(this).off('mouseout');                                                             
                                
                                // Update the hash for linkability and back-button support
                                window.location.hash = $(this).attr('data-project-slug');
                            }
                            // We Clicked on the Same Thumbnail - CLOSE
                            else {
                                $gallery.find('.overlay').on('mouseout',function(){
                                    mseout(this);
                                }).not(this).trigger('mouseout');
                                if (close != undefined)
                                    $(this).trigger('mouseout');
                                
                                $target.stop().animate({height: 0}, 500, function(){
                                    $(this).html('');
                                });
                            }
                        }
                        else {
                            $.error('Target ' + target + ' is invalid.');
                        }
                        
                        // Prevent a duplicate trigger due to click updating the hash, which
                        //   then triggers another click.
                        if (evt.isTrigger == undefined)
                            duplicate = true;
                    
                        } else {
                            duplicate = false;
                        } // end if duplicate === false
                    });
                });               
            });
            
            $(window).on('hashchange', function(){
                var hash = window.location.hash.substring(1);
                
                if (hash == '')
                    $('.thumbnails.gallery').find('.overlay').first().trigger('click','close');                   
                else
                    $('[data-project-slug="' + hash + '"]').trigger('click').trigger('mouseover');
            });
                        
            return rtrn;
        }        
    };
    
    $.fn.hedgeGallery = function(method) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.hedgeGallery' );
        }
    };
    
})(jQuery);

$(document).ready(function(){
    $('.thumbnails.gallery').hedgeGallery();
    $(window).trigger('hashchange');
});