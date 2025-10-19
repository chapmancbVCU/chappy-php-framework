<?php
declare(strict_types=1);
namespace Core\Lib\Http;
use Throwable;
use Core\FormHelper;
use Core\Lib\Logging\Logger;
trait JsonResponse {

    public function apiCsrfCheck(): bool {
        if(!FormHelper::checkToken($this->get('csrf_input'))) {
            Logger::log("corrupt token");
            return false;
            
        }
        return true;
    }

    public function get(string|null $input = null) {
        $raw = file_get_contents('php://input') ?: '';
        $foo = json_decode($raw, true);
        if(!$input) return $foo;

        return $foo[$input];
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
     * Sample jsonResponse for supporting AJAX requests.
     *
     * @param mixed $data The JSON response.
     * @return void
     */
    public function jsonResponse(mixed $data, int $status = 200, array $extraHeaders = []): void {
        // CORS - keep '*' only for public, no-credentials endpoints
        $headers = [
            'Access-Control-Allow-Origin'   => '*',
            'Content-Type'                  => 'application/json; charset=UTF-8',
            'Cache-control'                 => 'no-store'
        ]  + $extraHeaders;

        foreach($headers as $k => $v) {
            header("$k: $v");
        }

        http_response_code($status);

        $flags = JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        if(env('APP_ENV', 'production') !== 'production') {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            echo json_encode($data, $flags | JSON_THROW_ON_ERROR);
        } catch(Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'JSON encoding error'], $flags);
        }
        exit;
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
}