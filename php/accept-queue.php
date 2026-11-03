<?php
// Suppress unexpected PHP output that could break JSON formatting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

date_default_timezone_set('Asia/Manila');
$queue_info_id = isset($_POST['queue_info_id']) ? intval($_POST['queue_info_id']) : 0;

if ($queue_info_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Queue ID provided.']);
    exit;
}

try {
    // 1. Fetch queue code, patient name, and service snapshot from the database
    $query = "SELECT qi.service_snapshot, qi.queue_code,
                    TRIM(CONCAT(
                        pr.firstname, ' ', 
                        COALESCE(CONCAT(pr.middlename, ' '), ''), 
                        pr.lastname, 
                        COALESCE(CONCAT(' ', pr.suffix), '')
                    )) AS fullname
              FROM queue_information qi
              LEFT JOIN patient_records pr on qi.patient_id = pr.patient_id
              WHERE qi.queue_info_id = ?";
              
    $fetchStmt = $conn->prepare($query);
    $fetchStmt->bind_param("i", $queue_info_id);
    $fetchStmt->execute();
    $resultData = $fetchStmt->get_result();
    
    if ($resultData->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
        exit;
    }
    
    $row = $resultData->fetch_assoc();
    $queueCode = $row['queue_code'] ?? '';
    $serviceName = $row['service_snapshot'] ?? 'Medical Consultation';
    $patient_name = $row['fullname'] ?? 'Patient';
    $fetchStmt->close();

    // 2. Update status to 'In Progress'
    $stmt = $conn->prepare("UPDATE queue_information SET status = 'In Progress', accepted_at = NOW() WHERE queue_info_id = ?");
    $stmt->bind_param("i", $queue_info_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            
            // 3. Format queue code characters for natural reading (e.g., "A, Zero, One, Five")
            $digitMap = [
                '0' => 'Zero', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four',
                '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine'
            ];
            
            $chars = str_split($queueCode);
            $formattedParts = [];
            foreach ($chars as $char) {
                $formattedParts[] = $digitMap[$char] ?? strtoupper($char);
            }
            $formattedCode = implode(', ', $formattedParts);

            // Construct exact announcement text structure
            $textToSpeak = "Queue number {$formattedCode}. {$patient_name}, maaari na po kayong pumunta sa Istasyon para sa {$serviceName}, Salamat.";

            // 4. API Key Fallback Array (Replace with your actual active ElevenLabs API keys)
            $apiKeys = [
                'APIKEY1',   // Main Account Key
                'APIKEY2'    // Fallback Account Key (Used if primary hits limit or fails)
            ];

            $voiceId = 'XrExE9yKIg1WjnnlVkGX'; // Your verified ElevenLabs Voice ID
            $base64Audio = null;
            $ttsError = null;

            // Iterate through keys until one successfully generates audio (HTTP 200)
            foreach ($apiKeys as $apiKey) {
                // Skip empty slots or unconfigured placeholder strings
                if (empty($apiKey) || stripos($apiKey, 'APIKEY') !== false || stripos($apiKey, 'YOUR_') !== false) {
                    continue;
                }

                $payload = json_encode([
                    "text" => $textToSpeak,
                    "model_id" => "eleven_multilingual_v2",
                    "voice_settings" => [
                        "stability" => 0.5,
                        "similarity_boost" => 0.75
                    ]
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
                $curlError = curl_error($ch);
                curl_close($ch);

                // If successful, encode audio and break loop
                if ($audioResponse !== false && $httpCode === 200) {
                    $base64Audio = base64_encode($audioResponse);
                    $ttsError = null;
                    break;
                }

                $ttsError = [
                    'http_code' => $httpCode,
                    'connection_error' => $curlError ?: null
                ];

                error_log(
                    'ElevenLabs request failed. HTTP ' .
                    $httpCode .
                    '; cURL: ' . $curlError
                );
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Patient status updated to In Progress.',
                'audio' => $base64Audio,
                'tts_debug' => $ttsError
            ]);

        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Record not found or status already updated.'
            ]);
        }
    } else {
        throw new Exception($stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database or TTS error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>