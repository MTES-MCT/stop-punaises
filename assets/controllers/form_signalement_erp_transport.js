import $ from 'jquery';

$(function () {
    if ($('div.signalement-punaises').length > 0) {
        $('.signalement-punaises-success').hide();
        sendSignalement();
        handleFileUpload();
    }
});

function sendSignalement() {
    const form = $('.signalement-punaises form');
    form.on('submit', function (event) {
        event.preventDefault();
        $('.fr-error-text').addClass('fr-hidden');
        $('.signalement-punaises form [type=submit]').attr('disabled', true);
        const formElement = form[0];
        const formData = new FormData(formElement);
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                $('.signalement-punaises').hide();
                $('.signalement-punaises-success').show();
                return;
            },
            error: function (xhr) {
                console.log(xhr.status);
                if (400 === xhr.status) {
                    console.log(xhr.responseJSON.error);
                    xhr.responseJSON.error.forEach((element) => {
                       $('[name="' + element + '"]').next().removeClass('fr-hidden');
                       $('[name="' + element + '"]').closest('.fr-input-group').find('.fr-error-text').removeClass('fr-hidden');
                    });
                }
            },
            complete: function(xhr) {
                $('[type=submit]').removeAttr('disabled');
            }
        })
    });
}

function handleFileUpload() {
    $('#file-upload').on('change', function(event) {
        $('.fr-front-signalement-photos').empty();
        for (let i = 0; i < event.target.files.length; i++) {
            let imgSrc = URL.createObjectURL(event.target.files[i]);
            let strAppend = '<div class="fr-col-6 fr-col-md-3" style="text-align: center;">';
            strAppend += '<img src="' + imgSrc + '" width="100" height="100">';
            strAppend += '</div>';
            $('.fr-front-signalement-photos').append(strAppend);
        }
    });
}