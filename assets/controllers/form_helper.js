document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[data-error]');

    inputs.forEach(input => {
        const errorText = input.getAttribute('data-error');
        const errorId = input.getAttribute('data-error-id');

        input.addEventListener('invalid', function () {
            this.setCustomValidity(errorText);
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.remove('fr-hidden');
            }
        });

        input.addEventListener('input', function () {
            this.setCustomValidity('');
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.add('fr-hidden');
            }
        });
    });
});
