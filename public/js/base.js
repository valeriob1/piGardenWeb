/**
 * Created by lejubila on 26/08/16.
 */
function checkExitsElement(arr) {
    var i, max_i;
    for (i = 1, max_i = arguments.length; i < max_i; i++) {
        try{
            arr = arr[arguments[i]];
            if (arr === undefined) {
                return false;
            }
        } catch(err) {
            return false;
        }
    }
    return true;
}

function updateElement(id, data, attr){
    attr = attr || '';
    if($(id).length > 0){
        if(attr == ''){
            if($($(id).get(0)).text() != data){
                $($(id).get(0)).text(data);
            }
        } else {
            if($($(id).get(0)).attr(attr) != data){
                $($(id).get(0)).attr(attr, data);
            }
        }
    }
}

function updateZones(status){
    if(!(typeof status['zones'] === undefined)){
        $.each(status['zones'],function(i,zone){
            updateElement('#btn-zone-'+zone.name, zone.actionHref, 'href');
            updateElement('#btn-zone-'+zone.name+' i', 'fa '+zone.actionButtonClass, 'class');
            updateElement('#btn-zone-'+zone.name+' .button-zone-text', zone.actionButtonText);
            updateElement('#btn-zone-image-'+zone.name, zone.imageSrc, 'src');
            updateElement('.link-zone-'+zone.name+' i', (zone.state == 0 ? 'fa fa-toggle-off' : 'fa fa-toggle-on'), 'class');

            // Card state: badge text/colour, card highlight and button colour.
            // Bootstrap 4 classes — the old markup used AdminLTE/BS3 ones.
            var isOpen = zone.state != 0;
            // Labels come from the blade (translated); fall back so a page that
            // doesn't define them can't throw a ReferenceError here.
            var lblOpen   = (typeof pigardenZoneStateOpen   !== 'undefined') ? pigardenZoneStateOpen   : 'ON';
            var lblClosed = (typeof pigardenZoneStateClosed !== 'undefined') ? pigardenZoneStateClosed : 'OFF';
            var badge  = $('#badge-zone-'+zone.name);
            if (badge.length > 0) {
                badge.text(isOpen ? lblOpen : lblClosed)
                     .toggleClass('badge-success', isOpen)
                     .toggleClass('badge-secondary', !isOpen);
            }
            $('#box-zone-'+zone.name).toggleClass('zone-open', isOpen);
            $('#btn-zone-'+zone.name+', #btn-zone-'+zone.name+'+button.dropdown-toggle')
                .toggleClass('btn-warning', isOpen)
                .toggleClass('btn-success', !isOpen);

            if( zone.cronOpenInText !== null){
                $('#text-btn-zone-open-in-cancel-'+zone.name).html(zone.cronOpenInText);
                if( zone.cronOpenInText != ""){
                    $("#box-zone-"+zone.name+" li.open_in_start").addClass('d-none');
                    $("#box-zone-"+zone.name+" li.open_in_set").removeClass('d-none');
                    $('#btn-zone-'+zone.name+'+button.dropdown-toggle i.fa').addClass('text-danger');
                    $('#wrp-open-in-'+zone.name).removeClass('d-none');
                } else {
                    $("#box-zone-"+zone.name+" li.open_in_start").removeClass('d-none');
                    $("#box-zone-"+zone.name+" li.open_in_set").addClass('d-none');
                    $('#btn-zone-'+zone.name+'+button.dropdown-toggle i.fa').removeClass('text-danger');
                    $('#wrp-open-in-'+zone.name).addClass('d-none');
                }
            }
            var dropdown = $('#btn-zone-'+zone.name+'+button.dropdown-toggle');
            if(dropdown.length > 0 ){
                if(zone.state == 0){
                    dropdown.prop('disabled', false);
                } else {
                    dropdown.prop('disabled', true);
                }
            }
        });
    }
}

function updateSensor(data){
    // vue_sensor only exists where the sensor panel is rendered (the dashboard).
    // Other pages — the zone page — load this script without Vue, so guard it:
    // throwing here aborted the whole AJAX success callback and stopped
    // updateNotify() from ever running on those pages.
    if (typeof vue_sensor === 'undefined' || !vue_sensor) {
        return;
    }
    if (checkExitsElement(data, 'status', 'sensor')){
        vue_sensor.sensor = data.status.sensor;
    }
}

if (typeof Vue !== 'undefined') {
    var vue_sensor = new Vue({
        el: '#vue-sensor',
        data: {
            sensor: null
        }
    });
}

function updateDateTime(status){
    updateElement('#pigarden-date-time', status.date_time);
}

/**
 * Show a toast.
 *
 * This used to call PNotify directly. Backpack 6's CoreUI theme ships Noty
 * instead, so PNotify is undefined and every call threw a ReferenceError:
 * neither piGarden's messages ("Solenoid open") nor request errors ever
 * reached the user — a command that failed looked exactly like one that was
 * still running. Prefer Noty, fall back to PNotify for older themes, and
 * never throw if neither is loaded.
 */
function pigardenNotify(text, type){
    // Noty knows alert/success/error/warning/info; anything else would render
    // an unstyled toast
    var known = ['success', 'error', 'warning', 'info'];
    if (known.indexOf(type) === -1) {
        type = 'info';
    }
    if (typeof Noty !== 'undefined') {
        new Noty({ text: text, type: type, timeout: 5000 }).show();
    } else if (typeof PNotify !== 'undefined') {
        new PNotify({ text: text, type: type, icon: false });
    } else {
        console.warn('[piGarden] ' + type + ': ' + text);
    }
}

function updateNotify(status){
    if(!(typeof status['messages'] === undefined)){
        $.each(status['messages'],function(type, messages){
            $.each(messages,function(i, message){
                pigardenNotify(message, type);
            });
        });
    }
}

function callBackAjaxError(jqXHR, textStatus, errorThrown){
    console.log(jqXHR);
    console.log(textStatus);
    // 'warning' was also written unquoted here, a second ReferenceError on
    // exactly the path that was supposed to report the failure
    pigardenNotify(errorThrown ? errorThrown + ' (' + textStatus + ')' : textStatus, 'warning');
}

(function($){
    $(document).ready(function(){
        $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).parent().siblings().removeClass('open');
            $(this).parent().toggleClass('open');
        });
    });
})(jQuery);

function htmlDecode(input){
    var e = document.createElement('div');
    e.innerHTML = input;
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}
