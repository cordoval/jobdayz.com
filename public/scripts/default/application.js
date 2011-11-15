$('document').ready(function () {
    var pnlLogin = $('#pnlLogin');

    $('#eventDate').calendricalDate();    
    $('#startTime, #endTime').calendricalTime();
    
    $('#linkClose, #loginRegister').click(function(e){
        pnlLogin.slideToggle();
        return false;
    });
    
    $('#formRegister').submit(function(){
        var er = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
        if( !er.test( $('#emailUp').val()) ){
            alert('Ingrese un email valido');
            $('#emailUp').focus();
            return false;
        }

        $('#pnlLogin').block();
        $.post(this.action, $(this).serialize(), function(response){
            if( response && response.status){
                window.location.href = response.redirect;
            } else {
                alert(response.message);
            }
            $('#pnlLogin').unblock();
        }, 'json');
        return false;
    });
    
    $('#formLogin').submit(function(){
        $('#pnlLogin').block();
        $.post(this.action, $(this).serialize(), function(response){
            if( response && response.status){
                window.location.href = response.redirect;
            } else {
                alert(response.message);
            }
            $('#pnlLogin').unblock();
        }, 'json');
        return false;
    });
});
