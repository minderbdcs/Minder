/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 09.01.12
 * Time: 15:40
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function(){

    function ping()
    {
        $.ajax({
            url:        "/minder/user/ping",
            success:    function(data){
                setTimeout(function(){ ping(); }, 60000);
            },
            error:      function(data){
                setTimeout(function(){ ping(); }, 60000);
            }
        });
    }

    ping();
});

