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
          <!-- Atanmış ID -->
          <div class="uppercase text-xs tracking-widest text-blue-400 mb-2 mt-8">${server.assigned_id || '-'}</div>
          <!-- Sunucu Adı ve IP -->
          <div class="text-2xl font-extrabold text-white mb-1">${server.name}</div>
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
        renderPanel(allServers);
      },
      error: function () {
        $error.removeClass("hidden");
      },
      complete: function () {
        $loading.addClass("hidden");
      },
    });
  }

  $search.on("input", function () {
    const keyword = $(this).val().toLowerCase();
    const filtered = allServers.filter((s) =>
      [s.ip, s.name, s.assigned_id, s.location].some((val) =>
        val.toLowerCase().includes(keyword)
      )
    );
    renderPanel(filtered);
  });

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
        assigned_id: $("#assigned_id").val().trim(),
        location: $("#location").val().trim(),
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

  function setLocationSafely(locationValue) {
    const $select = $("#location");

    const matched = $select.find(`option[value="${locationValue}"]`);

    if (matched.length) {
      $select.val(locationValue);
    } else {
      $select.append(
        $("<option>", {
          value: locationValue,
          text: locationValue,
          selected: true
        })
      );
    }
  }

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
          $("#assigned_id").val(server.assigned_id);

          const activePorts = Array.isArray(server.ports)
            ? server.ports.map((p) => String(p.port_number))
            : [];

          $(".port-checkbox").each(function () {
            const portVal = $(this).val();
            $(this).prop("checked", activePorts.includes(portVal));
          });

          setTimeout(() => {
            setLocationSafely(server.location);
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
      assigned_id: $("#assigned_id").val().trim(),
      location: $("#location").val().trim(),
      ports: $("input[name='ports[]']:checked")
        .map(function () {
          return parseInt($(this).val(), 10);
        })
        .get(),
    };

    $.ajax({
      url: `${API_BASE_URL}/servers/edit/${serverId}`,
      method: "PUT",
      contentType: "application/json",
      data: JSON.stringify(formData),
      success: function () {
        window.location.href = `/${APP_NAME}/`;
      },
      error: function (xhr) {
        const errMsg = xhr.responseJSON?.message || "Sunucu güncellenemedi.";
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
            // Duruma göre stil
            const isUnread = notif.status === "unread";

            const borderColor = isUnread
              ? "border-blue-400"
              : "border-gray-300";
            const bgColor = isUnread ? "bg-blue-50" : "bg-white";
            const fontWeight = isUnread ? "font-semibold" : "font-normal";
            const textColor = isUnread ? "text-gray-900" : "text-gray-700";

            const li = $(`
            <li class="border ${borderColor} rounded-lg p-4 mb-3 shadow-sm ${bgColor} hover:shadow-md transition-shadow duration-200 cursor-pointer">
              <p class="${fontWeight} ${textColor}">${notif.message}</p>
              <time class="block text-xs text-gray-500 mt-1" datetime="${notif.created_at
              }">
                ${new Date(notif.created_at).toLocaleString()}
              </time>
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
      $("#assigned_id").text(server.assigned_id);
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

  // Lokasyon filtreleme
  function setLocationFilter(selected) {
    $(".location-filter-btn").removeClass("bg-blue-600 text-white").addClass("bg-slate-800 text-blue-300");
    let filtered = allServers;
    if (selected === "Tümü") {
      $("#locationAllBtn").addClass("bg-blue-600 text-white").removeClass("bg-slate-800 text-blue-300");
      filtered = allServers;
    } else if (selected === "Mars") {
      $("#locationMarsBtn").addClass("bg-blue-600 text-white").removeClass("bg-slate-800 text-blue-300");
      filtered = allServers.filter(s => (s.location || '').toLowerCase().includes('mars'));
    } else if (selected === "Hetzner") {
      $("#locationHetznerBtn").addClass("bg-blue-600 text-white").removeClass("bg-slate-800 text-blue-300");
      filtered = allServers.filter(s => (s.location || '').toLowerCase().includes('hetzner'));
    }
    updateSummaryBar(filtered);
    renderPanel(filtered);
  }
  $(document).on("click", "#locationAllBtn", function () { setLocationFilter("Tümü"); });
  $(document).on("click", "#locationMarsBtn", function () { setLocationFilter("Mars"); });
  $(document).on("click", "#locationHetznerBtn", function () { setLocationFilter("Hetzner"); });
  // Sayfa yüklenince varsayılan olarak 'Tümü' seçili olsun
  setLocationFilter("Tümü");
});
