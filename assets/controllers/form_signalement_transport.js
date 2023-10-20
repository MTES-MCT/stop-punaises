import $ from 'jquery';

$(function () {
    if ($('div.signalement-punaises').length > 0) {
        sendSignalement();
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
                const message = JSON.parse(data);
                console.log(message);
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
            }
        })
    });

    form.on('click', function (event) {
        $('[type=submit]').removeAttr('disabled');
    })
}
