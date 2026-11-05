document.addEventListener("DOMContentLoaded", () => {
  // Queue to hold incoming audio base64 strings sequentially
  const audioQueue = [];
  let isAudioPlaying = false;

  /**
   * Process and play queued TTS announcements sequentially without overlap.
   */
  function processAudioQueue() {
    if (isAudioPlaying || audioQueue.length === 0) {
      return;
    }

    isAudioPlaying = true;
    const nextAudioBase64 = audioQueue.shift();
    const audio = new Audio("data:audio/mpeg;base64," + nextAudioBase64);

    // When current announcement finishes, play the next one automatically
    audio.onended = () => {
      isAudioPlaying = false;
      processAudioQueue();
    };

    // Fallback handling if audio playback fails or encounters an error
    audio.onerror = (err) => {
      console.error("TTS Playback Error:", err);
      isAudioPlaying = false;
      processAudioQueue();
    };

    audio.play().catch((playError) => {
      console.warn(
        "Audio autoplay prevented by browser. Click page to enable:",
        playError,
      );
      isAudioPlaying = false;
      processAudioQueue();
    });
  }

  /**
   * Poll dedicated TTS announcements endpoint.
   */
  async function pollTtsAnnouncements() {
    try {
      const response = await fetch("../api/get_tts_announcements.php");
      const result = await response.json();

      if (result.status === "success" && Array.isArray(result.pending_audio)) {
        result.pending_audio.forEach((audioBase64) => {
          if (audioBase64) {
            audioQueue.push(audioBase64);
          }
        });
        processAudioQueue();
      }
    } catch (error) {
      console.error("Error fetching TTS announcements:", error);
    }
  }

  /**
   * Fetch live queue status from backend API.
   */
  async function fetchLiveQueueData() {
    try {
      const response = await fetch("../api/get_queue_display_data.php");
      const result = await response.json();

      if (result.status === "success") {
        // Update display cards DOM
        if (Array.isArray(result.data)) {
          let visibleCount = 0;

          result.data.forEach((item) => {
            const col = document.querySelector(
              `.dept-column[data-service="${CSS.escape(item.service)}"]`,
            );
            if (!col) return;

            if (item.isVisible) {
              col.style.display = "flex";
              visibleCount++;
            } else {
              col.style.display = "none";
            }

            const servingList = col.querySelector(".serving-list");
            if (servingList) {
              servingList.innerHTML =
                item.active.length > 0
                  ? item.active
                      .map(
                        (code) =>
                          `<div class="ticket-box">${escapeHtml(code)}</div>`,
                      )
                      .join("")
                  : `<div class="no-queue">No Queue</div>`;
            }

            const waitingList = col.querySelector(".waiting-list");
            if (waitingList) {
              waitingList.innerHTML =
                item.waiting.length > 0
                  ? item.waiting
                      .map(
                        (code) =>
                          `<div class="ticket-box is-waiting">${escapeHtml(code)}</div>`,
                      )
                      .join("")
                  : `<div class="no-queue">No Waiting</div>`;
            }

            const avgVal = col.querySelector(".avg-time-val");
            if (avgVal) {
              avgVal.textContent = item.avgTime;
            }
          });

          const noServicesMsg = document.getElementById("no-services-msg");
          if (noServicesMsg) {
            noServicesMsg.style.display = visibleCount === 0 ? "flex" : "none";
          }
        }
      }
    } catch (error) {
      console.error("Error auto-refreshing queue cards:", error);
    }
  }

  /**
   * Utility to sanitize text strings for safe HTML rendering.
   */
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  // Initialize polling intervals every 3 seconds
  setInterval(fetchLiveQueueData, 3000);
  setInterval(pollTtsAnnouncements, 3000);
});