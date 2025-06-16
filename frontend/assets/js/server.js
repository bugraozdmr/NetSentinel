import { API_BASE_URL, APP_NAME } from "./config.js";

$(document).ready(function () {
  const $tbody = $("#serverTableBody");
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

    const bars = normalized.map((check) => {
      let color = "bg-gray-300";
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
      <div class="group relative cursor-default">
        <span class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 text-white text-[10px] px-1 py-[1px] opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none select-none">
          ${tooltip}
        </span>
        <div class="w-3 h-5 ${color} rounded-sm group-hover:-translate-y-[2px] transition-transform"></div>
      </div>
    `;
    });

    return `<div class="flex gap-[2px] justify-end items-end">${bars.join(
      ""
    )}</div>`;
  }

  function renderTable(servers) {
    $tbody.empty();

    if (servers.length === 0) {
      $tbody.append(`
      <tr>
        <td colspan="8" class="text-center py-6 text-slate-400">
          Arama kriterlerine uygun sunucu bulunamadı.
        </td>
      </tr>`);
      return;
    }

    servers.forEach((server) => {
      const statusText = server.is_active == 1 ? "Running" : "Passive";
      const statusColor =
        server.is_active == 1 ? "text-green-600" : "text-red-600";
      const checks = renderStatusBars(server.last_checks);
      const lastCheck = server.last_check_at
        ? formatDate(server.last_check_at)
        : "Henüz kontrol edilmedi";

      const ports = Array.isArray(server.ports)
        ? server.ports.map((port) => ({
            number: port.port_number,
            isOpen: port.is_open === 1 || port.is_open === true,
          }))
        : [];

      $tbody.append(`
        <tr class="hover:bg-slate-50 border-b border-slate-200 transition-colors">
          <td class="p-4 py-5 text-sm font-semibold text-slate-800">${
            server.ip
          }</td>
          <td class="p-4 py-5 text-sm text-slate-500">${server.name}</td>
          <td class="p-4 py-5 text-sm text-slate-500">${server.assigned_id}</td>
          <td class="p-4 py-5 text-sm text-slate-500">${server.location}</td>
          <td class="p-4 py-5 text-sm font-semibold ${statusColor}">${statusText}</td>
          <td class="p-4 py-5 text-sm text-slate-500">${lastCheck}</td>
          <td class="p-4 py-5 text-sm">
            <div class="flex flex-row-reverse gap-1 justify-end">
              ${checks}
            </div>
          </td>
          <td class="p-4 py-5 text-sm text-slate-600 flex gap-2 items-center">
            <button
              type="button"
              title="Detayları Gör"
              aria-label="Detayları Gör"
              data-id="${server.id}"
              class="detail-btn inline-flex items-center justify-center size-9 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition"
            >
              <i class="fa fa-eye"></i>
            </button>

            <button
              type="button"
              title="Düzenle"
              aria-label="Düzenle"
              data-id="${server.id}"
              class="edit-btn inline-flex items-center justify-center size-9 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition"
            >
              <i class="fa fa-pen"></i>
            </button>

            <button
              type="button"
              title="Sil"
              aria-label="Sil"
              data-id="${server.id}"
              class="inline-flex items-center justify-center size-9 rounded-full bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 transition delete-btn"
            >
              <i class="fas fa-trash-alt text-base"></i>
            </button>
          </td>
        </tr>

        <tr class="bg-gray-50 border-b border-gray-200">
          <td colspan="8" class="p-3">
            <div class="flex flex-wrap gap-2 items-center">
              ${ports
                .map(
                  (port) => `
                <div class="flex items-center gap-1 bg-white px-3 py-1 rounded-full shadow-sm border ${
                  port.isOpen
                    ? "border-green-400 bg-green-50 text-green-700"
                    : "border-red-400 bg-red-50 text-red-700"
                }">
                  <span class="font-semibold">${port.number}</span>
                  <span class="w-3 h-3 rounded-full ${
                    port.isOpen ? "bg-green-500" : "bg-red-500"
                  }"></span>
                </div>
              `
                )
                .join("")}
            </div>
          </td>
        </tr>
      `);
    });
  }

  function fetchServers() {
    $loading.removeClass("hidden");
    $.ajax({
      url: `${API_BASE_URL}/servers`,
      method: "GET",
      dataType: "json",
      success: function (data) {
        allServers = data.servers || [];
        renderTable(allServers);
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
    renderTable(filtered);
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
        renderTable(allServers);
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
          $("#assigned_id").val(server.assigned_id);
          $("#location").val(server.location);

          const activePorts = Array.isArray(server.ports)
            ? server.ports.map((p) => String(p.port_number))
            : [];

          $(".port-checkbox").each(function () {
            const portVal = $(this).val();
            if (activePorts.includes(portVal)) {
              $(this).prop("checked", true);
            } else {
              $(this).prop("checked", false);
            }
          });

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

  // DETAILS
  let pingChart = null;

  $(document).on("click", ".detail-btn", function () {
    const serverId = $(this).data("id");
    if (serverId) {
      window.location.href = `/${APP_NAME}/server/detail/${serverId}`;
    }
  });

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

      $("#loading").hide();
      $("#serverDetail").removeClass("hidden");
    }).fail(function () {
      $("#loading").text("Veri yüklenemedi.");
    });
  });

  fetchServers();
});
