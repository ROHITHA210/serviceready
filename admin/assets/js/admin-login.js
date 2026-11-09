const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

const eyeIcon = `
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>
`;

const eyeOffIcon = `
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="m3 3 18 18"/>
        <path d="M10.6 5.1A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8"/>
        <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7a10 10 0 0 0 4-.8"/>
        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
    </svg>
`;

togglePassword.addEventListener('click', () => {
    const isPasswordHidden = passwordInput.type === 'password';

    passwordInput.type = isPasswordHidden ? 'text' : 'password';

    togglePassword.innerHTML = isPasswordHidden
        ? eyeOffIcon
        : eyeIcon;

    togglePassword.setAttribute(
        'aria-label',
        isPasswordHidden ? 'Hide password' : 'Show password'
    );

    togglePassword.setAttribute(
        'aria-pressed',
        String(isPasswordHidden)
    );
});
