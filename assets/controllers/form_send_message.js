import $ from 'jquery';
$(function() {
    sendMessage();
});

function sendMessage() {
    const form = $('.form');
    form.on('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(form[0]);
        $.ajax({
            type: 'POST',
            url: $('.form').attr('action'),
            data: formData,
            contentType:false,
            cache:false,
            processData:false,

            success: function(data, status, xhr) {
                const message = JSON.parse(data);
                const messageItem = `
                        <div class="message-item">
                            <div class="message-item-header">
                                <p class="fr-m-1w message-item-date">${message.createdAt}</p>
                                <p class="fr-m-1w fr-text--sm"><strong>Vous</strong></p>
                            </div>
                            <div class="message-item-content">
                                <p class="fr-p-1w">${message.content}</p>
                            </div>
                        </div>`
                const messageList = $('.message-list');
                messageList.append(messageItem);
                messageList.scrollTop(messageList.prop("scrollHeight"))
                form[0].reset();
                $('.message-confirmation')
                    .addClass('fr-alert--success')
                    .removeClass('fr-alert--warning')
                    .removeClass('fr-hidden');
                $('.message-confirmation .fr-alert__title').text('Message envoyé à l\'usager.');
                clearAlertMessage();
            },
            error: function(xhr, desc, err) {
                $('.message-confirmation')
                    .addClass('fr-alert--warning')
                    .removeClass('fr-hidden');
                $('.message-confirmation .fr-alert__title')
                    .text('Une erreur est survenue, merci de réessayer plus tard.');
                clearAlertMessage();
            }
        })
    });
}

function clearAlertMessage() {
    setTimeout(() => {
        $('.message-confirmation')
            .removeClass('fr-alert--success')
            .removeClass('fr-alert--warning')
            .addClass('fr-hidden');
    }, 5000);
}
