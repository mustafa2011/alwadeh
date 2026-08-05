<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Services\InvoiceService;
try {
    $input = file_get_contents("php://input");
    $noteData = json_decode(
        $input,
        true
    );
    if (!is_array($noteData)) {
        throw new Exception(
            "Invalid note data."
        );
    }
    $service = new InvoiceService();
    $result = $service->createNote(
        $noteData
    );
    echo json_encode(
        [
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data']
        ],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(
        [
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
}