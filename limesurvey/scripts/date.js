$(document).ready(function(){

    // dropdown dates

});
/* 
 * Function to launch timepicker in question id
 */
function doPopupDate(qId){
    if($("#question"+qId+" .popupdate").length){
        //console.log($("#question"+qId+" .popupdate"));
        var basename = $("#question"+qId+" .popupdate").attr("id").substr(6);
        format=$('#dateformat'+basename).val();
        language=$('#datelanguage'+basename).val();
        $("#question"+qId+" .popupdate").datetimepicker({ 
            showOn: 'both',
            changeYear: true,
            changeMonth: true,
            defaultDate: +0,
            // TODO: add support for minute interval, different month identifiers and times without minutes
            firstDay: "1",
            duration: 'fast',
            // set more options at "runtime"
            beforeShow: setPickerOptions
        }, $.datepicker.regional[language]);
    }
}
/* 
 * Function to launch timepicker in question id
 */
function doDropDownDate(qId){
    $(document).on("change",'#question'+qId+' select',dateUpdater);
    $(document).ready(function(){
        $("#question"+qId+" select").filter(':first').trigger("change");
    });
}
/* This function is called each time shortly before the picker pops up.
 *  Here we set all the picker options that can be different from question to question.
 */
function setPickerOptions(input)
{
    var basename = input.id.substr(6);
    var format=$('#dateformat'+basename).val();

    //split format into a date part and a time part
    var datepattern=new RegExp(/[mydYD][mydYD.:\/-]*[mydYD]/);
    var timepattern=new RegExp(/[HM][HM.:\/-]*[HM]/);
    var sdateFormat=datepattern.exec(format);
    if (sdateFormat!=null)
        sdateFormat=sdateFormat.toString();
    var stimeFormat=timepattern.exec(format);
    if (stimeFormat!=null)
        stimeFormat=stimeFormat.toString().replace(/N/gi,"M");
    
    var btimeOnly=false;
    var bshowButtonPanel=true;
    var bshowTimepicker=true;
    var sonSelect = '';
    var sonClose = '';
    var balwaysSetTime = true;
    
    // Validate input. Necessary because datepicker also allows keyboard entry.
    $(this).blur(function() {
        validateInput(basename);
    });
    
    //Configure the layout of the picker according to the format of the field
    if (stimeFormat==null) // no time component in mask: switch off timepicker
    {
        stimeFormat="HH:MM";
        bshowButtonPanel=false;
        bshowTimepicker=false;
        balwaysSetTime=false;
        
        //need this to close datetimepicker on selection of a date (mimics date picker)
        sonSelect = function () {$('#answer'+basename).datetimepicker('hide');};
        
        if (!(sdateFormat.match('d'))) // no day: switch off "calender"
        {
            bshowButtonPanel=true;
        
            sonClose = function(dateText, inst) {
                        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                        $(this).val($.datepicker.formatDate(sdateFormat, new Date(year, month, 1)));
                    }

            $(this).click(function () {
                $(".ui-datepicker-calendar").hide();
                $("#ui-datepicker-div").position({
                    my: "center top",
                    at: "right top",
                    of: $(this)
                });
            });
  
            $(this).focus(function () {
                $(".ui-datepicker-calendar").hide();
                $("#ui-datepicker-div").position({
                    my: "center top",
                    at: "right top",
                    of: $(this)
                });
            });
        }
    }
    else if (sdateFormat==null)
    {
        var sdateFormat="";
        btimeOnly=true;
    }
 
    // set minimum and maximum dates for calender
    datemin=$('#datemin'+basename).text();
    datemax=$('#datemax'+basename).text();

    return {
        // set minimum and maximum date
        // remove the time component for Firefox
        minDate: Date.parseString(datemin.substr(0,10), "yyyy-mm-dd"),
        maxDate: Date.parseString(datemax.substr(0,10), "yyyy-mm-dd"),
        yearRange: datemin.substr(0,4)+':'+datemax.substr(0,4),
        //set the other options so datetimepicker is either a datepicker or a timepicker or both
        showTimepicker: bshowTimepicker,
        timeOnly: btimeOnly,
        showButtonPanel: bshowButtonPanel,
        alwaysSetTime: balwaysSetTime,
        onSelect: sonSelect,
        dateFormat: sdateFormat,
        timeFormat: stimeFormat,
        onClose: sonClose
   };
}

