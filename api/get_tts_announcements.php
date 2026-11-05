<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
authorizeRoles(['Doctor/Staff', 'Display', 'Patient']);

date_default_timezone_set('Asia/Manila');

$pendingAudio = [];

try {
    if (!isset($_SESSION['announced_queues'])) {
        $_SESSION['announced_queues'] = [];
    }

    // Fetch newly accepted queues within the last 10 seconds
    $ttsQuery = "SELECT qi.queue_info_id, qi.queue_code, qi.service_snapshot, 
                        TRIM(CONCAT(pr.firstname, ' ', COALESCE(CONCAT(pr.middlename, ' '), ''), pr.lastname)) AS fullname
                 FROM queue_information qi
                 LEFT JOIN patient_records pr ON qi.patient_id = pr.patient_id
                 WHERE qi.status = 'In Progress' 
                   AND qi.accepted_at >= (NOW() - INTERVAL 10 SECOND)
                 ORDER BY qi.accepted_at ASC";

    $ttsResult = $conn->query($ttsQuery);

    if ($ttsResult && $ttsResult->num_rows > 0) {
        $apiKeys = ['', '']; 
        $voiceId = 'XrExE9yKIg1WjnnlVkGX'; 

        $digitMap = [
            '0' => 'Zero', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four',
            '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine'
        ];

        while ($row = $ttsResult->fetch_assoc()) {
            $queueId = intval($row['queue_info_id']);

            if (in_array($queueId, $_SESSION['announced_queues'])) {
                continue;
            }

            // Mark as announced in session
            $_SESSION['announced_queues'][] = $queueId;
            if (count($_SESSION['announced_queues']) > 50) {
                array_shift($_SESSION['announced_queues']);
            }

            $queueCode = $row['queue_code'];
            $serviceName = $row['service_snapshot'] ?? 'Medical Consultation';
            $patientName = $row['fullname'] ?? 'Patient';

            // Format code characters (e.g., "A, Zero, One, Five")
            $chars = str_split($queueCode);
            $formattedParts = [];
            foreach ($chars as $char) {
                $formattedParts[] = $digitMap[$char] ?? strtoupper($char);
            }
            $formattedCode = implode(', ', $formattedParts);

            $textToSpeak = "Tinatawagan. Queue number {$formattedCode}. {$patientName}, maaari na po kayong pumunta sa Istasyon para sa {$serviceName}. Salamat.";

            $base64Audio = null;
            foreach ($apiKeys as $apiKey) {
                if (empty($apiKey) || stripos($apiKey, 'APIKEY') !== false) continue;

                $payload = json_encode([
                    "text" => $textToSpeak,
                    "model_id" => "eleven_multilingual_v2",
                    "voice_settings" => ["stability" => 0.5, "similarity_boost" => 0.75]
                ]);

                $ch = curl_init("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Accept: audio/mpeg",
                    "Content-Type: application/json",
                    "xi-api-key: {$apiKey}"
                ]);

                $audioResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($audioResponse !== false && $httpCode === 200) {
                    $base64Audio = base64_encode($audioResponse);
                    break;
                }
            }

            if ($base64Audio) {
                $pendingAudio[] = $base64Audio;
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'pending_audio' => $pendingAudio
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>