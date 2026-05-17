document.addEventListener('DOMContentLoaded', () => {
    const inputPassword = document.querySelector('input[name="contrasena"]');
    const btnVisualizar = document.querySelector('.password-visualizar');
    const icon = btnVisualizar.querySelector('iconify-icon');

    btnVisualizar.addEventListener('click', () => {
        if (inputPassword.type === 'password') {
            inputPassword.type = 'text';
            icon.setAttribute('icon', 'iconoir:eye-closed');
        } else {
            inputPassword.type = 'password';
            icon.setAttribute('icon', 'iconoir:eye');
        }
    });
});
