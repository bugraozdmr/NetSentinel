import { API_BASE_URL, APP_NAME, INTERVAL_TIME } from "./config.js";
import { createModernNotificationCard } from "./helpers/notificationCard.js";

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

  // Pagination variables
  let currentPage = 1;
  let currentLimit = 100;
  let currentFilters = {
    status: '',
    location: '',
    panel: 'all',
    search: ''
  };
  let paginationData = {
    current_page: 1,
    per_page: 100,
    total: 0,
    total_pages: 1,
    has_next: false,
    has_prev: false
  };

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

  function fetchServers(page = 1, limit = 100, filters = {}) {
    $loading.removeClass("hidden");
    $error.addClass("hidden");

    // Build query parameters
    const params = new URLSearchParams({
      page: page,
      limit: limit
    });

    // Add filters to query parameters
    if (filters.status && filters.status !== 'all') {
      params.append('status', filters.status);
    }
    if (filters.location && filters.location !== 'all') {
      params.append('location', filters.location);
    }
    if (filters.panel && filters.panel !== 'all') {
      params.append('panel', filters.panel);
    }
    if (filters.search && filters.search.trim()) {
      params.append('search', filters.search.trim());
    }

    $.ajax({
      url: `${API_BASE_URL}/servers/paginated?${params.toString()}`,
      method: "GET",
      dataType: "json",
      success: function (data) {
        const servers = data.servers || [];
        paginationData = data.pagination || {
          current_page: 1,
          per_page: 100,
          total: 0,
          total_pages: 1,
          has_next: false,
          has_prev: false
        };

        // Update current page and limit
        currentPage = paginationData.current_page;
        currentLimit = paginationData.per_page;

        // Update summary bar with total stats
        updateSummaryBarWithStats();

        // Render servers and pagination
        renderPanel(servers);
        updatePaginationControls();

        // Update filter states
        updateFilterButtons();
      },
      error: function (xhr) {
        console.error("Server fetch error:", xhr);
        $error.removeClass("hidden").text("Sunucular alınırken bir hata oluştu.");
      },
      complete: function () {
        $loading.addClass("hidden");
      },
    });
  }

  function updateSummaryBarWithStats() {
    $.ajax({
      url: `${API_BASE_URL}/servers/stats`,
      method: "GET",
      dataType: "json",
      success: function (data) {
        $("#totalServers").text(data.total || 0);
        $("#activeServers").text(data.active || 0);
        $("#downServers").text(data.inactive || 0);
        const now = new Date();
        const timeStr = now.toLocaleTimeString("tr-TR", { hour12: false });
        $("#lastUpdate").text("Son güncelleme: " + timeStr);
      },
      error: function () {
        console.error("Stats fetch error");
      }
    });
  }

  function updatePaginationControls() {
    // Update page info
    $("#currentPageInfo").text(`Sayfa ${paginationData.current_page}`);
    $("#totalPagesInfo").text(paginationData.total_pages);
    $("#totalItemsInfo").text(paginationData.total);

    // Update navigation buttons
    const hasPrev = paginationData.has_prev;
    const hasNext = paginationData.has_next;

    $(".page-nav-btn").prop("disabled", false);
    $("#firstPageBtn, #firstPageBtnBottom").prop("disabled", !hasPrev);
    $("#prevPageBtn, #prevPageBtnBottom").prop("disabled", !hasPrev);
    $("#nextPageBtn, #nextPageBtnBottom").prop("disabled", !hasNext);
    $("#lastPageBtn, #lastPageBtnBottom").prop("disabled", !hasNext);

    // Update limit buttons
    $(".limit-btn").removeClass("bg-blue-600 text-white border-blue-600 shadow")
      .addClass("bg-slate-700 text-slate-300 border-slate-600");
    $(`.limit-btn[data-limit="${currentLimit}"]`)
      .removeClass("bg-slate-700 text-slate-300 border-slate-600")
      .addClass("bg-blue-600 text-white border-blue-600 shadow");

    // Generate page numbers
    generatePageNumbers();
  }

  function generatePageNumbers() {
    const current = paginationData.current_page;
    const total = paginationData.total_pages;
    const maxVisible = 5;

    let start = Math.max(1, current - Math.floor(maxVisible / 2));
    let end = Math.min(total, start + maxVisible - 1);

    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }

    const pageNumbers = [];

    // Add first page if not visible
    if (start > 1) {
      pageNumbers.push(1);
      if (start > 2) {
        pageNumbers.push('...');
      }
    }

    // Add visible pages
    for (let i = start; i <= end; i++) {
      pageNumbers.push(i);
    }

    // Add last page if not visible
    if (end < total) {
      if (end < total - 1) {
        pageNumbers.push('...');
      }
      pageNumbers.push(total);
    }

    // Render page numbers
    const $pageNumbers = $("#pageNumbers, #pageNumbersBottom");
    $pageNumbers.empty();

    pageNumbers.forEach(page => {
      if (page === '...') {
        $pageNumbers.append('<span class="px-3 py-2 text-slate-400">...</span>');
      } else {
        const isActive = page === current;
        const btnClass = isActive
          ? "px-3 py-2 rounded-lg bg-blue-600 text-white border border-blue-600 text-sm font-semibold"
          : "px-3 py-2 rounded-lg bg-slate-700 text-slate-300 border border-slate-600 text-sm font-semibold hover:bg-slate-600";

        $pageNumbers.append(`
          <button class="page-number-btn ${btnClass}" data-page="${page}">${page}</button>
        `);
      }
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
          const notificationHtml = createUnifiedNotificationHtml(notification);
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
            const notificationHtml = createUnifiedNotificationHtml(notif, true);
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

  // Bildirimleri tek tipte render eden fonksiyon - Modern kart kullanımı
  function createUnifiedNotificationHtml(notification, isServerDetail = false) {
    const card = createModernNotificationCard(notification, {
      isServerDetail: isServerDetail,
      showActions: true,
      compact: isServerDetail
    });

    if (isServerDetail) {
      const li = document.createElement('li');
      li.appendChild(card);
      return li.outerHTML;
    } else {
      return card.outerHTML;
    }
  }

  // Server detail sayfası için özel silme fonksiyonu
  async function deleteServerNotification(notificationId) {
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
        const card = document.querySelector(`[data-notification-id="${notificationId}"]`) ||
          document.querySelector(`.notification-card:has([data-id="${notificationId}"])`);
        if (card) {
          card.style.transition = 'all 0.3s ease';
          card.style.transform = 'translateX(100%)';
          card.style.opacity = '0';

          setTimeout(() => {
            card.remove();
          }, 300);
        }

        showSuccessToast('Bildirim silindi');
      } else {
        const data = await response.json();
        showErrorToast(data.message || 'Bildirim silinirken hata oluştu');
      }
    } catch (error) {
      showErrorToast('Bildirim silinirken hata oluştu');
    }
  }

  // Server detail sayfası için özel okundu işaretleme fonksiyonu
  async function markServerNotificationAsRead(notificationId) {
    try {
      const response = await fetch(`${API_BASE_URL}/notifications/read/${notificationId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        // Kartı bul ve güncelle
        const card = document.querySelector(`[data-notification-id="${notificationId}"]`) ||
          document.querySelector(`.notification-card:has([data-id="${notificationId}"])`);
        if (card) {
          card.classList.remove('border-blue-500/60', 'bg-gradient-to-br', 'from-slate-800/95', 'to-blue-900/20', 'hover:shadow-blue-500/20');
          card.classList.add('border-slate-700/60');

          // Badge'i kaldır
          const badge = card.querySelector('.absolute');
          if (badge) badge.remove();

          // Başlık rengini güncelle
          const title = card.querySelector('h3');
          if (title) {
            title.classList.remove('text-slate-100');
            title.classList.add('text-slate-300');
          }

          // Okundu butonunu kaldır
          const markReadBtn = card.querySelector('[title="Okundu olarak işaretle"]');
          if (markReadBtn) markReadBtn.remove();
        }

        showSuccessToast('Bildirim okundu olarak işaretlendi');
      } else {
        const data = await response.json();
        showErrorToast(data.message || 'Bildirim işaretlenirken hata oluştu');
      }
    } catch (error) {
      showErrorToast('Bildirim işaretlenirken hata oluştu');
    }
  }

  // Ana sayfa için okundu işaretleme fonksiyonu
  async function markAsRead(notificationId) {
    try {
      const response = await fetch(`${API_BASE_URL}/notifications/read/${notificationId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        // Kartı bul ve güncelle
        const card = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (card) {
          card.classList.remove('border-blue-500/60', 'bg-gradient-to-br', 'from-slate-800/95', 'to-blue-900/20', 'hover:shadow-blue-500/20');
          card.classList.add('border-slate-700/60');

          // Badge'i kaldır
          const badge = card.querySelector('.absolute');
          if (badge) badge.remove();

          // Başlık rengini güncelle
          const title = card.querySelector('h3');
          if (title) {
            title.classList.remove('text-slate-100');
            title.classList.add('text-slate-300');
          }

          // Okundu butonunu kaldır
          const markReadBtn = card.querySelector('[title="Okundu olarak işaretle"]');
          if (markReadBtn) markReadBtn.remove();
        }

        showSuccessToast('Bildirim okundu olarak işaretlendi');
      } else {
        const data = await response.json();
        showErrorToast(data.message || 'Bildirim işaretlenirken hata oluştu');
      }
    } catch (error) {
      showErrorToast('Bildirim işaretlenirken hata oluştu');
    }
  }

  // Modal fonksiyonları
  window.showDeleteSingleModal = function (id) {
    window.currentNotificationId = id;
    const modal = document.getElementById('deleteSingleModal');
    if (modal) modal.classList.remove('hidden');
  };

  window.hideDeleteSingleModal = function () {
    const modal = document.getElementById('deleteSingleModal');
    if (modal) modal.classList.add('hidden');
    window.currentNotificationId = null;
  };

  window.confirmDeleteSingle = async function () {
    if (!window.currentNotificationId) return;

    try {
      const response = await fetch(`${API_BASE_URL}/notifications/${window.currentNotificationId}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        // Kartı animasyonla kaldır
        const card = document.querySelector(`[data-notification-id="${window.currentNotificationId}"]`);
        if (card) {
          card.style.transition = 'all 0.3s ease';
          card.style.transform = 'translateX(100%)';
          card.style.opacity = '0';

          setTimeout(() => {
            card.remove();
          }, 300);
        }

        window.hideDeleteSingleModal();
        showSuccessToast('Bildirim silindi');
      } else {
        const data = await response.json();
        showErrorToast(data.message || 'Bildirim silinirken hata oluştu');
      }
    } catch (error) {
      showErrorToast('Bildirim silinirken hata oluştu');
    }
  };

  // Toast fonksiyonları
  function showSuccessToast(message) {
    const toast = document.getElementById('successToast');
    const messageEl = document.getElementById('successToastMessage');
    if (toast && messageEl) {
      messageEl.textContent = message;
      toast.classList.remove('hidden', 'translate-x-full');
      toast.classList.add('translate-x-0');

      setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.classList.add('hidden'), 300);
      }, 3000);
    }
  }

  function showErrorToast(message) {
    const toast = document.getElementById('errorToast');
    const messageEl = document.getElementById('errorToastMessage');
    if (toast && messageEl) {
      messageEl.textContent = message;
      toast.classList.remove('hidden', 'translate-x-full');
      toast.classList.add('translate-x-0');

      setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.classList.add('hidden'), 300);
      }, 3000);
    }
  }

  // Load more butonları
  $("#load-more-notifications").on("click", function () {
    if (hasMoreNotifications) {
      loadNotifications(currentNotificationPage + 1, true);
    }
  });

  $("#load-more-server-notifications").on("click", function () {
    if (window.hasMoreServerNotifications) {
      const serverId = window.currentServerId || window.location.pathname.split('/').pop();
      loadServerNotifications(serverId, window.currentServerNotificationPage + 1, true);
    }
  });

  // Bildirim butonları için event handler'ları
  $(document).on('click', '.notification-card button[title="Sil"]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const card = $(this).closest('.notification-card');
    const notificationId = card.attr('data-notification-id');
    if (notificationId) {
      if (window.currentServerId) {
        // Server detail sayfası için
        deleteServerNotification(notificationId);
      } else {
        // Ana sayfa için modal göster
        showDeleteSingleModal(notificationId);
      }
    }
  });

  $(document).on('click', '.notification-card button[title="Okundu olarak işaretle"]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const card = $(this).closest('.notification-card');
    const notificationId = card.attr('data-notification-id');
    if (notificationId) {
      if (window.currentServerId) {
        // Server detail sayfası için
        markServerNotificationAsRead(notificationId);
      } else {
        // Ana sayfa için normal işlem
        markAsRead(notificationId);
      }
    }
  });

  // Çoklu filtreleme sistemi
  function applyFilters() {
    // Reset to first page when filters change
    currentPage = 1;

    // Fetch servers with current filters
    fetchServers(currentPage, currentLimit, currentFilters);
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

  function clearAllFilters() {
    currentFilters = {
      status: '',
      location: '',
      panel: 'all',
      search: ''
    };

    // Reset form elements
    $('#searchInput').val('');
    $('#panelFilter').val('all');

    // Reset to first page
    currentPage = 1;

    // Fetch servers without filters
    fetchServers(currentPage, currentLimit, currentFilters);
  }

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
            $list.append(createUnifiedNotificationHtml(notif, true));
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

  // Event Handlers for Pagination and Filtering

  // Limit buttons (50, 100, 200)
  $(document).on('click', '.limit-btn', function () {
    const newLimit = parseInt($(this).data('limit'));
    if (newLimit !== currentLimit) {
      currentLimit = newLimit;
      currentPage = 1; // Reset to first page when changing limit
      fetchServers(currentPage, currentLimit, currentFilters);
    }
  });

  // Page navigation buttons
  $(document).on('click', '#firstPageBtn, #firstPageBtnBottom', function () {
    if (paginationData.has_prev) {
      fetchServers(1, currentLimit, currentFilters);
    }
  });

  $(document).on('click', '#prevPageBtn, #prevPageBtnBottom', function () {
    if (paginationData.has_prev) {
      fetchServers(currentPage - 1, currentLimit, currentFilters);
    }
  });

  $(document).on('click', '#nextPageBtn, #nextPageBtnBottom', function () {
    if (paginationData.has_next) {
      fetchServers(currentPage + 1, currentLimit, currentFilters);
    }
  });

  $(document).on('click', '#lastPageBtn, #lastPageBtnBottom', function () {
    if (paginationData.has_next) {
      fetchServers(paginationData.total_pages, currentLimit, currentFilters);
    }
  });

  // Page number buttons
  $(document).on('click', '.page-number-btn', function () {
    const page = parseInt($(this).data('page'));
    if (page !== currentPage) {
      fetchServers(page, currentLimit, currentFilters);
    }
  });

  // Filter event handlers
  $('.status-filter-btn').on('click', function () {
    const status = $(this).data('status');
    if (status === 'all') {
      currentFilters.status = '';
    } else {
      currentFilters.status = status;
    }
    applyFilters();
  });

  $('.location-filter-btn').on('click', function () {
    const location = $(this).data('location');
    if (location === 'all') {
      currentFilters.location = '';
    } else {
      currentFilters.location = location;
    }
    applyFilters();
  });

  $('#panelFilter').on('change', function () {
    const panel = $(this).val();
    currentFilters.panel = panel;
    applyFilters();
  });

  // Search with debounce
  let searchTimeout;
  $('#searchInput').on('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      currentFilters.search = $(this).val();
      applyFilters();
    }, 300);
  });

  // Clear filters button
  $('#clearFiltersBtn').on('click', clearAllFilters);

  // Initial load
  fetchServers(currentPage, currentLimit, currentFilters);

  updateNotificationCount();
});
