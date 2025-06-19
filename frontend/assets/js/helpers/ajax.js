import { showErrorToast } from './toast.js';

export function defineGlobalAjaxErrorHandler() {
    if (window.jQuery) {
        $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
            if (jqxhr && jqxhr.responseJSON && jqxhr.responseJSON.message) {
                showErrorToast(jqxhr.responseJSON.message);
            } else {
                showErrorToast('Bilinmeyen bir AJAX hatası oluştu.');
            }
        });
    }
} 