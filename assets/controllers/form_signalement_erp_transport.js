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
                                $('#rechercheAdresse').attr('aria-describedby', 'rechercheAdresse-error');
                            }

                            if (element.indexOf('isPlaceAvertie') > 0) {
                                $('#signalement_transport_isPlaceAvertie-error').removeClass('fr-hidden');
                                $('#signalement_front_isPlaceAvertie-error').removeClass('fr-hidden');
                            }

                            if (element.indexOf('placeType') > 0) {
                                $('#signalement_front_placeType-error').removeClass('fr-hidden');
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
        $('.fr-upload-group').next().addClass('fr-hidden')

        for (let file of event.target.files) {
            let inputDiv = $('.fr-upload-group .fr-upload');
            if (file.size > 10 * 1024 * 1024) {
                let errorDiv = $('.fr-upload-group').next();
                errorDiv.text('Le fichier est trop lourd. Merci d\'ajouter une photo de moins de 10 Mo.').removeClass('fr-hidden');
                inputDiv.attr('aria-describedby', 'file-upload-error');
                break;
            } else if(file.type !== 'image/jpeg' && file.type !== 'image/png') {
                let errorDiv = $('.fr-upload-group').next();
                errorDiv.text('Format de fichier non supporté. Merci de choisir un fichier au format jpg ou png.').removeClass('fr-hidden');
                inputDiv.attr('aria-describedby', 'file-upload-error');
                break;
            } else {
                let filename = file.name;
                let imgSrc = URL.createObjectURL(file);
                let strAppend = '<div class="fr-col-6 fr-col-md-3 align-center">';
                strAppend += '<img src="' + imgSrc + '" width="100" height="100">';
                strAppend += '<br><button type="button" data-filename="' + filename  +'" class="fr-link fr-icon-close-circle-line fr-link--icon-left link--error file-uploaded"> Supprimer </button>';
                strAppend += '</div>';
                $('.fr-front-signalement-photos').append(strAppend);
                filesUploaded.push(file);
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
