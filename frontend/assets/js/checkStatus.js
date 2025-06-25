import { API_BASE_URL } from './config.js';

document.addEventListener('DOMContentLoaded', () => {
  const refreshButton = document.getElementById("refreshButton");
  const spinner = document.getElementById("spinner");
  const refreshIcon = document.getElementById("refreshIcon");

  if (!refreshButton || !spinner || !refreshIcon) return;

  refreshButton.addEventListener("click", async () => {
    spinner.classList.remove("hidden");
    refreshIcon.classList.add("hidden");
    refreshButton.disabled = true;

    try {
      const response = await fetch(`${API_BASE_URL()}/check`);
      if (!response.ok) {
        throw new Error("server error");
      }

      const result = await response.json();
      window.location.reload();
    } catch (err) {
      console.error("error:", err);
    } finally {
      spinner.classList.add("hidden");
      refreshIcon.classList.remove("hidden");
      refreshButton.disabled = false;
    }
  });
});
