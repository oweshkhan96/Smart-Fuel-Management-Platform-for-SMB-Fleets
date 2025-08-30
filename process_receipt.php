<?php
// process_receipt.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['receipt'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}

try {
    // Read and encode the file
    $imageData = file_get_contents($file['tmp_name']);
    $imageB64 = base64_encode($imageData);
    
    // Determine MIME type
    $mimeType = $file['type'];
    if ($mimeType === 'image/jpg') {
        $mimeType = 'image/jpeg';
    }
    
    // OpenRouter API configuration
    $apiKey = "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";
    $url = "https://openrouter.ai/api/v1/chat/completions";
    
    $payload = [
        "model" => "google/gemini-2.5-pro",
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "Extract all text (OCR) from this receipt image. Return only the text, clean and readable. Focus on important details like store name, date, items, prices, and totals."
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:{$mimeType};base64,{$imageB64}"
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    $headers = [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json",
        "HTTP-Referer: " . $_SERVER['HTTP_HOST'],
        "X-Title: Fleet Receipt OCR"
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("API request failed with code: " . $httpCode);
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception("Unexpected API response format");
    }
    
    $extractedText = $result['choices'][0]['message']['content'];
    
    echo json_encode([
        'success' => true,
        'extracted_text' => $extractedText
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
