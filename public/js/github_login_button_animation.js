"use strict";

window.addEventListener('DOMContentLoaded', () => {
    let login_buttons = document.querySelectorAll('.github-login-button-group button[type="submit"]');
    let i;
    for (i = 0; i < login_buttons.length; ++i) {
        let login_button = login_buttons[i];
        if (login_button) {
            login_button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                submitForm(login_button);

                return false;
            });
        }
    }

    /**
     * Add an animation class to the submit button
     * @param login_button
     * @returns {boolean}
     */
    function submitForm(login_button) {
        if (login_button.classList.contains('button--loading')) {
            // Prevent multiple form submits
            return false;
        }

        login_button.classList.add('button--loading');
        login_button.setAttribute('disabled', '');

        window.setTimeout(() => {
            let formBe = login_button.closest('form.github-login-form-backend');
            let formFe = login_button.closest('form.github-login-form-frontend');

            if (formBe) {
                formBe.submit();
            }

            if (formFe) {
                formFe.submit();
            }

        }, 1000);
    }

});
