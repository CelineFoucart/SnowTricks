function togglePassword(button, targetSelector) {
    const target = document.querySelector(targetSelector);

    if (!target) {
        return;
    }

    if (target.type === 'password') {
        target.type = 'text';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        target.type = 'password';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}