let errorToastTimeout;
let successToastTimeout;

export function showErrorToast(message) {
    const errorToast = document.getElementById('errorToast');
    if (!errorToast) return;
    const messageElement = document.getElementById('errorToastMessage');
    if (messageElement) messageElement.textContent = message;
    errorToast.classList.remove('translate-x-full', 'hidden');
    errorToast.classList.add('translate-x-0');
    clearTimeout(errorToastTimeout);
    errorToastTimeout = setTimeout(() => {
        errorToast.classList.remove('translate-x-0');
        errorToast.classList.add('translate-x-full', 'hidden');
    }, 3000);
}

export function showSuccessToast(message) {
    const successToast = document.getElementById('successToast');
    if (!successToast) return;
    const messageElement = document.getElementById('successToastMessage');
    if (messageElement) messageElement.textContent = message;
    successToast.classList.remove('translate-x-full', 'hidden');
    successToast.classList.add('translate-x-0');
    clearTimeout(successToastTimeout);
    successToastTimeout = setTimeout(() => {
        successToast.classList.remove('translate-x-0');
        successToast.classList.add('translate-x-full', 'hidden');
    }, 3000);
} 