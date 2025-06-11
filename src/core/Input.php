<?php
declare(strict_types=1);
namespace Core;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\{FormHelper, Router};

/**
 * Input class handles requests to the server.
 */
class Input {
    /**
     * Checks csrf token to determine if it was tampered with.  If the check 
     * fails the user is routed to a view stating access is restricted.
     *
     * @return bool Returns true if check passes.
     */
    public function csrfCheck(): bool {
        if(!FormHelper::checkToken($this->get('csrf_token'))) 
            Router::redirect('restricted/badToken');
        return true;
    }
    
    /**
     * Supports operations related to handling POST and GET requests.
     *
     * @param string|null $input Field name from POST/GET request, or null to get all
     * @return array|string Sanitized input as array or string
     */
    public function get(string|null $input = null): array|string {
        if (!$input) {
            // Return entire request array and sanitize it
            $data = [];
            foreach ($_REQUEST as $field => $value) {
                if (Arr::isArray($value)) {
                    // Recursively sanitize arrays
                    $data[$field] = Arr::map($value, [FormHelper::class, 'sanitize']);
                } else {
                    // Only trim if it's a string
                    $data[$field] = trim(FormHelper::sanitize($value));
                }
            }
            return $data;
        }
    
        // Handle single input field
        if (isset($_REQUEST[$input])) {
            $value = $_REQUEST[$input];
            if (Arr::isArray($value)) {
                return Arr::map($value, [FormHelper::class, 'sanitize']);
            }
            return trim(FormHelper::sanitize($value));
        }
    
        return '';
    }
    

    /**
     * Returns the request element within the $_SERVER superglobal array.
     *
     * @return string The type of request stored in the REQUEST_METHOD element 
     * within the $_SERVER superglobal array.
     */
    public function getRequestMethod(): string {
        return Str::upper($_SERVER['REQUEST_METHOD']);
    }


    public function isDelete(): bool {
        return $this->getRequestMethod() === 'DELETE';
    }

    /**
     * Checks if the REQUEST_METHOD is GET.
     *
     * @return bool True if the request method is GET.
     */
    public function isGet(): bool {
        return $this->getRequestMethod() === 'GET';
    } 

    /**
     * Checks if the REQUEST_METHOD is POST.
     *
     * @return boolean True if the request method is POST.
     */
    public function isPost(): bool {
        return $this->getRequestMethod() === 'POST';
    }

    /**
     * Checks if the REQUEST_METHOD is PUT.
     *
     * @return boolean True if the request method is PUT.
     */
    public function isPut(): bool {
        return $this->getRequestMethod() === 'PUT';
    } 
}