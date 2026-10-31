
const toastSoundSettings = {
    enabled: true,
    volume: 0.12
};

let toastAudioContext = null;

// Initialize audio context
function getToastAudioContext() {
    const AudioContextClass =
        window.AudioContext || window.webkitAudioContext;

    if (!AudioContextClass) return null;

    if (!toastAudioContext) {
        toastAudioContext = new AudioContextClass();
    }

    return toastAudioContext;
}

// Play an individual synthesized tone
function playToastTone(
    frequency,
    startTime,
    duration,
    waveform = "sine",
    volume = 0.12
) {
    const context = getToastAudioContext();

    if (!context || context.state !== "running") return;

    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = waveform;
    oscillator.frequency.setValueAtTime(
        frequency,
        startTime
    );

    const level = Math.max(
        0.001,
        Math.min(volume, 0.5)
    );

    gain.gain.setValueAtTime(0.0001, startTime);

    gain.gain.exponentialRampToValueAtTime(
        level,
        startTime + 0.015
    );

    gain.gain.exponentialRampToValueAtTime(
        0.0001,
        startTime + duration
    );

    oscillator.connect(gain);
    gain.connect(context.destination);

    oscillator.start(startTime);
    oscillator.stop(startTime + duration + 0.01);

    oscillator.onended = () => {
        oscillator.disconnect();
        gain.disconnect();
    };
}

// Play a sound based on toast type
async function playToastSound(type = "info") {
    if (!toastSoundSettings.enabled) return;

    try {
        const context = getToastAudioContext();
        if (!context) return;

        // Browsers may block audio before user interaction.
        if (context.state === "suspended") {
            await context.resume();
        }

        if (context.state !== "running") return;

        const now = context.currentTime + 0.01;
        const volume = toastSoundSettings.volume;

        switch (type) {

            case "success":
                // Pleasant ascending chime
                playToastTone(
                    523.25, now, 0.17, "sine", volume
                );
                playToastTone(
                    783.99, now + 0.13, 0.25, "sine", volume
                );
                break;

            case "error":
                // Descending alert
                playToastTone(
                    392, now, 0.17, "triangle", volume
                );
                playToastTone(
                    261.63, now + 0.15, 0.28,
                    "triangle", volume
                );
                break;

            case "warning":
                // Two attention pulses
                playToastTone(
                    440, now, 0.12, "triangle", volume
                );
                playToastTone(
                    440, now + 0.19, 0.12,
                    "triangle", volume
                );
                break;

            case "info":
            default:
                // Soft single chime
                playToastTone(
                    659.25, now, 0.22, "sine", volume
                );
                break;
        }

    } catch (error) {
        // Never interrupt a toast because audio is blocked.
        console.debug("Toast audio unavailable:", error);
    }
}

// Unlock audio after a user gesture.
// Useful for toast notifications triggered by AJAX requests.
function unlockToastAudio() {
    const context = getToastAudioContext();

    if (context && context.state === "suspended") {
        context.resume().catch(() => {});
    }
}

document.addEventListener(
    "pointerdown",
    unlockToastAudio,
    { once: true }
);

document.addEventListener(
    "keydown",
    unlockToastAudio,
    { once: true }
);



function showToast(message, type = "info") {
    const container = document.getElementById("toastContainer");

    if (!container) return;

    // Supported toast types
    const toastTypes = {
        success: {
            title: "Success",
            icon: `
                <circle cx="12" cy="12" r="10"/>
                <path d="m8 12 3 3 5-6"/>
            `
        },
        error: {
            title: "Error",
            icon: `
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4"/>
                <path d="M12 16h.01"/>
            `
        },
        info: {
            title: "Information",
            icon: `
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 11v5"/>
                <path d="M12 8h.01"/>
            `
        },
        warning: {
            title: "Warning",
            icon: `
                <path d="M10.3 3.5 1.8 18.2
                         A2 2 0 0 0 3.5 21h17
                         a2 2 0 0 0 1.7-2.8L13.7 3.5
                         a2 2 0 0 0-3.4 0Z"/>
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
            `
        }
    };

    // Fallback to info if unknown type
    type = String(type || "info").toLowerCase();
    if (!toastTypes[type]) type = "info";

    const config = toastTypes[type];
    const duration = 4000;
    playToastSound(type);

    // Create toast
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.setAttribute("role", type === "error" ? "alert" : "status");
    toast.style.setProperty("--toast-duration", `${duration}ms`);

    // Icon
    const icon = document.createElement("div");
    icon.className = "toast-icon";
    icon.innerHTML = `
        <svg viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg"
             aria-hidden="true">
            ${config.icon}
        </svg>
    `;

    // Content
    const content = document.createElement("div");
    content.className = "toast-content";

    const title = document.createElement("strong");
    title.className = "toast-title";
    title.textContent = config.title;

    const description = document.createElement("span");
    description.className = "toast-message";
    description.textContent = String(message ?? "");

    content.append(title, description);

    // Close button
    const closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "toast-close";
    closeButton.setAttribute("aria-label", "Dismiss notification");
    closeButton.innerHTML = `
        <svg viewBox="0 0 24 24"
             aria-hidden="true">
            <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
    `;

    // Progress bar
    const progress = document.createElement("div");
    progress.className = "toast-progress";
    progress.setAttribute("aria-hidden", "true");

    toast.append(icon, content, closeButton, progress);
    container.appendChild(toast);

    let isClosing = false;
    let removalTimer;

    // Smooth removal
    function removeToast() {
        if (isClosing) return;
        isClosing = true;

        clearTimeout(removalTimer);

        toast.style.animation =
            "toast-out 300ms ease forwards";

        setTimeout(() => {
            toast.remove();
        }, 300);
    }

    // Close manually
    closeButton.addEventListener("click", removeToast);

    // Automatically close after 4 seconds
    removalTimer = setTimeout(removeToast, duration);
}

(function () {
    const params = new URLSearchParams(window.location.search);

    if (!params.has("msg")) return;

    const message = params.get("msg") || "";
    const type = params.get("type") || "info";

    showToast(message, type);

    // Remove only toast-related parameters
    if (window.history.replaceState) {
        const url = new URL(window.location.href);

        url.searchParams.delete("msg");
        url.searchParams.delete("type");

        window.history.replaceState(
            null,
            "",
            url.pathname + url.search + url.hash
        );
    }
})();
