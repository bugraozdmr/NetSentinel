import { API_BASE_URL } from '../config.js';
import { showErrorToast, showSuccessToast } from '../helpers/toast.js';
import { escapeHtml, formatDate } from '../helpers/dom.js';
import { defineGlobalAjaxErrorHandler } from '../helpers/ajax.js';
import { createModernNotificationCard } from '../helpers/notificationCard.js';

document.addEventListener('DOMContentLoaded', function () {
    let currentPage = 1;
    let hasMoreNotifications = true;
    let currentNotificationId = null;
    let notificationsList, loadMoreBtn, loadingIndicator, markReadBtn;
    let deleteOldModal, deleteAllModal, deleteSingleModal;

    // Toast'ları ve global AJAX error handler'ı başlat
    $(function () {
        $('#errorToast').removeClass('translate-x-0').addClass('translate-x-full hidden');
        $('#successToast').removeClass('translate-x-0').addClass('translate-x-full hidden');
    });
    document.addEventListener('DOMContentLoaded', function () {
        var et = document.getElementById('errorToast');
        if (et) et.classList.remove('translate-x-0'), et.classList.add('translate-x-full', 'hidden');
        var st = document.getElementById('successToast');
        if (st) st.classList.remove('translate-x-0'), st.classList.add('translate-x-full', 'hidden');
    });
    defineGlobalAjaxErrorHandler();

    notificationsList = document.getElementById('notifications-list');
    loadMoreBtn = document.getElementById('load-more-notifications');
    loadingIndicator = document.getElementById('notifications-loading');
    markReadBtn = document.getElementById('mark-read-btn');
    deleteOldModal = document.getElementById('deleteOldModal');
    deleteAllModal = document.getElementById('deleteAllModal');
    deleteSingleModal = document.getElementById('deleteSingleModal');
    if (notificationsList) {
        loadNotifications();
        setupEventListeners();
    }

    function setupEventListeners() {
        if (markReadBtn) {
            markReadBtn.addEventListener('click', markAllAsRead);
        }
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadMoreNotifications);
        }
    }

    async function loadNotifications(reset = true) {
        if (!notificationsList) return;
        if (reset) {
            currentPage = 1;
            hasMoreNotifications = true;
            notificationsList.innerHTML = `<div class="flex flex-col items-center justify-center bg-slate-800/80 rounded-2xl shadow-xl p-6 text-slate-300"><svg class="w-8 h-8 mb-2 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg><span>Bildirimler yükleniyor...</span></div>`;
        }
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications?page=${currentPage}&limit=10`);
            const data = await response.json();
            if (response.ok) {
                if (reset) notificationsList.innerHTML = '';
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        const notificationElement = createModernNotificationCard(notification, {
                            showActions: true,
                            compact: false,
                            onDelete: async (id) => {
                                currentNotificationId = id;
                                if (deleteSingleModal) deleteSingleModal.classList.remove('hidden');
                            },
                            onMarkRead: async (id) => {
                                await markAsRead(id);
                            }
                        });
                        notificationsList.appendChild(notificationElement);
                    });
                    hasMoreNotifications = data.notifications.length === 10;
                    if (hasMoreNotifications && loadMoreBtn) loadMoreBtn.classList.remove('hidden');
                    else if (loadMoreBtn) loadMoreBtn.classList.add('hidden');
                } else {
                    if (reset) notificationsList.innerHTML = `<div class="flex flex-col items-center justify-center bg-slate-800/80 rounded-2xl shadow-xl p-6 text-slate-300"><svg class="w-8 h-8 mb-2 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Henüz bildirim bulunmuyor</span></div>`;
                    if (loadMoreBtn) loadMoreBtn.classList.add('hidden');
                }
            } else {
                showErrorToast(data.message || 'Bildirimler yüklenirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Bildirimler yüklenirken hata oluştu');
        }
    }

    async function loadMoreNotifications() {
        if (!hasMoreNotifications || !loadMoreBtn) return;
        currentPage++;
        await loadNotifications(false);
    }

    window.showDeleteOldModal = function () { if (deleteOldModal) deleteOldModal.classList.remove('hidden'); };
    window.hideDeleteOldModal = function () { if (deleteOldModal) deleteOldModal.classList.add('hidden'); };
    window.showDeleteAllModal = function () { if (deleteAllModal) deleteAllModal.classList.remove('hidden'); };
    window.hideDeleteAllModal = function () { if (deleteAllModal) deleteAllModal.classList.add('hidden'); };
    window.showDeleteSingleModal = function (id) { currentNotificationId = id; if (deleteSingleModal) deleteSingleModal.classList.remove('hidden'); };
    window.hideDeleteSingleModal = function () { if (deleteSingleModal) deleteSingleModal.classList.add('hidden'); currentNotificationId = null; };
    window.deleteOldNotifications = deleteOldNotifications;
    window.deleteAllNotifications = deleteAllNotifications;
    window.confirmDeleteSingle = confirmDeleteSingle;
    window.markAsRead = markAsRead;

    async function markAsRead(notificationId) {
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications/read/${notificationId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' } });
            const data = await response.json();
            if (response.ok) {
                // Kartı bul ve güncelle
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`) ||
                    document.querySelector(`.notification-card:has([onclick*="markAsRead(${notificationId})"])`);
                if (notificationElement) {
                    notificationElement.classList.remove('border-blue-500/60', 'bg-gradient-to-br', 'from-slate-800/95', 'to-blue-900/20', 'hover:shadow-blue-500/20');
                    notificationElement.classList.add('border-slate-700/60');

                    // Badge'i kaldır
                    const badge = notificationElement.querySelector('.absolute');
                    if (badge) badge.remove();

                    // Başlık rengini güncelle
                    const title = notificationElement.querySelector('h3');
                    if (title) {
                        title.classList.remove('text-slate-100');
                        title.classList.add('text-slate-300');
                    }

                    // Okundu butonunu kaldır
                    const markReadBtn = notificationElement.querySelector('[title="Okundu işaretle"]');
                    if (markReadBtn) markReadBtn.remove();
                }
                showSuccessToast('Bildirim okundu olarak işaretlendi');
            } else {
                showErrorToast(data.message || 'Bildirim işaretlenirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Bildirim işaretlenirken hata oluştu');
        }
    }

    async function markAllAsRead() {
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications/mark-all-read`, { method: 'PUT', headers: { 'Content-Type': 'application/json' } });
            const data = await response.json();
            if (response.ok) {
                await loadNotifications();
                showSuccessToast('Tüm bildirimler okundu olarak işaretlendi');
            } else {
                showErrorToast(data.message || 'Bildirimler işaretlenirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Bildirimler işaretlenirken hata oluştu');
        }
    }

    async function deleteOldNotifications() {
        const days = document.getElementById('oldNotificationsDays').value;
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications/delete-old`, { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ days: parseInt(days) }) });
            const data = await response.json();
            if (response.ok) {
                window.hideDeleteOldModal();
                await loadNotifications();
                showSuccessToast(`${data.deleted_count} eski bildirim silindi`);
            } else {
                showErrorToast(data.message || 'Eski bildirimler silinirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Eski bildirimler silinirken hata oluştu');
        }
    }

    async function deleteAllNotifications() {
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications/delete-all`, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } });
            const data = await response.json();
            if (response.ok) {
                window.hideDeleteAllModal();
                await loadNotifications();
                showSuccessToast('Tüm bildirimler silindi');
            } else {
                showErrorToast(data.message || 'Bildirimler silinirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Bildirimler silinirken hata oluştu');
        }
    }

    async function confirmDeleteSingle() {
        if (!currentNotificationId) return;
        try {
            const response = await fetch(`${API_BASE_URL()}/notifications/${currentNotificationId}`, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } });
            const data = await response.json();
            if (response.ok) {
                window.hideDeleteSingleModal();
                await loadNotifications();
                showSuccessToast('Bildirim silindi');
            } else {
                showErrorToast(data.message || 'Bildirim silinirken hata oluştu');
            }
        } catch (error) {
            showErrorToast('Bildirim silinirken hata oluştu');
        }
    }
}); 