var
    Const = {
       
        MESSAGES: 'messages',
        WARNINGS: 'warnings',
        ERRORS:   'errors'
    };

function frmSubmit(id)
{
    if (id) {
        document.getElementById(id + '-form').submit();
    } else {
        document.forms.main.submit();
    }
    return true;
}

function onAjaxSuccess()
{
  //$.unblockUI();  
  return true;
}
function onAjaxStop()
{
    //$.unblockUI();
    return true;
}

function onAjaxError(src, evt)
{
  //$.unblockUI();
 // alert("Error"); 
 // return true;
}
function onAjaxBeforeSend()
{
  var elem = $("#wait").clone();
  //$.blockUI(elem);  
  return true;
}

/**
 *  
 */
function selectAll(element) {
    // select list of checkboxes
    var chk = $('table tbody input:checkbox', '#' + $(element).parent().parent().parent().parent().parent().get(0).id);
    var checkOnOff = element.checked;
    for (i=0; i < chk.length; i++) {
        chk[i].checked = checkOnOff;
    }
    showSelected(element, 'auto');
}

/**
 * Receives list of checkboxes and count of checked item
 * 
 * @return integer
 */
function countSelected(list) {
    var selectedNum = 0;
        
    for (i=0; i < list.length; i++) {
        if (list[i].checked) {
            selectedNum++;
        };
    }
    
    return selectedNum;
}

/**
 * Updates selected line counter. 
 * Counter placement should lie in container by 5 level higher e.g. "<container><table><tbody><tr><td>element"
 * and identified by id="selected_num".
 * 
 * Also calls (if exists) calcOther() function for additional counters.
 *
 * So, sample structure 
 * <div>
 *   <span id="selected_num"></span>  
 *   <table>
 *     <thead></thead>
 *     <tbody>
 *       <tr>
 *         <td>
 *           Element here... 
 *         </td>
 *         ... Other columns ...       
 *         <td>
 *         </td>
 *       </tr>
 *       ... Other rows ...       
 *       <tr>...</tr>
 *     </tbody>
 *   </table>
 * </div>  
 *
 * @return true 
 */
function showSelected(element, method)
{
    if (element) {
        if (typeof element == 'object') {
            var chk = $('table tbody input:checkbox', '#' + $(element).parent().parent().parent().parent().parent().get(0).id);
            selectedNum = countSelected(chk);
            $("#selected_num", '#' + $(element).parent().parent().parent().parent().parent().get(0).id).get(0).innerHTML = selectedNum;
        }
        if ( selectedNum == chk.length) {
            $("#select_all", '#' + $(element).parent().parent().parent().parent().get(0).id).get(0).checked = true;
        } else {
            $("#select_all", '#' + $(element).parent().parent().parent().parent().get(0).id).get(0).checked = false;
        }    
        
        $("#selected_num", '#' + $(element).parent().parent().parent().parent().parent().get(0).id).get(0).innerHTML = selectedNum;
        if ('function' == typeof calcOther) {
            calcOther(element, method);
        }
    }
    return true;
}

function savePosition()
{
    $.getJSON(arguments[0],
        {'table_id': arguments[3], 'ctrl': arguments[1], 'act': arguments[2], 'src': arguments[4], 'dst': arguments[5]});
}

function getTimeForDatePicker(){
    
    date_obj        = new Date();
    date_obj_hours  = date_obj.getHours();
    date_obj_mins   = date_obj.getMinutes();
    date_obj_sec    = date_obj.getSeconds();

    if (date_obj_mins < 10) { 
        date_obj_mins = "0" + date_obj_mins;
    }
    
    if(date_obj_sec < 10) {
        date_obj_sec = "0" + date_obj_sec;     
    }
    
    date_obj_time = " '" + date_obj_hours + ":" + date_obj_mins + ":" + date_obj_sec + "'";
    
    return date_obj_time; 
}

function getDefaultMessageContainer(messageType) {
    var
        container = $('ul.' + messageType);

    if(container.length < 1){
        container = $('<ul class="' + messageType + '"></ul>');
        $('#page').prepend(container);
    }

    return container;
}

function showResponseMessages(response) {

removeMessages();
removeErrors();   
removeWarnings();
 
    if (response.messages && response.messages.length > 0) {
        showMessage(response.messages);
    }

    if (response.warnings && response.warnings.length > 0) {
        showWarnings(response.warnings);
    }

    if (response.errors && response.errors.length > 0) {
        showErrors(response.errors);
    }
}

function showMessage(messages, container){
    //timeOut = (timeOut) ? timeOut : Const.DEFAULT_MESSAGE_TIMEOUT;
    container = container ? container : getDefaultMessageContainer(Const.MESSAGES);

    var $html = $(messages.map(function(message){return '<li>' + message + '</li>'}).join(''));

    container.append($html).show();
    /*setTimeout(
        function(){
            $html.remove();
            if (container.children().length < 1) {
                container.remove();
            }
        },
        timeOut
    );*/
}

function removeMessages(){
    $('ul.messages').remove();    
}

function showErrors(errors,  container){

    container = container ? container : getDefaultMessageContainer(Const.ERRORS);
    showMessage(errors, container);
}

function removeErrors(){
    $('ul.errors').remove();    
}

function showWarnings(warnings, container) {
    container = container ? container : getDefaultMessageContainer(Const.WARNINGS);
    showMessage(warnings, container);
}

function removeWarnings(){
    $('ul.warnings').remove();
}

function sortFieldsByTabindex(a, b) {
    return ($(a).attr('tabindex') - $(b).attr('tabindex'));
}

function moveNextOnEnter(evt) {
    if (evt.keyCode == 13) {
        evt.preventDefault();
        
        if (evt.data.fieldsScopeSel) {
            fields = $(evt.data.fieldsScopeSel);
            fields.sort(sortFieldsByTabindex);
            
            index  = fields.index(this);
            if (index === -1)
                return false;
            
            oldField = $(fields[index]);
            while (fields[index + 1] && $(fields[index + 1]).attr('disabled'))
                index ++;
                
            if (fields[index + 1]) {
                nextField = $(fields[index + 1]);
                nextField.focus();
                nextField.trigger('select');
            }
        }
        return false;
    }
}
    
