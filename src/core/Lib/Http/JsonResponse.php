<?php
declare(strict_types=1);
namespace Core\Lib\Http;

use Core\Exceptions\FrameworkException;
use Core\FormHelper;
use Core\Lib\Utilities\Arr;

/**
 * A trait that support operations related to APIs and their associated 
 * JSON responses.
 */
trait JsonResponse {
    /** 
     * Raw JSON override for tests 
     * 
     * @var string|null 
     */
    public static ?string $rawInputOverride = null;

    /** 
     * When true, do not send headers or exit; just echo JSON and return. 
     * 
     * @var bool
     */
    public static bool $testing = false;

    /** 
     * Captured status for tests (optional convenience). 
     * 
     * @var int
     */
    public static int $lastStatus = 200;

    /** 
     * Captured headers for tests (optional convenience). 
     * 
     * @var array
     */
    public static array $lastHeaders = [];
    
    /**
     * Checks if CSRF token has been tampered with.
     *
     * @return bool True if token is valid, otherwise we return false.
     */
    public function apiCsrfCheck(): bool {
        if(!FormHelper::checkToken($this->get('csrf_token'))) {
            return false; 
        }
        return true;
    }

    /**
     * Supports operations related to handling POST and GET requests.  
     * Similar in behavior to the get function from the Input class but for 
     * JSON related operations.
     *
     * @param string|null $input Field name from POST/GET request, or null to get all
     * @return array|string Sanitized input as array or string
     */
    public function get(string|null $input = null): array| string {
        $raw = self::$rawInputOverride ?? (file_get_contents('php://input') ?: '');
        $data = json_decode($raw, true) ?: [];
        
        if(!$input) {
            foreach($data as $field => $value) {
                if(Arr::isArray($value)) {
                    // Recursively sanitize arrays
                    $data[$field] = Arr::map($value, [FormHelper::class, 'sanitize']);
                } else {
                    // Only trim if it's a string
                    $data[$field] = trim(FormHelper::sanitize($value));
                }
            }
            return $data;
        }
        if(isset($data[$input])) {
            $value =  $data[$input];
            if (Arr::isArray($value)) {
                return Arr::map($value, [FormHelper::class, 'sanitize']);
            }
            return trim(FormHelper::sanitize($value));
        }

        return '';
    }

    /**
     * Makes JSON Response for error payloads.
     *
     * @param string $message The error message.
     * @param integer $status The status code.
     * @param array $errors The array of errors.
     * @return void
     */
    public function jsonError(string $message, int $status = 400, array $errors = []): void {
        $this->jsonResponse(
            ['success' => false, 'message' => $message, 'errors' => $errors], 
            $status
        );
    }

    /**
     * Sends a JSON response with headers and status code.
     *
     * @param mixed $data The JSON response.
     * @param int $status The status code.
     * @param array $extraHeaders Any extra headers.
     * @return void
     */
    public function jsonResponse(mixed $data, int $status = 200, array $extraHeaders = []): void
    {
        // CORS - keep '*' only for public, no-credentials endpoints
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type'                => 'application/json; charset=UTF-8',
            'Cache-control'               => 'no-store',
        ] + $extraHeaders;

        // Capture for tests (handy for assertions)
        self::$lastStatus  = $status;
        self::$lastHeaders = $headers;

        // In tests, don't mutate PHP's global response state or terminate execution
        if (!self::$testing) {
            foreach ($headers as $k => $v) {
                header("$k: $v");
            }
            http_response_code($status);
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        if (env('APP_ENV', 'production') !== 'production') {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            echo json_encode($data, $flags | JSON_THROW_ON_ERROR);
        } catch (FrameworkException|\Throwable $e) {
            // In production, reflect failure via status code; in tests, just emit error JSON
            if (!self::$testing) {
                http_response_code(500);
            }
            echo json_encode(
                ['success' => false, 'message' => 'JSON encoding error'],
                $flags
            );
        }

        if (!self::$testing) {
            exit;
        }
    }

    /**
     * Respond to CORS preflight.
     *
     * @return void
     */
    public function preflight(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token');
        http_response_code(204);
        exit;
    }

    /**
     * Inject JSON body by providing payload parameter.  Also performs cleanup 
     * to avoid leaking into other tests.
     *
     * @param string|false|null $payload Result of json_encode or null.
     * @return void
     */
    public static function setRawInputOverride(string|false|null $payload = null): void {
        self::$rawInputOverride = $payload;
    }
}