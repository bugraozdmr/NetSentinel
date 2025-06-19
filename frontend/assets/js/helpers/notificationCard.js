import { escapeHtml, formatDate } from './dom.js';
import { showErrorToast, showSuccessToast } from './toast.js';
import { API_BASE_URL } from '../config.js';

/**
 * Modern bildirim kartı oluşturur
 * @param {Object} notification - Bildirim objesi
 * @param {Object} options - Kart ayarları
 * @returns {HTMLElement} Bildirim kartı elementi
 */
export function createModernNotificationCard(notification, options = {}) {
    const {
        isServerDetail = false,
        showActions = true,
        compact = false,
        onDelete = null,
        onMarkRead = null
    } = options;

    const isUnread = notification.status === "unread" || notification.is_read === false;

    // Ana kapsayıcı
    const card = document.createElement('div');
    card.className = `notification-card relative bg-slate-800/90 rounded-2xl shadow-xl border transition-all duration-300 group overflow-hidden ${isUnread
        ? 'border-blue-500/60 bg-gradient-to-br from-slate-800/95 to-blue-900/20 hover:shadow-blue-500/20'
        : 'border-slate-700/60 hover:shadow-lg'
        } ${compact ? 'p-4' : 'p-6'}`;
    card.setAttribute('data-notification-id', notification.id);

    // Yeni bildirim badge'i
    if (isUnread) {
        const badge = document.createElement('div');
        badge.className = 'absolute bottom-4 right-4 bg-gradient-to-r from-pink-500 to-red-400 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-md ring-2 ring-white z-10 animate-pulse';
        badge.textContent = 'Yeni';
        card.appendChild(badge);
    }

    // İçerik kapsayıcısı
    const content = document.createElement('div');
    content.className = 'flex items-start gap-4';

    // İkon bölümü
    const iconContainer = document.createElement('div');
    iconContainer.className = `flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-slate-700/80 shadow-lg ${getNotificationIconClass(notification)
        }`;
    iconContainer.innerHTML = getNotificationIcon(notification);
    content.appendChild(iconContainer);

    // Ana içerik
    const mainContent = document.createElement('div');
    mainContent.className = 'flex-1 min-w-0';

    // Başlık ve zaman
    const header = document.createElement('div');
    header.className = 'flex items-start justify-between gap-3 mb-0';

    const time = document.createElement('p');
    time.className = 'text-xs text-slate-500 mt-1';
    time.textContent = formatDate(notification.created_at);
    header.appendChild(time);

    // Aksiyon butonları
    if (showActions) {
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200';

        // Okundu işaretle butonu (sadece okunmamış bildirimler için)
        if (isUnread) {
            const markReadBtn = document.createElement('button');
            markReadBtn.className = 'p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition-all duration-200';
            markReadBtn.title = 'Okundu olarak işaretle';
            markReadBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            actions.appendChild(markReadBtn);
        }

        // Sil butonu
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-all duration-200';
        deleteBtn.title = 'Sil';
        deleteBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        `;
        actions.appendChild(deleteBtn);

        header.appendChild(actions);
    }

    mainContent.appendChild(header);

    // Mesaj içeriği
    if (notification.message) {
        const message = document.createElement('p');
        message.className = `text-slate-300 leading-relaxed ${compact ? 'text-sm' : 'text-base'}`;
        message.textContent = escapeHtml(notification.message);
        mainContent.appendChild(message);
    }

    // Bildirim türü badge'i
    if (notification.notification_type) {
        const typeBadge = document.createElement('span');
        typeBadge.className = `inline-block text-xs font-semibold px-2 py-1 rounded-lg mt-2 ${getTypeBadgeClass(notification.notification_type)}`;
        typeBadge.textContent = getTypeBadgeText(notification.notification_type);
        mainContent.appendChild(typeBadge);
    }

    // Sunucu bilgisi
    if (notification.server_name) {
        const serverInfo = document.createElement('div');
        serverInfo.className = 'mt-3 pt-3 border-t border-slate-700/50';
        serverInfo.innerHTML = `
            <span class="text-xs text-slate-500">Sunucu: </span>
            <span class="text-xs text-blue-400 font-medium">${escapeHtml(notification.server_name)}</span>
        `;
        mainContent.appendChild(serverInfo);
    }

    content.appendChild(mainContent);
    card.appendChild(content);

    return card;
}

/**
 * Bildirim türüne göre ikon sınıfını döndürür
 */
function getNotificationIconClass(notification) {
    switch (notification.notification_type) {
        case 'first_down':
            return 'text-red-400 bg-red-900/20';
        case 'repeated_down':
            return 'text-orange-400 bg-orange-900/20';
        case 'long_term_down':
            return 'text-red-500 bg-red-900/30';
        case 'status_change':
        default:
            return 'text-green-400 bg-green-900/20';
    }
}

/**
 * Bildirim türüne göre ikonu döndürür
 */
function getNotificationIcon(notification) {
    switch (notification.notification_type) {
        case 'first_down':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>`;
        case 'repeated_down':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`;
        case 'long_term_down':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>`;
        case 'status_change':
        default:
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`;
    }
}

/**
 * Bildirim türüne göre badge sınıfını döndürür
 */
function getTypeBadgeClass(type) {
    switch (type) {
        case 'first_down':
            return 'text-red-300 bg-red-900/50';
        case 'repeated_down':
            return 'text-orange-300 bg-orange-900/50';
        case 'long_term_down':
            return 'text-red-300 bg-red-900/50';
        case 'status_change':
        default:
            return 'text-green-300 bg-green-900/50';
    }
}

/**
 * Bildirim türüne göre badge metnini döndürür
 */
function getTypeBadgeText(type) {
    switch (type) {
        case 'first_down':
            return 'İlk Düşüş';
        case 'repeated_down':
            return 'Tekrar Düşüş';
        case 'long_term_down':
            return 'Uzun Süreli Düşüş';
        case 'status_change':
        default:
            return 'Durum Değişikliği';
    }
}

/**
 * Bildirimi okundu olarak işaretler
 */
async function markAsRead(notificationId, cardElement) {
    try {
        const response = await fetch(`${API_BASE_URL}/notifications/read/${notificationId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.ok) {
            // Kartı güncelle
            cardElement.classList.remove('border-blue-500/60', 'bg-gradient-to-br', 'from-slate-800/95', 'to-blue-900/20', 'hover:shadow-blue-500/20');
            cardElement.classList.add('border-slate-700/60');

            // Badge'i kaldır
            const badge = cardElement.querySelector('.absolute');
            if (badge) badge.remove();

            // Başlık rengini güncelle
            const title = cardElement.querySelector('h3');
            if (title) {
                title.classList.remove('text-slate-100');
                title.classList.add('text-slate-300');
            }

            // Okundu butonunu kaldır
            const markReadBtn = cardElement.querySelector('[title="Okundu olarak işaretle"]');
            if (markReadBtn) markReadBtn.remove();

            showSuccessToast('Bildirim okundu olarak işaretlendi');
        } else {
            const data = await response.json();
            showErrorToast(data.message || 'Bildirim işaretlenirken hata oluştu');
        }
    } catch (error) {
        showErrorToast('Bildirim işaretlenirken hata oluştu');
    }
}

/**
 * Bildirimi siler
 */
async function deleteNotification(notificationId, cardElement) {
    if (!confirm('Bu bildirimi silmek istediğinizden emin misiniz?')) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });

        if (response.ok) {
            // Kartı animasyonla kaldır
            cardElement.style.transition = 'all 0.3s ease';
            cardElement.style.transform = 'translateX(100%)';
            cardElement.style.opacity = '0';

            setTimeout(() => {
                cardElement.remove();
            }, 300);

            showSuccessToast('Bildirim silindi');
        } else {
            const data = await response.json();
            showErrorToast(data.message || 'Bildirim silinirken hata oluştu');
        }
    } catch (error) {
        showErrorToast('Bildirim silinirken hata oluştu');
    }
} 