function validateInput(basename) 
{
    if(typeof showpopup=="undefined"){showpopup=1;}
    format=$('#dateformat'+basename).val();
    answer=$('#answer'+basename).val();
    //only validate if the format mask says it's a complete date and only a date
    var str_regexp = /^[mydYD]{1,4}[-.\s\/][mydYD]{1,4}[-.\/\s][mydYD]{1,4}$/; 
    var pattern = new RegExp(str_regexp); 
    if (format.match(pattern)!=null)
    {
        try
        {
            newvalue=jQuery.datepicker.parseDate(format, answer);
        }
        catch(error)
        {
            if(showpopup)
            {
                $('#answer'+basename).datetimepicker('hide');
                alert(translt.alertInvalidDate);
            }
            $('#answer'+basename).val("");
        }
    }
}



function dateUpdater() {
    if(this.id.substr(0,3)=='yea')
    {
        thisid=this.id.substr(4);
    }
    else if(this.id.substr(0,3)=='mon')
    {
        thisid=this.id.substr(5);
    }
    else if(this.id.substr(0,3)=='day')
    {
        thisid=this.id.substr(3);
    }
    else if(this.id.substr(0,3)=='hou')
    {
        thisid=this.id.substr(4);
    }
    else if(this.id.substr(0,3)=='min')
    {
        thisid=this.id.substr(6);
    }

    if ((!$('#year'+thisid).length || $('#year'+thisid).val()=='') &&
        (!$('#month'+thisid).length || $('#month'+thisid).val()=='') &&
        (!$('#day'+thisid).length || $('#day'+thisid).val()=='') &&
        (!$('#hour'+thisid).length || $('#hour'+thisid).val()=='') &&
        (!$('#minute'+thisid).length || $('#minute'+thisid).val()==''))
    {
        //nothing filled in
        $('#qattribute_answer'+thisid).val('');
        $('#answer'+thisid).val('');
        $('#answer'+thisid).change();
    }
    else if (($('#year'+thisid).length && $('#year'+thisid).val()=='') ||
        ($('#month'+thisid).length && $('#month'+thisid).val()=='') ||
        ($('#day'+thisid).length && $('#day'+thisid).val()=='') ||
        ($('#hour'+thisid).length && $('#hour'+thisid).val()=='') ||
        ($('#minute'+thisid).length && $('#minute'+thisid).val()==''))
        {
            //incomplete
            $('#qattribute_answer'+thisid).val(translt.infoCompleteAll);
            $('#answer'+thisid).val('INVALID');
            $('#answer'+thisid).change();
        }
        else
        {
            if (!$('#year'+thisid).val())
            {
                iYear='1900';
            }
            else
            {
                iYear=$('#year'+thisid).val(); 
            }
            if (!$('#month'+thisid).val())
            {
                iMonth='01';
            }
            else
            {
                iMonth=$('#month'+thisid).val(); 
            }
            if (!$('#day'+thisid).val())
            {
                iDay='01';
            }
            else
            {
                iDay=$('#day'+thisid).val(); 
            }
            if (!$('#hour'+thisid).val())
            {
                iHour='00';
            }
            else
            {
                iHour=$('#hour'+thisid).val(); 
            }            
            if (!$('#minute'+thisid).val())
            {
                iMinute='00';
            }
            else
            {
                iMinute=$('#minute'+thisid).val(); 
            }
            ValidDate(this,iYear+'-'+iMonth+'-'+iDay);
            parseddate=Date.parseString(trim(iDay+'-'+iMonth+'-'+iYear+' '+iHour+':'+iMinute), 'dd-mm-yy H:M');
            parseddate=parseddate.format($('#dateformat'+thisid).val());
            $('#answer'+thisid).val(parseddate); 
            $('#answer'+thisid).change();
            $('#qattribute_answer'+thisid).val('');
        }
}

function pad (str, max) {
    return str.length < max ? pad("0" + str, max) : str;
}

function ValidDate(oObject, value) {// Regular expression used to check if date is in correct format
    if(typeof showpopup=="undefined"){showpopup=1;}
    var str_regexp = /[1-9][0-9]{3}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/; 
    var pattern = new RegExp(str_regexp); 
    if ((value.match(pattern)!=null)) 
    {
        var date_array = value.split('-'); 
        var day = date_array[2]; 
        var month = date_array[1]; 
        var year = date_array[0]; 
        str_regexp = /1|3|5|7|8|10|12/; 
        pattern = new RegExp(str_regexp); 
        if ( day <= 31 && (month.match(pattern)!=null)) 
        { 
            return true; 
        } 
        str_regexp = /4|6|9|11/; 
        pattern = new RegExp(str_regexp); 
        if ( day <= 30 && (month.match(pattern)!=null)) 
        { 
            return true; 
        } 
        if (day == 29 && month == 2 && (year % 4 == 0)) 
        { 
            return true; 
        } 
        if (day <= 28 && month == 2) 
        { 
            return true; 
        }         
    }
    if(showpopup)
    {
        window.alert(translt.alertInvalidDate);
    }// TODO : use EM and move it to EM
    oObject.focus(); 
    return false; 
} 
