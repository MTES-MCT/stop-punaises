import $ from 'jquery';

let filesUploaded = [];

$(function () {
    if ($('div.signalement-punaises').length > 0) {
        $('.signalement-punaises-success').hide();
        sendSignalement();
        handleFileUpload();
        handleBehaviourRadio();
        deleteFileUploaded();
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

        let index = 0;
        formData.delete('file-upload[]');
        for (const fileInput of filesUploaded) { // make sure all files will be submitted (upload one by one or multiple)
            formData.append('file-upload[]', fileInput);
            index++;
        }

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
                if (400 === xhr.status) {
                    const errors = xhr.responseJSON.error;

                    let index = 0;
                    for (let element in errors) {
                        if (errors.hasOwnProperty(element)) {
                            const elementField = $('[name="' + element + '"]');
                            if (index === 0) {
                                if (element.indexOf('adresse') === -1) {
                                    elementField[0].focus(); // Focus on the first error
                                } else if (element.indexOf('adresse') > 0) {
                                    $('#rechercheAdresse')[0].focus();
                                }
                            }

                            elementField
                                .closest('.fr-input-group, .fr-select-group')
                                .find('.fr-error-text')
                                .text(errors[element])
                                .removeClass('fr-hidden');

                            elementField
                                .next()
                                .removeClass('fr-hidden');

                            if (element.indexOf('adresse') > 0) {
                                $('#rechercheAdresse').next().removeClass('fr-hidden');
                            }
                        }
                        index++;
                    }
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
        $('.fr-upload-group').next().addClass('fr-hidden');

        for (let i = 0; i < event.target.files.length; i++) {
            if (event.target.files[i].size < 10 * 1024 * 1024) {
                let filename = event.target.files[i].name;
                let imgSrc = URL.createObjectURL(event.target.files[i]);
                let strAppend = '<div class="fr-col-6 fr-col-md-3" style="text-align: center;">';
                strAppend += '<img src="' + imgSrc + '" width="100" height="100">';
                strAppend += '<br><button type="button" data-filename="' + filename  +'" class="fr-link fr-icon-close-circle-line fr-link--icon-left link--error file-uploaded"> Supprimer </button>';
                strAppend += '</div>';
                $('.fr-front-signalement-photos').append(strAppend);
                filesUploaded.push(event.target.files[i]);
            } else {
                $('.fr-upload-group').next().removeClass('fr-hidden');
                break;
            }
        }
    });
}

function deleteFileUploaded() {
    $('.fr-front-signalement-photos').on('click', '.file-uploaded', function(event) {
        const fileToDelete = event.target.getAttribute("data-filename");
        filesUploaded = filesUploaded.filter(function (fileUploaded) {
            return fileUploaded.name !== fileToDelete;
        });
        event.target.closest('div').remove();
    });
}

function handleBehaviourRadio() {
    $('input[type=radio]').on('click', function(event) {
        if (event.target.checked) {
            $(this).parent().siblings().removeClass('is-checked');
            $(this).parent().addClass('is-checked');
        } else {
            $(this).parent().removeClass('is-checked');
        }
    })
}
