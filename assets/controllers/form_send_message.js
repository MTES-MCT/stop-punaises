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
                $('.message-list').append(messageItem);
                form[0].reset();
            },
            error: function(xhr, desc, err) {
                console.log(xhr, desc, err);
            }
        })
    });
}
