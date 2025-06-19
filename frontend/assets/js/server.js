import { API_BASE_URL, APP_NAME, INTERVAL_TIME } from "./config.js";

$(document).ready(function () {
  function updateNotificationCount() {
    $.ajax({
      url: `${API_BASE_URL}/notifications/count/all`,
      method: "GET",
      dataType: "json",
      success: function (data) {
        const count = data.unread_count;
        const $badge = $('button[aria-label="notifications"] span');

        if (count && count > 0) {
          $badge.text(count).show();
        } else {
          $badge.hide();
        }
      },
      error: function () {
        console.error("Bildirim sayısı alınamadı");
      },
    });
  }

  //* HOME PAGE
  const $panelGrid = $("#serverPanelGrid");
  const $search = $("#searchInput");
  const $loading = $("#loading");
  const $error = $("#error");
  const $deleteModal = $("#deleteModal");
  const $confirmDeleteBtn = $("#confirmDelete");
  const $cancelDeleteBtn = $("#cancelDelete");
  let allServers = [];
  let selectedServerId = null;

  function formatDate(str) {
    const date = new Date(str);
    return date.toLocaleString("tr-TR");
  }

  function renderStatusBars(json) {
    let checks = [];
    try {
      const parsed = JSON.parse(json);
      if (Array.isArray(parsed)) {
        checks = parsed;
      } else {
        checks = [];
      }
    } catch (e) {
      checks = [];
    }
    const maxChecks = 10;
    if (!Array.isArray(checks)) {
      checks = [];
    }
    const emptyCount = maxChecks - checks.length;
    const normalized = [
      ...Array(emptyCount).fill(null),
      ...checks.slice(-maxChecks),
    ];
    const bars = normalized.map((check, idx) => {
      let color = "bg-gray-700";
      let tooltip = "Henüz kontrol edilmedi";
      if (check && typeof check === "object" && "status" in check) {
        if (check.status === 1) {
          color = "bg-green-500";
          tooltip = check.time || "Tarih yok";
        } else if (check.status === 0) {
          color = "bg-red-500";
          tooltip = check.time || "Tarih yok";
        }
      }
      return `
        <div class="group relative cursor-default overflow-visible">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 text-white text-[10px] px-1 py-[1px] opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-auto select-none shadow-lg">
            ${tooltip}
          </span>
          <div class="w-3 h-5 ${color} rounded-sm group-hover:-translate-y-[2px] transition-transform"></div>
        </div>
      `;
    });
    return `<div class="flex gap-[2px] justify-end items-end">${bars.join("")}</div>`;
  }

  // Sunucu lokasyonuna göre ikon döndüren fonksiyon
  function getServerIcon(location) {
    if (!location) return defaultIcon();
    const loc = location.toLowerCase();
    if (loc === "mars") {
      // Gerçekçi Türk bayrağı SVG (tam daire, taşma yok)
      return `<span title="Mars (Türkiye)"><svg viewBox="0 0 64 64" class="w-10 h-10"><circle cx="32" cy="32" r="32" fill="#E30A17"/><circle cx="26" cy="32" r="12" fill="#fff"/><circle cx="29" cy="32" r="9" fill="#E30A17"/><path d="M38 32a4 4 0 1 1-8 0 4 4 0 0 1 8 0z" fill="#fff"/></svg></span>`;
    } else if (loc === "hetzner") {
      // Hetzner: Sol yarı Almanya, sağ yarı ABD, tam daire maskeli SVG
      return `<span title="Hetzner (DE/US)"><svg viewBox="0 0 64 64" class="w-10 h-10">
        <defs>
          <clipPath id="circleMask"><circle cx="32" cy="32" r="32"/></clipPath>
        </defs>
        <g clip-path="url(#circleMask)">
          <!-- Sol: Almanya -->
          <rect x="0" y="0" width="32" height="64" fill="#000"/>
          <rect x="0" y="21" width="32" height="22" fill="#DD0000"/>
          <rect x="0" y="43" width="32" height="21" fill="#FFCE00"/>
          <!-- Sağ: ABD -->
          <rect x="32" y="0" width="32" height="64" fill="#B22234"/>
          <g>
            <rect x="32" y="7" width="32" height="6" fill="#fff"/>
            <rect x="32" y="19" width="32" height="6" fill="#fff"/>
            <rect x="32" y="31" width="32" height="6" fill="#fff"/>
            <rect x="32" y="43" width="32" height="6" fill="#fff"/>
            <rect x="32" y="55" width="32" height="6" fill="#fff"/>
          </g>
          <rect x="32" y="0" width="18" height="25" fill="#3c3b6e"/>
          <g fill="#fff">
            <circle cx="35" cy="5" r="1.2"/><circle cx="41" cy="5" r="1.2"/><circle cx="47" cy="5" r="1.2"/>
            <circle cx="35" cy="11" r="1.2"/><circle cx="41" cy="11" r="1.2"/><circle cx="47" cy="11" r="1.2"/>
            <circle cx="35" cy="17" r="1.2"/><circle cx="41" cy="17" r="1.2"/><circle cx="47" cy="17" r="1.2"/>
          </g>
        </g>
        <circle cx="32" cy="32" r="31" fill="none" stroke="#222" stroke-width="2"/>
      </svg></span>`;
    } else {
      return defaultIcon();
    }
  }

  function defaultIcon() {
    return `<svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 17.25h16.5M4.5 6.75h15v10.5a2.25 2.25 0 0 1-2.25 2.25h-10.5A2.25 2.25 0 0 1 4.5 17.25V6.75zm3 3.75h9"/></svg>`;
  }

  function getPanelIcon(panel, sizeClass = 'w-6 h-6') {
    const p = (panel || '').toLowerCase();
    if (p === 'cpanel') return `<img src="https://cdn.simpleicons.org/cpanel/FF6C2C" alt="cPanel" title="cPanel" class="${sizeClass} inline-block align-middle" />`;
    if (p === 'plesk') return `<img src="https://cdn.simpleicons.org/plesk/52B0E7" alt="Plesk" title="Plesk" class="${sizeClass} inline-block align-middle" />`;
    if (p === 'backup') return `<img src="https://cdn.simpleicons.org/minio/00B4B6" alt="Backup" title="Backup" class="${sizeClass} inline-block align-middle" />`;
    if (p === 'esxi') return `<img src="https://cdn.simpleicons.org/vmware/607078" alt="ESXi" title="ESXi" class="${sizeClass} inline-block align-middle" />`;
    if (p === 'yok') return `<img src="https://cdn.simpleicons.org/protonmail/gray" alt="Panel Yok" title="Panel Yok" class="${sizeClass} inline-block align-middle opacity-40" />`;
    if (p === 'diğer' || p === 'diger') return `<svg class="${sizeClass} inline-block align-middle text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>`;
    return `<img src="https://cdn.simpleicons.org/question/gray" alt="Bilinmiyor" title="Bilinmiyor" class="${sizeClass} inline-block align-middle opacity-40" />`;
  }

  function renderPanel(servers) {
    $panelGrid.empty();
    if (servers.length === 0) {
      $panelGrid.append(`
        <div class="col-span-full text-center py-6 text-slate-400">
          Arama kriterlerine uygun sunucu bulunamadı.
        </div>`);
      return;
    }
    servers.forEach((server) => {
      const statusText = server.is_active == 1 ? "Aktif" : "Kapalı";
      const statusColor = server.is_active == 1 ? "border-green-400" : "border-red-500";
      const ledColor = server.is_active == 1 ? "bg-green-400" : "bg-red-500";
      const checks = renderStatusBars(server.last_checks);
      const lastCheck = server.last_check_at ? formatDate(server.last_check_at) : "Henüz kontrol edilmedi";
      const ports = Array.isArray(server.ports)
        ? server.ports.map((port) => ({
          number: port.port_number,
          isOpen: port.is_open === 1 || port.is_open === true,
        }))
        : [];
      const portPanelId = `port-panel-${server.id}`;
      const toggleBtnId = `toggle-ports-${server.id}`;
      $panelGrid.append(`
        <div class="relative bg-gradient-to-br from-slate-800 to-slate-900 border-4 ${statusColor} rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-all hover:scale-105 hover:shadow-blue-500/30">
          <!-- Sunucu İkonu -->
          <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 rounded-full p-3 shadow-lg border-4 border-slate-700">
            ${getServerIcon(server.location)}
          </div>
          <!-- Durum LED'i -->
          <span class="absolute top-4 right-4 w-3 h-3 rounded-full ${ledColor} border-2 border-white shadow"></span>
          <!-- Sunucu Adı ve IP -->
          <div class="flex flex-col items-center mb-1">
            <span class="mb-3">${getPanelIcon(server.panel, 'w-12 h-12')}</span>
            <span class="text-2xl font-extrabold text-white">${server.name}</span>
          </div>
          <div class="text-sm text-blue-200 mb-4">${server.ip}</div>
          <!-- Durum Badge -->
          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold border border-slate-600 bg-slate-700 text-white mb-2">${statusText}</span>
          <!-- Son Kontroller Barı -->
          <div class="flex items-center gap-2 mb-2 justify-center">${checks}</div>
          <div class="text-xs text-slate-400 mb-2">Son kontrol: ${lastCheck}</div>
          <!-- Portlar ve Aksiyonlar -->
          <button type="button" id="${toggleBtnId}" aria-controls="${portPanelId}" aria-expanded="false" class="toggle-ports-btn mt-2 mb-2 px-6 py-2 rounded-full font-semibold bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-lg flex items-center gap-2 text-base transition-all duration-200 hover:from-blue-700 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
            </svg>
            <span>Portları Göster</span>
          </button>
          <div id="${portPanelId}" class="ports-list hidden mt-2 transition-all">
            <div class="flex flex-wrap gap-2 items-center justify-center">
              ${ports.map(port => `
                <span class="inline-flex items-center px-2 py-1 rounded-full border text-xs font-semibold mr-2 ${port.isOpen ? 'bg-green-50 text-green-700 border-green-300' : 'bg-red-50 text-red-700 border-red-300'}">
                  ${port.number}
                  <span class="ml-1 w-2 h-2 ${port.isOpen ? 'bg-green-500' : 'bg-red-500'} rounded-full"></span>
                </span>
              `).join('')}
            </div>
          </div>
          <div class="flex gap-2 mt-4">
            <button type="button" title="Detayları Gör" aria-label="Detayları Gör" data-id="${server.id}" class="detail-btn inline-flex items-center justify-center size-9 rounded-full bg-slate-700 text-blue-300 hover:bg-blue-900 hover:text-white transition"><i class="fa fa-eye"></i></button>
            <button type="button" title="Düzenle" aria-label="Düzenle" data-id="${server.id}" class="edit-btn inline-flex items-center justify-center size-9 rounded-full bg-slate-700 text-blue-300 hover:bg-blue-900 hover:text-white transition"><i class="fa fa-pen"></i></button>
            <button type="button" title="Sil" aria-label="Sil" data-id="${server.id}" class="delete-btn inline-flex items-center justify-center size-9 rounded-full bg-slate-700 text-red-400 hover:bg-red-900 hover:text-white transition"><i class="fas fa-trash-alt text-base"></i></button>
          </div>
        </div>
      `);
    });
    // Portları aç/kapa butonları için event handler
    $(".toggle-ports-btn").off("click").on("click", function () {
      const btn = $(this);
      const portPanelId = btn.attr("aria-controls");
      const $portPanel = $(`#${portPanelId}`);
      const expanded = btn.attr("aria-expanded") === "true";
      if (expanded) {
        $portPanel.slideUp(200);
        btn.attr("aria-expanded", "false");
        btn.find("svg").html('<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />');
        btn.find("span").text("Portları Göster");
        btn.removeClass("from-green-600 to-green-400").addClass("from-blue-600 to-blue-400");
      } else {
        $portPanel.slideDown(200);
        btn.attr("aria-expanded", "true");
        btn.find("svg").html('<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />');
        btn.find("span").text("Portları Gizle");
        btn.removeClass("from-blue-600 to-blue-400").addClass("from-green-600 to-green-400");
      }
    });
    // Delete butonları için event handler'ı tekrar ekle
    $(".delete-btn").off("click").on("click", function () {
      const id = $(this).data("id");
      selectedServerId = id;
      $deleteModal.removeClass("hidden");
    });
  }

  function updateSummaryBar(servers) {
    const total = servers.length;
    const active = servers.filter(s => s.is_active == 1).length;
    const down = total - active;
    $("#totalServers").text(total);
    $("#activeServers").text(active);
    $("#downServers").text(down);
    const now = new Date();
    const timeStr = now.toLocaleTimeString("tr-TR", { hour12: false });
    $("#lastUpdate").text("Son güncelleme: " + timeStr);
  }

  function fetchServers() {
    $loading.removeClass("hidden");
    $.ajax({
      url: `${API_BASE_URL}/servers`,
      method: "GET",
      dataType: "json",
      success: function (data) {
        allServers = data.servers || [];
        updateSummaryBar(allServers);
        applyFilters();
      },
      error: function () {
        $error.removeClass("hidden");
      },
      complete: function () {
        $loading.addClass("hidden");
      },
    });
  }

  // Silme butonlarına tıklama (delegation)
  $(document).on("click", ".delete-btn", function () {
    selectedServerId = $(this).data("id");
    $deleteModal.removeClass("hidden");
  });

  // Silme işlemini iptal et
  $cancelDeleteBtn.on("click", function () {
    $deleteModal.addClass("hidden");
    selectedServerId = null;
  });

  // Arka plana tıklayınca modalı kapat
  $deleteModal.on("click", function (e) {
    if (e.target.id === "deleteModal") {
      $deleteModal.addClass("hidden");
      selectedServerId = null;
    }
  });

  $confirmDeleteBtn.on("click", function () {
    if (!selectedServerId) return;
    $.ajax({
      url: `${API_BASE_URL}/servers/delete/${selectedServerId}`,
      method: "DELETE",
      success: function () {
        allServers = allServers.filter(
          (server) => server.id !== selectedServerId
        );
        renderPanel(allServers);
        $deleteModal.addClass("hidden");
        selectedServerId = null;
      },
      error: function () {
        alert("Sunucu silinemedi. Lütfen tekrar deneyin.");
      },
    });
  });

  //* ADD SERVER
  const $successMsg = $("#successMsg");
  const $errorMsg = $("#errorMsg");

  const $addForm = $("#addServerForm");

  if ($addForm.length) {
    $addForm.on("submit", function (e) {
      e.preventDefault();

      const selectedPorts = $("input[name='ports[]']:checked")
        .map(function () {
          return parseInt($(this).val(), 10);
        })
        .get();

      const formData = {
        ip: $("#ip").val().trim(),
        name: $("#name").val().trim(),
        location: $("#location").val().trim(),
        panel: $("#panel").val().trim(),
        ports: selectedPorts,
      };


      $successMsg.addClass("hidden");
      $errorMsg.addClass("hidden").text("");

      $.ajax({
        url: `${API_BASE_URL}/servers`,
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify(formData),
        success: function () {
          $addForm[0].reset();
          $successMsg.removeClass("hidden");
          window.location.href = `/${APP_NAME}/`;
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message || "Sunucu eklenemedi.";
          $errorMsg.text(msg).removeClass("hidden");
        },
      });
    });
  }

  //* UPDATE SERVER
  document.addEventListener("click", (e) => {
    const target = e.target.closest(".edit-btn");
    if (!target) return;

    const id = target.getAttribute("data-id");

    window.location.href = `/${APP_NAME}/server/updateServer/${id}`;
  });

  const $editFormWrapper = $("#editFormWrapper");

  if ($editFormWrapper.length) {
    const serverId = $editFormWrapper.data("server-id");

    if (serverId) {
      const $loading = $("#loading");
      const $editFormContainer = $("#editFormContainer");
      const $errorMsg = $(`
        <div id="serverLoadError" class="max-w-xl mx-auto mt-16 px-6 py-10 bg-white rounded-2xl shadow-xl border border-red-300 text-center text-red-700 font-semibold text-xl">
          Sunucu bilgisi alınamadı.
        </div>
      `);

      $.ajax({
        url: `${API_BASE_URL}/server/${serverId}`,
        method: "GET",
        dataType: "json",
        success: function (res) {
          const server = res.server;

          $("#ip").val(server.ip);
          $("#name").val(server.name);

          const activePorts = Array.isArray(server.ports)
            ? server.ports.map((p) => String(p.port_number))
            : [];

          $(".port-checkbox").each(function () {
            const portVal = $(this).val();
            $(this).prop("checked", activePorts.includes(portVal));
          });

          setTimeout(() => {
            $("#locationUpdate").val(server.location);
            $("#panel").val(server.panel);
          }, 100);

          $loading.addClass("hidden");
          $editFormContainer.removeClass("hidden");
        },
        error: function (xhr) {
          $loading.remove();
          $editFormWrapper.append($errorMsg);
        },
      });
    }
  }


  $("#updateServerForm").on("submit", function (e) {
    e.preventDefault();

    const serverId = $("#editFormWrapper").data("server-id");

    const formData = {
      ip: $("#ip").val().trim(),
      name: $("#name").val().trim(),
      location: $("#locationUpdate").val().trim(),
      panel: $("#panel").val().trim(),
      ports: $("input[name='ports[]']:checked")
        .map(function () {
          return parseInt($(this).val(), 10);
        })
        .get(),
    };

    console.log('Update outgoing data:', formData);

    $.ajax({
      url: `${API_BASE_URL}/servers/edit/${serverId}`,
      method: "PUT",
      contentType: "application/json",
      data: JSON.stringify(formData),
      success: function () {
        window.location.href = `/${APP_NAME}/`;
      },
      error: function (xhr) {
        const errMsg = xhr.responseJSON?.errors || xhr.responseJSON?.message || "Sunucu güncellenemedi.";
        $("#updateErrorMsg").text(errMsg).removeClass("hidden");
      },
    });
  });

  //* DETAILS Page
  let pingChart = null;

  $(document).on("click", ".detail-btn", function () {
    const serverId = $(this).data("id");
    if (serverId) {
      window.location.href = `/${APP_NAME}/server/detail/${serverId}`;
    }
  });

  // * Notifications with Pagination
  let currentNotificationPage = 1;
  let currentServerNotificationPage = 1;
  let hasMoreNotifications = true;
  let hasMoreServerNotifications = true;

  function loadNotifications(page = 1, append = false) {
    const $list = $("#notifications-list");
    const $loading = $("#notifications-loading");
    const $loadMoreBtn = $("#load-more-notifications");

    if (page === 1) {
      $list.empty();
      currentNotificationPage = 1;
      hasMoreNotifications = true;
    }

    if (!append) {
      $loading.show();
      $loadMoreBtn.hide();
    }

    $.ajax({
      url: `${API_BASE_URL}/notifications/`,
      method: "GET",
      data: {
        page: page,
        limit: 20
      },
      dataType: "json",
      success: function (response) {
        $loading.hide();

        if (!response.notifications || response.notifications.length === 0) {
          if (page === 1) {
            $list.html(
              '<div class="bg-slate-800/80 shadow-lg rounded-xl p-6 text-center text-slate-400">Henüz hiç bildiriminiz yok.</div>'
            );
          }
          return;
        }

        response.notifications.forEach((notification) => {
          const notificationHtml = createNotificationHtml(notification);
          $list.append(notificationHtml);
        });

        // Pagination kontrolü
        hasMoreNotifications = response.pagination.has_next;
        if (hasMoreNotifications) {
          $loadMoreBtn.show();
        } else {
          $loadMoreBtn.hide();
        }

        currentNotificationPage = page;
      },
      error: function () {
        $loading.hide();
        if (page === 1) {
          $list.html(
            '<div class="text-center text-red-500">Bildirimler yüklenirken hata oluştu.</div>'
          );
        }
      },
    });
  }

  function fetchNotifications(serverId, page = 1, append = false) {
    const $notifContainer = $("#notifications");
    const $loading = $("#notifications-loading");
    const $list = $("#notifications-list");
    const $loadMoreBtn = $("#load-more-server-notifications");

    $notifContainer.removeClass("hidden");

    if (page === 1) {
      $list.empty();
      currentServerNotificationPage = 1;
      hasMoreServerNotifications = true;
    }

    if (!append) {
      $loading.show();
      $loadMoreBtn.hide();
    }

    $.get(`${API_BASE_URL}/notifications/server/${serverId}`, {
      page: page,
      limit: 20
    })
      .done(function (data) {
        $loading.hide();

        if (data.notifications && data.notifications.length > 0) {
          data.notifications.forEach(function (notif) {
            const notificationHtml = createServerNotificationHtml(notif);
            $list.append(notificationHtml);
          });

          // Pagination kontrolü
          hasMoreServerNotifications = data.pagination.has_next;
          if (hasMoreServerNotifications) {
            $loadMoreBtn.show();
          } else {
            $loadMoreBtn.hide();
          }

          currentServerNotificationPage = page;
        } else {
          if (page === 1) {
            $list.html(
              '<p class="text-center text-gray-500 italic py-4">Bildirim bulunamadı.</p>'
            );
          }
        }
      })
      .fail(function () {
        $loading.hide();
        if (page === 1) {
          $list.html(
            '<p class="text-center text-red-600 font-semibold py-4">Bildirimler yüklenemedi.</p>'
          );
        }
      });
  }

  function createNotificationHtml(notification) {
    const isUnread = notification.status === "unread";
    const borderClass = isUnread ? "border-blue-600" : "border-slate-600";
    const bgClass = isUnread ? "bg-slate-800/90" : "bg-slate-800/80";
    const newBadge = isUnread
      ? '<span class="ml-4 inline-block text-xs font-semibold text-blue-300 bg-blue-900/50 px-2 py-1 rounded">Yeni</span>'
      : "";

    // Bildirim türüne göre icon ve renk belirleme
    let iconClass = "text-blue-400";
    let iconSvg = "📣";
    let typeBadge = "";

    if (notification.notification_type) {
      switch (notification.notification_type) {
        case 'first_down':
          iconClass = "text-red-400";
          iconSvg = "⚠️";
          typeBadge = `<span class='inline-block text-xs font-semibold text-red-300 bg-red-900/50 px-2 py-1 rounded ml-2'>İlk Düşüş</span>`;
          break;
        case 'repeated_down':
          iconClass = "text-orange-400";
          iconSvg = "🔄";
          typeBadge = `<span class='inline-block text-xs font-semibold text-orange-300 bg-orange-900/50 px-2 py-1 rounded ml-2'>Tekrar Düşüş</span>`;
          break;
        case 'long_term_down':
          iconClass = "text-red-500";
          iconSvg = "🚨";
          typeBadge = `<span class='inline-block text-xs font-semibold text-red-300 bg-red-900/50 px-2 py-1 rounded ml-2'>Uzun Süreli Düşüş</span>`;
          break;
        case 'status_change':
        default:
          iconClass = "text-green-400";
          iconSvg = "✅";
          typeBadge = `<span class='inline-block text-xs font-semibold text-green-300 bg-green-900/50 px-2 py-1 rounded ml-2'>Durum Değişikliği</span>`;
          break;
      }
    }

    const date = new Date(notification.created_at);
    const formattedDate = date.toLocaleDateString("tr-TR", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });

    return `
      <div class="flex items-start ${bgClass} shadow rounded-xl p-4 transition hover:shadow-lg border-l-4 ${borderClass}">
        <div class="text-2xl mr-4 mt-1 ${iconClass}">${iconSvg}</div>
        <div class="flex-1">
          <p class="text-slate-200 font-medium">${notification.message} ${typeBadge}</p>
          <p class="text-sm text-slate-400 mt-1">${formattedDate}</p>
        </div>
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
          ${newBadge}
          <button onclick="showDeleteSingleModal(${notification.id})" class="text-red-400 hover:text-red-300 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </div>
    `;
  }

  function createServerNotificationHtml(notif) {
    const isUnread = notif.status === "unread";
    const border = isUnread
      ? "border-2 border-blue-400 bg-gradient-to-br from-blue-900/80 to-blue-800/80"
      : "border border-slate-700 bg-slate-800/80";
    const shadow = isUnread
      ? "shadow-xl hover:shadow-blue-400/40"
      : "shadow-md hover:shadow-lg";
    const scale = isUnread ? "hover:scale-[1.025]" : "hover:scale-[1.01]";
    const badge = isUnread
      ? `<span class='absolute -top-3 left-3 bg-gradient-to-r from-blue-500 to-blue-400 text-white text-xs font-bold px-3 py-1 rounded-full shadow animate-pulse z-10'>Yeni</span>`
      : "";
    let iconClass = "text-blue-400";
    let iconSvg = `<svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' /></svg>`;
    let typeBadge = "";
    if (notif.notification_type) {
      switch (notif.notification_type) {
        case 'first_down':
          iconClass = "text-red-400";
          iconSvg = `<svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z' /></svg>`;
          typeBadge = `<span class='inline-block text-xs font-semibold text-red-300 bg-red-900/50 px-2 py-1 rounded ml-2'>İlk Düşüş</span>`;
          break;
        case 'repeated_down':
          iconClass = "text-orange-400";
          iconSvg = `<svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>`;
          typeBadge = `<span class='inline-block text-xs font-semibold text-orange-300 bg-orange-900/50 px-2 py-1 rounded ml-2'>Tekrar Düşüş</span>`;
          break;
        case 'long_term_down':
          iconClass = "text-red-500";
          iconSvg = `<svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z' /></svg>`;
          typeBadge = `<span class='inline-block text-xs font-semibold text-red-300 bg-red-900/50 px-2 py-1 rounded ml-2'>Uzun Süreli Düşüş</span>`;
          break;
        case 'status_change':
        default:
          iconClass = "text-green-400";
          iconSvg = `<svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>`;
          typeBadge = `<span class='inline-block text-xs font-semibold text-green-300 bg-green-900/50 px-2 py-1 rounded ml-2'>Durum Değişikliği</span>`;
          break;
      }
    }
    const icon = `<span class='flex items-center justify-center w-10 h-10 rounded-full bg-slate-700/80 ${iconClass} shadow-lg mr-4'>${iconSvg}</span>`;
    const msg = `<span class='block text-base md:text-lg font-semibold text-slate-100 mb-1'>${notif.message} ${typeBadge}</span>`;
    const time = `<span class='block text-xs text-slate-400 text-right mt-2'>${new Date(notif.created_at).toLocaleString()}</span>`;
    // Sil ve okundu butonları
    const actions = `
      <div class="flex flex-col gap-2 ml-4">
        <button class="delete-server-notification-btn text-red-400 hover:text-red-300 p-1" data-id="${notif.id}" title="Sil">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
        ${isUnread ? `<button class="mark-read-server-notification-btn text-blue-400 hover:text-blue-300 p-1" data-id="${notif.id}" title="Okundu olarak işaretle">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </button>` : ''}
      </div>
    `;
    return `
      <li class="relative flex items-start gap-3 p-5 md:p-6 rounded-2xl ${border} ${shadow} ${scale} transition-all duration-200 cursor-pointer group overflow-hidden">
        ${badge}
        ${icon}
        <div class="flex-1 min-w-0">
          ${msg}
          ${time}
        </div>
        ${actions}
      </li>
    `;
  }

  // Load more butonları
  $("#load-more-notifications").on("click", function () {
    if (hasMoreNotifications) {
      loadNotifications(currentNotificationPage + 1, true);
    }
  });

  $("#load-more-server-notifications").on("click", function () {
    if (hasMoreServerNotifications) {
      const serverId = window.location.pathname.split('/').pop();
      fetchNotifications(serverId, currentServerNotificationPage + 1, true);
    }
  });

  // Çoklu filtreleme sistemi
  let currentFilters = {
    status: 'all',
    location: 'all',
    panel: 'all',
    search: ''
  };

  function applyFilters() {
    let filtered = allServers;

    // Durum filtresi
    if (currentFilters.status !== 'all') {
      filtered = filtered.filter(server => {
        if (currentFilters.status === 'active') {
          return server.is_active === 1;
        } else if (currentFilters.status === 'inactive') {
          return server.is_active === 0;
        }
        return true;
      });
    }

    // Lokasyon filtresi
    if (currentFilters.location !== 'all') {
      filtered = filtered.filter(server =>
        (server.location || '').toLowerCase() === currentFilters.location.toLowerCase()
      );
    }

    // Panel filtresi
    if (currentFilters.panel !== 'all') {
      filtered = filtered.filter(server =>
        (server.panel || '').toLowerCase() === currentFilters.panel.toLowerCase()
      );
    }

    // Arama filtresi
    if (currentFilters.search) {
      const searchTerm = currentFilters.search.toLowerCase();
      filtered = filtered.filter(server =>
        [server.ip, server.name, server.location, server.panel].some(val =>
          (val || '').toLowerCase().includes(searchTerm)
        )
      );
    }

    updateSummaryBar(filtered);
    renderPanel(filtered);
    updateFilterButtons();
  }

  function updateFilterButtons() {
    // Durum butonları
    $('.status-filter-btn').removeClass('bg-blue-600 text-white border-blue-600 shadow').addClass('bg-slate-800 text-green-300 border-slate-700');
    $(`.status-filter-btn[data-status="${currentFilters.status}"]`).removeClass('bg-slate-800 text-green-300 border-slate-700').addClass('bg-blue-600 text-white border-blue-600 shadow');

    // Lokasyon butonları
    $('.location-filter-btn').removeClass('bg-blue-600 text-white border-blue-600 shadow').addClass('bg-slate-800 text-blue-300 border-slate-700');
    $(`.location-filter-btn[data-location="${currentFilters.location}"]`).removeClass('bg-slate-800 text-blue-300 border-slate-700').addClass('bg-blue-600 text-white border-blue-600 shadow');

    // Panel select
    $('#panelFilter').val(currentFilters.panel);
  }

  // Durum filtreleme
  $('.status-filter-btn').on('click', function () {
    currentFilters.status = $(this).data('status');
    applyFilters();
  });

  // Lokasyon filtreleme
  $('.location-filter-btn').on('click', function () {
    currentFilters.location = $(this).data('location');
    applyFilters();
  });

  // Panel filtreleme
  $('#panelFilter').on('change', function () {
    currentFilters.panel = $(this).val();
    applyFilters();
  });

  // Arama filtreleme
  $('#searchInput').on('input', function () {
    currentFilters.search = $(this).val();
    applyFilters();
  });

  // Filtreleri temizle
  function clearAllFilters() {
    currentFilters = {
      status: 'all',
      location: 'all',
      panel: 'all',
      search: ''
    };
    $('#searchInput').val('');
    applyFilters();
  }

  // Filtreleri Temizle butonunu sadece HTML'deki butona bağla
  $('#clearFiltersBtn').on('click', clearAllFilters);

  //? sayfayı yenile belirlitilen aralıklarda
  if (
    window.location.pathname === `/${APP_NAME}/` ||
    window.location.pathname === `/${APP_NAME}/index.php`
  ) {
    //! burda çekme yapıldı index özel
    fetchServers();

    setInterval(function () {
      location.reload();
    }, INTERVAL_TIME);
  }

  //? DETAIL SAYFASI
  if (window.location.pathname.includes(`/${APP_NAME}/server/detail/`)) {
    const serverId = window.location.pathname.split('/').pop();
    if (serverId && !isNaN(serverId)) {
      loadServerDetail(serverId);
    }
  }

  // Detail sayfası için global değişkenler
  window.currentServerId = null;
  window.currentServerNotificationPage = 1;
  window.hasMoreServerNotifications = true;

  // Detail sayfası için sunucu bilgilerini yükle
  function loadServerDetail(serverId) {
    console.log('Loading server detail for ID:', serverId);
    window.currentServerId = serverId;

    $.ajax({
      url: `${API_BASE_URL}/server/${serverId}`,
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        if (data.server) {
          const server = data.server;
          populateServerDetail(server);
          loadServerNotifications(serverId, 1, false);
        } else {
          showError('Sunucu bilgileri alınamadı');
        }
      },
      error: function (xhr, status, error) {
        console.error('Server detail error:', error);
        showError('Sunucu bilgileri yüklenirken hata oluştu');
      }
    });
  }

  // Detail sayfasını doldur
  function populateServerDetail(server) {
    // Loading'i gizle
    $('#loading-detail').hide();
    $('#serverDetail').show();

    // Temel bilgileri doldur
    $('#name').text(server.name || 'N/A');
    $('#ip').text(server.ip || 'N/A');
    $('#location').text(server.location || 'N/A');
    $('#panel').text(server.panel || 'N/A');
    $('#is_active').text(server.is_active == 1 ? 'Aktif' : 'Kapalı');
    $('#last_check_at').text(server.last_check_at ? formatDate(server.last_check_at) : 'Henüz kontrol edilmedi');

    // Son kontroller
    renderCheckList(server.last_checks);

    // Port durumu
    renderPorts(server.ports);

    // Ping grafiği
    renderPingChart(server.last_checks);
  }

  // Son kontroller listesini render et
  function renderCheckList(checksJson) {
    const $checkList = $('#checkList');
    $checkList.empty();

    let checks = [];
    try {
      const parsed = JSON.parse(checksJson);
      if (Array.isArray(parsed)) {
        checks = parsed.slice(-10); // Son 10 kontrol
      }
    } catch (e) {
      console.error('Check list parse error:', e);
    }

    if (checks.length === 0) {
      $checkList.append(`
        <div class="text-center text-slate-400 py-4">
          Henüz kontrol verisi yok
        </div>
      `);
      return;
    }

    checks.reverse().forEach((check, index) => {
      const status = check.status === 1 ? 'Aktif' : 'Kapalı';
      const statusColor = check.status === 1 ? 'text-green-400' : 'text-red-400';
      const bgColor = check.status === 1 ? 'bg-green-900/30' : 'bg-red-900/30';
      const borderColor = check.status === 1 ? 'border-green-500/50' : 'border-red-500/50';

      $checkList.append(`
        <div class="bg-slate-800/60 p-4 rounded-xl border ${borderColor} ${bgColor} min-w-[200px]">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-slate-300">Kontrol #${checks.length - index}</span>
            <span class="text-sm font-bold ${statusColor}">${status}</span>
          </div>
          <div class="text-xs text-slate-400">
            ${check.time ? formatDate(check.time) : 'Tarih yok'}
          </div>
          ${check.ping ? `<div class="text-xs text-blue-400 mt-1">Ping: ${check.ping}ms</div>` : ''}
        </div>
      `);
    });
  }

  // Port durumunu render et
  function renderPorts(ports) {
    const $ports = $('#ports');
    $ports.empty();

    if (!Array.isArray(ports) || ports.length === 0) {
      $ports.append(`
        <div class="text-center text-slate-400 py-4">
          Port bilgisi yok
        </div>
      `);
      return;
    }

    ports.forEach(port => {
      const isOpen = port.is_open === 1 || port.is_open === true;
      const statusColor = isOpen ? 'text-green-400' : 'text-red-400';
      const bgColor = isOpen ? 'bg-green-900/30' : 'bg-red-900/30';
      const borderColor = isOpen ? 'border-green-500/50' : 'border-red-500/50';

      $ports.append(`
        <div class="bg-slate-800/60 p-4 rounded-xl border ${borderColor} ${bgColor} min-w-[120px] text-center">
          <div class="text-lg font-bold text-slate-100 mb-1">${port.port_number}</div>
          <div class="text-sm font-semibold ${statusColor}">
            ${isOpen ? 'Açık' : 'Kapalı'}
          </div>
          ${port.service_name ? `<div class="text-xs text-slate-400 mt-1">${port.service_name}</div>` : ''}
        </div>
      `);
    });
  }

  // Ping grafiğini render et
  function renderPingChart(checksJson) {
    const $chartContainer = $('#chartContainer');
    const $canvas = $('#pingChart');

    let checks = [];
    try {
      const parsed = JSON.parse(checksJson);
      if (Array.isArray(parsed)) {
        checks = parsed.slice(-20); // Son 20 kontrol
      }
    } catch (e) {
      console.error('Ping chart parse error:', e);
    }

    if (checks.length === 0) {
      $chartContainer.show();
      $canvas.replaceWith('<canvas id="pingChart" style="max-height: 300px;"></canvas>');
      return $('#pingChart').after('<div class="text-center text-slate-400 py-4">Ping verisi yok</div>');
    }

    // Ping verilerini hazırla (hem ping hem avg_ms)
    const pingData = checks
      .map(check => {
        if (typeof check.ping !== 'undefined') return Number(check.ping);
        if (typeof check.avg_ms !== 'undefined') return Number(check.avg_ms);
        return null;
      })
      .filter(val => val !== null && !isNaN(val) && val > 0);

    if (pingData.length === 0) {
      $chartContainer.show();
      $canvas.replaceWith('<canvas id="pingChart" style="max-height: 300px;"></canvas>');
      return $('#pingChart').after('<div class="text-center text-slate-400 py-4">Ping verisi yok</div>');
    }

    // X ekseni için etiketler (kontrol sırası veya zaman)
    const labels = checks
      .map(check => check.time ? (new Date(check.time)).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '')
      .slice(-pingData.length);

    // Eski chart varsa yok et
    if (window.pingChart && typeof window.pingChart.destroy === 'function') {
      window.pingChart.destroy();
    }
    $canvas.replaceWith('<canvas id="pingChart" style="max-height: 300px;"></canvas>');
    const ctx = document.getElementById('pingChart').getContext('2d');

    window.pingChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Ping (ms)',
          data: pingData,
          borderColor: 'rgba(59,130,246,1)',
          backgroundColor: 'rgba(59,130,246,0.2)',
          fill: true,
          tension: 0.3,
          pointRadius: 3,
          pointBackgroundColor: 'rgba(59,130,246,1)',
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return `Ping: ${context.parsed.y} ms`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'ms'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Zaman'
            }
          }
        }
      }
    });
  }

  // Detail sayfası için sunucu bildirimlerini yükle (pagination)
  function loadServerNotifications(serverId, page = 1, append = false) {
    const $notifications = $('#notifications');
    const $loading = $('#notifications-loading');
    const $list = $('#notifications-list');
    const $loadMoreBtn = $('#load-more-server-notifications');
    window.currentServerId = serverId;

    $notifications.show();
    if (page === 1) {
      $list.empty();
      window.currentServerNotificationPage = 1;
      window.hasMoreServerNotifications = true;
    }
    if (!append) {
      $loading.show();
      $loadMoreBtn.hide();
    }

    $.get(`${API_BASE_URL}/notifications/server/${serverId}`, {
      page: page,
      limit: 10
    })
      .done(function (data) {
        $loading.hide();
        if (data.notifications && data.notifications.length > 0) {
          data.notifications.forEach(function (notif) {
            $list.append(createServerNotificationHtml(notif));
          });
          // Pagination kontrolü
          window.hasMoreServerNotifications = !!(data.pagination && data.pagination.has_next);
          if (window.hasMoreServerNotifications) {
            $loadMoreBtn.removeClass('hidden').show();
          } else {
            $loadMoreBtn.addClass('hidden').hide();
          }
          window.currentServerNotificationPage = page;
        } else {
          if (page === 1) {
            $list.html('<li class="text-center text-slate-400 py-6">Bu sunucu için bildirim bulunamadı</li>');
          }
          $loadMoreBtn.addClass('hidden').hide();
        }
      })
      .fail(function () {
        $loading.hide();
        if (page === 1) {
          $list.html('<li class="text-center text-red-400 py-6">Bildirimler yüklenirken hata oluştu</li>');
        }
        $loadMoreBtn.addClass('hidden').hide();
      });
  }

  // Bildirim silme (tekli)
  $(document).off('click', '.delete-server-notification-btn').on('click', '.delete-server-notification-btn', function () {
    const notifId = $(this).data('id');
    if (!notifId) return;
    if (!confirm('Bu bildirimi silmek istediğine emin misin?')) return;
    $.ajax({
      url: `${API_BASE_URL}/notifications/${notifId}`,
      method: 'DELETE',
      success: function () {
        loadServerNotifications(window.currentServerId, 1, false);
      },
      error: function () {
        alert('Bildirim silinirken hata oluştu');
      }
    });
  });

  // Bildirim okundu olarak işaretle (tekli)
  $(document).off('click', '.mark-read-server-notification-btn').on('click', '.mark-read-server-notification-btn', function () {
    const notifId = $(this).data('id');
    if (!notifId) return;
    $.ajax({
      url: `${API_BASE_URL}/notifications/read/${notifId}`,
      method: 'PUT',
      success: function () {
        loadServerNotifications(window.currentServerId, 1, false);
      },
      error: function () {
        alert('Bildirim okundu olarak işaretlenirken hata oluştu');
      }
    });
  });

  // Toplu silme butonu (tüm sunucu bildirimleri)
  $('#delete-all-server-notifications').off('click').on('click', function () {
    if (!window.currentServerId) return;
    if (!confirm('Bu sunucuya ait TÜM bildirimleri silmek istediğine emin misin?')) return;
    $.ajax({
      url: `${API_BASE_URL}/notifications/server/${window.currentServerId}`,
      method: 'DELETE',
      success: function () {
        loadServerNotifications(window.currentServerId, 1, false);
      },
      error: function () {
        alert('Tüm bildirimler silinirken hata oluştu');
      }
    });
  });

  // Hata gösterme fonksiyonu
  function showError(message) {
    $('#loading-detail').hide();
    $('#serverDetail').html(`
      <div class="text-center text-red-400 py-12">
        <svg class="mx-auto mb-4 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div class="text-lg font-semibold">${message}</div>
      </div>
    `);
  }

  updateNotificationCount();
});
