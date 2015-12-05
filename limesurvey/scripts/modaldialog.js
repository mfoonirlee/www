$(function() {
    openUploadModalDialog();
});
function openUploadModalDialog(){
    $('.upload').click(function(e) {
        e.preventDefault();
        var $this = $(this);
        var show_title   = getQueryVariable('show_title', this.href);
        var show_comment = getQueryVariable('show_comment', this.href);
        var pos          = getQueryVariable('pos', this.href);
        var fieldname    = getQueryVariable('fieldname', this.href);
        var buttonsOpts = {};
        buttonsOpts[uploadLang.returnTxt] = function() {
            $(this).dialog("close");
        };
        var windowwidth = $(window).width()-30;
        var dialogwidth= Math.min(windowwidth, 940);

        $('#uploader').dialog('destroy').remove(); // destroy the old modal dialog
        $('<iframe id=\"uploader\" name=\"uploader\" class=\"externalSite\" src=\"' + this.href + '\" />').dialog({
                title: uploadLang.title,
                autoOpen: true,
                width: dialogwidth,
                height: 'auto',
                open: function( event, ui ) {
                    setWidthUploader();
                },
                modal: true,
                resizable: false,
                autoResize: false,
                draggable: true,
                closeOnEscape: false,
                dialogClass: "dialog-upload",
                beforeClose: function() {
                    var pass;
                    if(document.getElementById('uploader').contentDocument) {
                        if(document.getElementById('uploader').contentDocument.defaultView)
                            {       /*Firefox*/
                            pass=document.getElementById('uploader').contentDocument.defaultView.saveAndExit(fieldname,show_title,show_comment,pos);
                        }else{       /*IE8*/
                            pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                        }
                    }else{    /*IE6*/
                        pass=document.getElementById('uploader').contentWindow.saveAndExit(fieldname,show_title,show_comment,pos);
                    }
                    return pass;
                },
                overlay: {
                    opacity: 0.85,
                    background: 'black'
                },
                buttons: buttonsOpts,
                close: function( ) {
                    checkconditions();
                }
            });
    });
}
$(window).resize(function() { 
    setWidthUploader();
    if(typeof $("iframe#uploader")[0]!=="undefined" && jQuery.isFunction($("iframe#uploader")[0].contentWindow.fixParentHeigth))
        $("iframe#uploader")[0].contentWindow.fixParentHeigth();
});
/* Reset the position of the dialog (recenter) */
function resetUploaderPosition(){
     $( "#uploader" ).dialog( "option", "position", $( "#uploader" ).dialog( "option", "position" ) );
     
}
/* Set the with of upload madal and uploader frame according to windows width */
function setWidthUploader(){
    var maxwidth=Math.min($("body").innerWidth()-4, 974);
    if(maxwidth!=$( "#uploader" ).dialog( "option", "width" )){
        $("#uploader").dialog( "option", "width", maxwidth).width(maxwidth-18) // Leave 20px for overflow
    }
    resetUploaderPosition();
}

function getQueryVariable(variable, url) {
    var vars = url.split("/");
    for (var i=0;i<vars.length;i++) {
        //var pair = vars[i].split("=");
        if (vars[i] == variable) {
        return vars[i+1];
        }
    }
    // If not found try with ?
    // TODO : replace by a regexp
   var vars = url.replace(/\&amp;/g,'&').split("&");
   for (var i=0;i<vars.length;i++) {
           var pair = vars[i].split("=");
           if(pair[0] == variable){return pair[1];}
   }
   return null;
}

function isValueInArray(arr, val) {
    inArray = false;
    for (i = 0; i < arr.length; i++) {
        if (val.toLowerCase() == arr[i].toLowerCase()) {
            inArray = true;
        }
    }

    return inArray;
}

function displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment) {
    var jsonobj;
    var i;
    var display = '';

    if (jsonstring == '[]') {
        $('#'+fieldname+'_uploadedfiles').html(display);
        return;
    }

    if (jsonstring !== '')
    {
        jsonobj = eval('(' + jsonstring + ')');
        display = '<table width="100%" class="question uploadedfiles"><thead><tr><td width="20%">&nbsp;</td>';
        if (show_title != 0)
            display += '<th>'+uploadLang.headTitle+'</th>';
        if (show_comment != 0)
            display += '<th>'+uploadLang.headComment+'</th>';
        display += '<th>'+uploadLang.headFileName+'</th><th class="edit"></th></tr></thead><tbody>';
        var image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico');

        for (i = 0; i < filecount; i++)
        {
            if (isValueInArray(image_extensions, jsonobj[i].ext))
                display += '<tr><td class="upload image"><img src="' + uploadurl + '/filegetcontents/'+decodeURIComponent(jsonobj[i].filename)+'" class="uploaded" /></td>';
            else
                display += '<tr><td class="upload placeholder"><div class="upload-placeholder" /></td>';

            if (show_title != 0)
                display += '<td class="upload title">'+jsonobj[i].title+'</td>';
            if (show_comment != 0)                                                                                                  
                display += '<td class="upload comment">'+jsonobj[i].comment+'</td>';
            display +='<td class="upload edit">'+decodeURIComponent(jsonobj[i].name)+'</td><td>'+'<a class="upload-edit" onclick="javascript:upload_'+fieldname+'();$(\'#upload_'+fieldname+'\').click();">'+uploadLang.editFile+'</a></td></tr>';
        }
        display += '</tbody></table>';

        $('#'+fieldname+'_uploadedfiles').html(display);
    }
}

function copyJSON(jsonstring, filecount, fieldname, show_title, show_comment, pos) {
    $('#'+fieldname).val(jsonstring);
    $('#'+fieldname+'_filecount').val(filecount);
    displayUploadedFiles(jsonstring, filecount, fieldname, show_title, show_comment, pos);
}


function showBasic() {
    $('#basic').show();
}

function hideBasic() {
    $('#basic').hide();
}
