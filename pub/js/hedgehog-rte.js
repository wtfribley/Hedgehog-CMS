/*
 * HEDGEHOG's Custom Rich Text Editor (RTE)
 * 
 * Many thanks to the life-saving Rangy Library, found here: code.google.com/p/rangy
 * 
 */

(function(Hedgehog, $, undefined){
  
// Define the RTE namespace
Hedgehog.RTE = (function(){
    var pub = {}, // store public properties and methods in this object
        callback = ''; // set to the custom publish function by init() and used in publishEditables()

    // INITIALIZE AND BIND EVENTS (public)
    ////////////////////////////////

    pub.init = function(customSubmitFnc) {
    // Assignment
    callback = customSubmitFnc;

    $(document).ready(function(){
    
    // Find all contenteditables
    var editables = $('[contenteditable=true]');
        
    if (editables.length > 0) {
        
        // Deactivate Controls (except publishing)
        $('section.editor-controls div').find('button').not('[data-edit-action="publish"]').attr('disabled','disabled');
        
        // Show RTE on click
        $.each(editables, function() {
            var controls = $(this).attr('data-editable-controls');
            
            // Set up proper padding on editables - fix weird width problem
            var width = $(this).width();
            $(this).css({'padding':'10px','width':width});
            
            $(this).click(function() {pub.activate(this, controls);});         
        });
        
        // Hide RTE by catching all click events outside the editables and the controls
        $('html').click(function(evt) {
            var srcEl = $(evt.srcElement);
            if (srcEl.parents('[contenteditable=true]').size() > 0 || srcEl.parents('section.editor-controls').size() > 0 || srcEl.attr('contenteditable') == 'true' || srcEl.next("[contenteditable=true]").size() > 0) {
               // do nothing
            }
            else {
                pub.deactivate('all');
            }             
        });
        
        // Bind to Buttons
        
        // Style
        $('section.editor-controls div[data-editor-group="style"] button').click(function() {style($(this).attr('data-edit-action'));});
        
        // Align
        $('section.editor-controls div[data-editor-group="align"] button').click(function() {align($(this).attr('data-edit-action'));});
        
        // Formatting
        $('section.editor-controls div[data-editor-group="formatting"] button').click(function() {format($(this).attr('data-edit-action'));});
        
        // Inserts
        $('section.editor-controls div[data-editor-group="inserts"] button').click(function() {insert($(this).attr('data-edit-action'));});
        
        // View Source
        $('section.editor-controls button[data-edit-action="view-source"]').click(function() {viewSource($(this));});
        
        // Publish
        $('section.editor-controls button[data-edit-action="publish"]').click(function() {publish();});
        
        // Add 'active' attribute to textareas with focus for "view source" functionality
        $('html').on('focus', 'textarea', function() {
            if ($(this).next().attr("contenteditable") == "true") {
                editables.prev('textarea').attr('data-editable-source','inactive');
                $(this).attr('data-editable-source','active');                
            }
        });
    }
    else {
        // pop up a reminder with instructions on how to make areas editable
    }
    
    // Add a special class to all tags which can be modified by toggleSurroundSelection();
    //   ...this class will be removed in the code cleanup process - i.e. on publish.
    editables.find('strong, em, a').addClass('rte');
    
    });
};

    // ACTIVATE and DEACTIVATE (public)
    ////////////////////////////////////////

    pub.activate = function(editable, controls) {
    
        // Deactivate other editables
        pub.deactivate(editable);

        $editable = $(editable);

        // Give the this editable an attribute so we can find it later
        $editable.attr('data-editable-active','true');

        // Activate/Deactivate proper controls - governed by editables' data-editable-controls attribute.
        $('section.editor-controls button').removeAttr('disabled');  
        if(controls != 'all') {
            //@todo: split controls on ' ' and run a loop.
            $('section.editor-controls div').not('[data-editor-group="'+controls+'"]').not('[data-editor-group="publishing"]').find('button').attr('disabled','disabled');
        }

        // Fade in the controls
//        $controls = $('section.editor-controls');
//        $controls.css('visibility','visible').stop().animate({opacity: 1}, 200);

        // Change the editable's background.
        $editable.css('background-color', '#fff');
    
    }
    
    pub.deactivate = function(editable) {
    
        var $editable = $('[contenteditable=true]');

        (editable == 'all') ? $editable = $editable : $editable = $editable.not($(editable));

        var $controls = $('section.editor-controls div');

        // Remove the active attribute
        $editable.removeAttr('data-editable-active');

        // Reset the editables' background.
        $editable.css('background-color','transparent');

        // Deactivate Controls (except publishing)
        $controls.find('button').not('[data-edit-action="publish"]').attr('disabled','disabled');
    }
    
    
    // RTE EDITING FUNCTIONS
    // @todo: style and insert link only work on already highlighted text - include a way to use these "inline".
    //////////////////////////////

    function style(tag) {    
        toggleSurroundSelection(tag);    
    }

    function align(clss) {    
        toggleClassOnUniqueAnscestors(clss);        
    }

    function format(tag) {    
        toggleTagOfUniqueAnscestors(tag);
    }


    // @todo: finish this shit - add modals and stuff...
    function insert(tag) {
        if (tag == "a") {
            toggleSurroundSelection(tag);
        }
    }

    function viewSource(btn) {

        var thisEditable = "textarea";    
        if (rangy.getSelection().anchorNode != null) {
            thisEditable = $(rangy.getSelection().anchorNode.parentElement);
            if (!thisEditable.attr('contenteditable')) {thisEditable = thisEditable.parents("[contenteditable=true]");}
        }   

        // switch from VISUAL to HTML
        if (thisEditable != "textarea") {

            btn.html("visual");

            var content = thisEditable.html();
            var txtarea = $("<textarea>").html(content).height(thisEditable.height()).width(thisEditable.width());

            thisEditable.before(txtarea).css("display","none").prev("textarea").focus();
        }
        // switch from HTML to VISUAL
        else {

            btn.html("html");

            var txtarea = $("textarea[data-editable-source='active']");
            var content = txtarea.val();

            txtarea.next("[contenteditable=true]").html(content).css("display","inline-block").focus();
            txtarea.remove();
        }
    }
    
    // SELECTION & RANGE FUNCTIONS... big thanks to the Rangy library at code.google.com/p/rangy
    ///////////////////////////////////

    function toggleSurroundSelection(tag) {   
        var applier = rangy.createCssClassApplier('rte', {elementTagName: tag, normalize: true});    
        applier.toggleSelection();    
    }

    function toggleClassOnUniqueAnscestors(clss) {

        var sel = rangy.saveSelection();

        var nodes = getUniqueAnscestors();

        if (nodes.length == undefined) nodes = [nodes];

        $.each(nodes, function(i, node) {
            if (clss.indexOf('align-') > -1) {
                $(nodes[i]).removeClass('align-center align-right align-justify');
                if (clss != "align-left") $(nodes[i]).addClass(clss);
            }
            else {
                $(nodes[i]).toggleClass(clss);
            } 
        });

        rangy.restoreSelection(sel);
    }

    function toggleTagOfUniqueAnscestors(tag) {

        var sel = rangy.saveSelection();

        var nodes = getUniqueAnscestors();

        if (nodes.length == undefined) nodes = [nodes];

        $.each(nodes, function(i, node) {
            $(node).replaceWith(function() {
                var $html = $("<"+tag+">",{"class":this.className});
                $html.html(this.innerHTML);
                return $html;
            });
        });

        rangy.restoreSelection(sel);
    }

    function getUniqueAnscestors() {
        var sel = rangy.getSelection();
        var range = sel.getRangeAt(0);

        if (range.collapsed == true) {
            if (range.startContainer.nodeType == 3) {
                return range.startContainer.parentNode;
            } else {
                return range.startContainer;
            }
        }
        else {
            var nodes = range.getNodes([3]), node, uniqueNodes = [];
            var nodeslen = nodes.length;

            for (var i = 0; i < nodeslen; i++) {
                node = nodes[i].parentNode;

                if (uniqueNodes.indexOf(node) == -1)
                    uniqueNodes.push(node);
            }

            nodeslen = uniqueNodes.length   
            for (var i = 0; i < nodeslen; i++) {
                var parentNode = uniqueNodes[i].parentNode;

                if (uniqueNodes.indexOf(parentNode) > -1) {
                    uniqueNodes[i] = 0;
                }
            }

            return uniqueNodes.remove(0);
        }
    }

    Array.prototype.remove= function(){
        var what, a= arguments, L= a.length, ax;
        while(L && this.length){
            what= a[--L];
            while((ax= this.indexOf(what))!= -1){
                this.splice(ax, 1);
            }
        }
        return this;
    }
    
    // PUBLISHING FUNCTION
    /////////////////////////

    function publish() {       
        
        // clean the code
        var editables = $('[contenteditable=true]');

        // initialize data object
        var editablesData = {};

        $.each(editables, function() {
            var $this = $(this);

            if ($this.prev('textarea').val() != undefined) {
                var txtarea = $this.prev('textarea');
                var val = txtarea.val();

                $this.html(val).css("display","inline-block").focus();
                txtarea.remove();
            }

            // Remove temporary "rte" identifier class.
            $this.find('[class="rte"]').removeClass('rte');

            // Get the content and clean up any extra whitespace and
            //  empty attribute definitions.
            var content = $.trim($this.html());            
            content = content.replace(/\s\w+\=""/g,'');

            var field = $this.attr('data-rte-field');

            editablesData[field] = content;                       
        });       

        callback(editablesData); // this custom callback is set by Hedgehog.RTE.init('callback');
    }
    
            
    // Expose the public properties and methods of Hedgehog.RTE
    return pub;
}());
    
}(window.Hedgehog = window.Hedgehog || {}, jQuery));