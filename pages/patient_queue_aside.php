<!-- Queue Aside Panel -->
<aside id="queueAsidePanel" class="queue-aside-panel">
    <button id="queueToggleBtn" class="queue-toggle-btn" title="Toggle Queue Info">
        <span class="toggle-text">My Queue</span>
    </button>

    <div class="queue-content-wrapper">
        <div class="queue-aside-header">
            <h3>Active Queue Status</h3>
            <button id="queueCloseBtn" class="queue-close-btn">&times;</button>
        </div>

        <!-- Content populated dynamically via patient_queue_aside.js -->
        <div id="queueAsideBody" class="queue-aside-body">
            <div class="queue-loading">Loading queue status...</div>
        </div>
    </div>
</aside>

<!-- External CSS for the Aside Widget -->
<link rel="stylesheet" href="../styles/patient_queue_aside.css">
<!-- External JS for Fetching and Toggle Logic -->
<script src="../js/patient_queue_aside.js" defer></script>