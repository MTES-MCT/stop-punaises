import $ from 'jquery';
$(function() {
    sendMessage();
    displayTabMessages();
    scrollDownMessageList();
});

function sendMessage() {
    const form = $('#form-send-message')
    form.on('submit', function(event) {
        event.preventDefault();
        $('#form-send-message button').attr('disabled', true);
        const formElement = form[0];
        const formData = new FormData(formElement);
        formData.set('message', formData.get('message').replaceAll(/(<([^>]+)>)/gi, ""));
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formData,
            contentType:false,
            cache:false,
            processData:false,

            success: function(data) {
                const message = JSON.parse(data);
                const messageItem = `
                        <div class="message-item">
                            <div class="message-item-header">
                                <p class="fr-m-1w message-item-date">${message.createdAt}</p>
                                <p class="fr-m-1w fr-text--sm"><strong>Vous</strong></p>
                            </div>
                            <div class="message-item-content">
                                <p class="fr-p-1w bg-blue">${message.content.replace (/\n/g, "<br />")}</p>
                            </div>
                        </div>`
                const messageList = $('.message-list');
                messageList.append(messageItem);
                messageList.scrollTop(messageList.prop("scrollHeight"));
                formElement.reset();
                $('.message-confirmation')
                    .addClass('fr-alert--success')
                    .removeClass('fr-alert--warning')
                    .removeClass('fr-hidden');
                $('.message-confirmation .fr-alert__title').text('Message envoyé à l\'usager.');
                clearAlertMessage();
                $('#form-send-message button').attr('disabled', false);
            },
            error: function(xhr) {
                $('.message-confirmation')
                    .addClass('fr-alert--warning')
                    .removeClass('fr-hidden');
                $('.message-confirmation .fr-alert__title')
                    .text(400 === xhr.status ?
                        xhr.responseJSON.message :
                        'Une erreur est survenue, merci de réessayer plus tard.'
                    );
                clearAlertMessage();
                $('#form-send-message button').attr('disabled', false);
            }
        })
    });
}

function displayTabMessages() {
    $('#btn-send-message').on('click', function() {
        $('.fiche-signalement #tabpanel-messages').click();
    });
}

function scrollDownMessageList() {
    $('.fiche-signalement #tabpanel-messages').on('click', () => {
        const messageList = $('.message-list');
        messageList.scrollTop(messageList.prop("scrollHeight"));
    })
}

function clearAlertMessage() {
    setTimeout(() => {
        $('.message-confirmation')
            .removeClass('fr-alert--success')
            .removeClass('fr-alert--warning')
            .addClass('fr-hidden');
    }, 5000);
}


