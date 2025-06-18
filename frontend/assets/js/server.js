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

  function fetchNotifications(serverId) {
    const $notifContainer = $("#notifications");
    const $loading = $("#notifications-loading");
    const $list = $("#notifications-list");

    $notifContainer.removeClass("hidden");
    $loading.show();
    $list.empty();

    $.get(`${API_BASE_URL}/notifications/server/${serverId}`)
      .done(function (data) {
        $loading.hide();

        if (data.notifications && data.notifications.length > 0) {
          data.notifications.forEach(function (notif) {
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
            const icon = `<span class='flex items-center justify-center w-10 h-10 rounded-full bg-blue-700/80 text-white shadow-lg mr-4'>
              <svg class='w-6 h-6' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' /></svg>
            </span>`;
            const msg = `<span class='block text-base md:text-lg font-semibold text-slate-100 mb-1'>${notif.message}</span>`;
            const time = `<span class='block text-xs text-slate-400 text-right mt-2'>${new Date(notif.created_at).toLocaleString()}</span>`;

            const li = $(`
              <li class="relative flex items-start gap-3 p-5 md:p-6 rounded-2xl ${border} ${shadow} ${scale} transition-all duration-200 cursor-pointer group overflow-hidden">
                ${badge}
                ${icon}
                <div class="flex-1 min-w-0">
                  ${msg}
                  ${time}
                </div>
              </li>
            `);

            $list.append(li);
          });
        } else {
          $list.html(
            '<p class="text-center text-gray-500 italic py-4">Bildirim bulunamadı.</p>'
          );
        }
      })
      .fail(function () {
        $loading.hide();
        $list.html(
          '<p class="text-center text-red-600 font-semibold py-4">Bildirimler yüklenemedi.</p>'
        );
      });
  }

  $(document).ready(function () {
    const pathParts = window.location.pathname.split("/");
    const serverId = pathParts[pathParts.length - 1];

    if (!serverId || isNaN(serverId)) {
      $("#loading").text("Geçersiz sunucu ID.");
      return;
    }

    $.get(`${API_BASE_URL}/server/${serverId}`, function (data) {
      const server = data.server;

      $("#name").text(server.name);
      $("#ip").text(server.ip);
      $("#location").text(server.location);
      $("#last_check_at").text(server.last_check_at);

      if (server.is_active == 1) {
        $("#is_active").html(
          '<span class="inline-block px-3 py-1 rounded-full text-white text-sm font-semibold bg-green-500">Aktif</span>'
        );
      } else {
        $("#is_active").html(
          '<span class="inline-block px-3 py-1 rounded-full text-white text-sm font-semibold bg-red-500">Pasif</span>'
        );
      }

      const checks = JSON.parse(server.last_checks || "[]");
      const $checkList = $("#checkList").empty();

      const labels = [];
      const msValues = [];

      checks.forEach((check) => {
        const isActive = check.status === 1;
        const colorBg = isActive ? "bg-green-500" : "bg-red-500";
        const colorBgDark = isActive ? "dark:bg-green-600" : "dark:bg-red-600";
        const icon = isActive ? "✓" : "✗";

        const formattedTime = check.time.replace("T", " ").substring(0, 19);

        const div = $(`
        <div class="flex-1 flex items-center space-x-3 bg-gray-50 dark:bg-gray-900 rounded-lg px-4 py-2 shadow-md hover:shadow-lg transition-shadow duration-200 cursor-default mx-1">
          <span class="flex items-center justify-center w-8 h-8 rounded-full text-white ${colorBg} ${colorBgDark} font-semibold text-lg select-none">
            ${icon}
          </span>
          <span class="text-gray-900 dark:text-gray-100 font-semibold text-sm tracking-wide leading-tight">
            ${formattedTime}
          </span>
        </div>
      `);
        $checkList.append(div);

        labels.push(check.time.substr(11, 5));
        msValues.push(check.avg_ms !== null ? parseFloat(check.avg_ms) : null);
      });

      if (pingChart) {
        pingChart.destroy();
      }

      const ctx = document.getElementById("pingChart").getContext("2d");
      pingChart = new Chart(ctx, {
        type: "line",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Ping Süresi (ms)",
              data: msValues,
              borderColor: "rgba(37, 99, 235, 1)",
              backgroundColor: "rgba(37, 99, 235, 0.2)",
              spanGaps: true,
              tension: 0.3,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: "rgba(37, 99, 235, 1)",
              fill: true,
              borderWidth: 2,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          layout: {
            padding: 10,
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "ms",
                font: { size: 12 },
              },
              ticks: {
                font: { size: 11 },
                stepSize: 10,
              },
            },
            x: {
              title: {
                display: true,
                text: "Zaman",
                font: { size: 12 },
              },
              ticks: {
                font: { size: 11 },
              },
            },
          },
          plugins: {
            legend: {
              display: true,
              labels: {
                font: {
                  size: 13,
                  weight: "600",
                },
              },
            },
            tooltip: {
              enabled: true,
              mode: "nearest",
              intersect: false,
              backgroundColor: "rgba(37, 99, 235, 0.8)",
              titleFont: { size: 13 },
              bodyFont: { size: 12 },
              padding: 8,
            },
          },
          responsive: true,
        },
      });

      const $ports = $("#ports").empty();

      server.ports.forEach((port) => {
        const isOpen = port.is_open == 1;
        const bgColor = isOpen ? "bg-green-50" : "bg-red-50";
        const textColor = isOpen ? "text-green-800" : "text-red-800";
        const statusBg = isOpen ? "bg-green-500" : "bg-red-500";

        const portDiv = $(`
    <div class="${bgColor} ${textColor} flex items-center justify-between p-4 rounded-xl shadow-md font-semibold cursor-default hover:shadow-lg transition-shadow duration-200 w-full max-w-[140px]">
      <span class="text-lg md:text-xl font-bold select-none mr-2">${port.port_number}</span>
      <span class="${statusBg} w-5 h-5 rounded-full select-none"></span>
    </div>
  `);

        $ports.append(portDiv);
      });

      $("#loading-detail").hide();
      $("#serverDetail").removeClass("hidden");

      fetchNotifications(serverId);

      // Panel bilgisini doldur
      $("#panel").text(server.panel || "-");
    }).fail(function () {
      $("#loading-detail").text("Veri yüklenemedi.");
    });
  });

  // * Notifications
  function loadNotifications() {
    $.ajax({
      url: `${API_BASE_URL}/notifications/`,
      method: "GET",
      dataType: "json",
      success: function (response) {
        const $list = $("#notifications-list");
        $list.empty();

        if (!response.notifications || response.notifications.length === 0) {
          $list.html(
            '<div class="bg-white shadow-lg rounded-xl p-6 text-center text-gray-500">Henüz hiç bildiriminiz yok.</div>'
          );
          return;
        }

        response.notifications.forEach((notification) => {
          const isUnread = notification.status === "unread";

          const borderClass = isUnread ? "border-blue-600" : "border-gray-300";
          const bgClass = isUnread ? "bg-blue-50" : "bg-white";
          const newBadge = isUnread
            ? '<span class="ml-4 inline-block text-xs font-semibold text-blue-700 bg-blue-100 px-2 py-1 rounded">Yeni</span>'
            : "";

          // Tarih formatı için basit fonksiyon
          const date = new Date(notification.created_at);
          const formattedDate = date.toLocaleDateString("tr-TR", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });

          const itemHtml = `
          <div class="flex items-start bg-white shadow rounded-xl p-4 transition hover:shadow-lg border-l-4 ${borderClass} ${bgClass}">
            <div class="text-2xl mr-4 mt-1">📣</div>
            <div class="flex-1">
              <p class="text-gray-800 font-medium">${notification.message}</p>
              <p class="text-sm text-gray-500 mt-1">${formattedDate}</p>
            </div>
            ${newBadge}
          </div>
        `;

          $list.append(itemHtml);
        });
      },
      error: function () {
        $("#notifications-list").html(
          '<div class="text-center text-red-500">Bildirimler yüklenirken hata oluştu.</div>'
        );
      },
    });
  }

  $("#mark-read-btn").on("click", function () {
    $.ajax({
      url: `${API_BASE_URL}/notifications/mark-read`,
      method: "POST",
      success: function (response) {
        location.reload();
      },
      error: function () {
        alert("Bildirimleri okundu olarak işaretlerken hata oluştu.");
      },
    });
  });

  // TODO : bunları sonra bir yerde topla bu gelirse şunu sesle şu gelirse şunları sesle !!!
  $(document).ready(function () {
    if (
      window.location.pathname === "/netsentinel/notifications" ||
      window.location.pathname === "/netsentinel/notifications/"
    ) {
      loadNotifications();
    }
  });

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

  updateNotificationCount();

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
});
