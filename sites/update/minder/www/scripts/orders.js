/*
var ajaxCounter = 0;
function onAjaxSuccess()
{
    ajaxCounter--;
    if (!ajaxCounter) {
        $.unblockUI();    
    }
    return true;
}
function onAjaxError(src, evt)
{
    ajaxCounter--;
    if (!ajaxCounter) {
        $.unblockUI();    
    }
    alert("Error");    
    return true;
}
function onAjaxBeforeSend()
{
    var elem = $("#wait").clone();
    if (!ajaxCounter) {
        $.blockUI(elem);    
    }
    ajaxCounter++;
    return true;
}
function frmSubmit()
{
    document.forms.main.submit();
    return true;
}
*/
