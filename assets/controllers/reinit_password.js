document?.querySelector('form[name="login-creation-mdp-form"]')?.querySelectorAll('[name^="password"]').forEach(pwd => {
    pwd.addEventListener('input', canSubmitFormReinitPassword)
})
document?.querySelector('form[name="login-creation-mdp-form"]')?.addEventListener('submit', (event) => {
    event.preventDefault();
    if(canSubmitFormReinitPassword()){
        event.target.submit();
    }
})

function canSubmitFormReinitPassword() {
    const pass = document?.querySelector('form[name="login-creation-mdp-form"] #login-password').value;
    const repeat = document?.querySelector('form[name="login-creation-mdp-form"] #login-password-repeat').value;
    const pwdMatchError = document?.querySelector('form[name="login-creation-mdp-form"] #password-match-error');
    const submitBtn = document?.querySelector('form[name="login-creation-mdp-form"] #submitter');
    const messageLength = document?.querySelector('form[name="login-creation-mdp-form"] #password-input-message-info-length');
    const messageMaj = document?.querySelector('form[name="login-creation-mdp-form"] #password-input-message-info-maj');
    const messageMin = document?.querySelector('form[name="login-creation-mdp-form"] #password-input-message-info-min');
    const messageNb = document?.querySelector('form[name="login-creation-mdp-form"] #password-input-message-info-nb');
    const messageSpecial = document?.querySelector('form[name="login-creation-mdp-form"] #password-input-message-info-special');
    const groupInputPassword = document?.querySelector('form[name="login-creation-mdp-form"] .fr-input-group-password');
    const groupInputPasswordRepeat = document?.querySelector('form[name="login-creation-mdp-form"] .fr-input-group-password-repeat');
    let canSubmit = true;
    pwdMatchError.classList.add('fr-hidden')
    submitBtn.disabled = false;
    if (pass !== repeat) {
        canSubmit = false;
        pwdMatchError.classList.remove('fr-hidden')
    }
    if (pass.length < 8) {
        messageLength.classList.remove('fr-message--info', 'fr-message--valid')
        messageLength.classList.add('fr-message--error')
        canSubmit = false;
    }else{
        messageLength.classList.remove('fr-message--info', 'fr-message--valid')
        messageLength.classList.add('fr-message--valid')
    }
    if (!/[A-Z]/.test(pass)) {
        messageMaj.classList.remove('fr-message--info', 'fr-message--valid')
        messageMaj.classList.add('fr-message--error')
        canSubmit = false;
    }else{
        messageMaj.classList.remove('fr-message--info', 'fr-message--valid')
        messageMaj.classList.add('fr-message--valid')
    }
    if(!/[a-z]/.test(pass)){
        messageMin.classList.remove('fr-message--info', 'fr-message--valid')
        messageMin.classList.add('fr-message--error')
        canSubmit = false;
    }else{
        messageMin.classList.remove('fr-message--info', 'fr-message--valid')
        messageMin.classList.add('fr-message--valid')
    }
    if(!/[0-9]/.test(pass)){
        messageNb.classList.remove('fr-message--info', 'fr-message--valid')
        messageNb.classList.add('fr-message--error')
        canSubmit = false;
    }else{
        messageNb.classList.remove('fr-message--info', 'fr-message--valid')
        messageNb.classList.add('fr-message--valid')
    }
    if(!/[^a-zA-Z0-9]/.test(pass)){
        messageSpecial.classList.remove('fr-message--info', 'fr-message--valid')
        messageSpecial.classList.add('fr-message--error')
        canSubmit = false;
    }else{
        messageSpecial.classList.remove('fr-message--info', 'fr-message--valid')
        messageSpecial.classList.add('fr-message--valid')
    }
    if(!canSubmit){
        groupInputPassword.classList.add('fr-input-group--error')
        groupInputPasswordRepeat.classList.add('fr-input-group--error')
        submitBtn.disabled = true;
    }else{
        groupInputPassword.classList.remove('fr-input-group--error')
        groupInputPasswordRepeat.classList.remove('fr-input-group--error')
    }
    return canSubmit;
}