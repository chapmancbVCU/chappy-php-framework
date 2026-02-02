<?php
declare(strict_types=1);
namespace Core;
use Core\Session;
use App\Models\Users;
use Core\Exceptions\FrameworkException;
use Core\Services\ACLService;
use Core\Services\AuthService;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\ArraySet;

/**
 * This class is responsible for routing between views.
 */
class Router {
    /**
     * Gets link based on value from acl.
     * 
     * @param string $value item in acl that will be used to create a 
     * link.  
     * @return false|string False if the user does not have access to a 
     * controller or action.  Otherwise we return the value so we can create 
     * a link.
     */
    private static function get_link(string $value): string|false {
        // Check if external link just return it.
        if(preg_match('/https?:\/\//', $value) == 1) {
            return $value;
        } else {
            $uArray = explode('/', $value);
            $controller_name = Str::ucwords($uArray[0]);
            $action_name = (isset($uArray[1])) ? $uArray[1] : '';

            // Build link item only if the user has access.
            if(self::hasAccess($controller_name, $action_name)) {
                return Env::get('APP_DOMAIN', '/') . $value;
            } 
            return false;
        }
    }

    /**
     * Parses menu_acl.json file to determine menu contents depending on acl 
     * of user.
     * 
     * @param string $menu Name of menu acl file.
     * @return array The array of menu items.
     */
    public static function getMenu(string $menu): array {
        $menuArray = [];
        $menuFile = file_get_contents(CHAPPY_BASE_PATH . DS . 'app' . DS . $menu . '.json');
        $acl = json_decode($menuFile, true);
        
        foreach($acl as $key => $value) {
            // If array we will know if there is a dropdown or something else.
            if(Arr::isArray($value)) {
                $subMenu = [];
                foreach($value as $k => $v) {
                    /* Check if item is a separator and continue.  Don't what 
                     * to add separator as a link. */
                    if($k == 'separator' && !empty($subMenu)) {
                        $subMenu[$k] = '';
                        continue;
                    } else if($finalValue = self::get_link($v)) {
                        $subMenu[$k] = $finalValue;
                    }
                }
                if(!empty($subMenu)) {
                    $menuArray[$key] = $subMenu;
                }
            } else {
                if($finalValue = self::get_link($value)) {
                    $menuArray[$key] = $finalValue;
                }
            }
        }
        return $menuArray;
    }

    /**
     * Checks if user has access to a particular section of the site
     * and grants access if that is the case.
     * 
     * @param string $controller_name The name of the controller we want to 
     * test before granting the user access to a particular section of the 
     * site.
     * @param string $action_name The name of the action the user wants to 
     * perform.  The default value is "index".
     * @return bool $grantAccess True if we give access, otherwise false.
     */
    public static function hasAccess(string $controller_name, string $action_name = "index"): bool {
        $acl_file = file_get_contents(CHAPPY_BASE_PATH . DS . 'app' . DS . 'acl.json');
        $acl = json_decode($acl_file, true) ?? [];
        $grantAccess = false;
        $current_user_acls = ["Guest"]; // Default to Guest
    
        // Get current user
        if (Session::exists(Env::get('CURRENT_USER_SESSION_NAME'))) {
            $current_user_acls[] = "LoggedIn"; // Default to LoggedIn if user is authenticated
            $currentUser = AuthService::currentUser();
    
            if ($currentUser) {
                $username = Str::lower($currentUser->username); // Normalize username for ACL matching
                $userAcls = ACLService::aclsForUser($currentUser);
    
                // If the user has an ACL defined in the ACL file, add it
                if (isset($acl[$username])) {
                    $current_user_acls[] = $username;
                }
    
                // Add role-based ACLs (if any exist for this user)
                if (!empty($userAcls)) {
                    foreach ($userAcls as $userAcl) {
                        $current_user_acls[] = $userAcl;
                    }
                } else {
                    // If user has NO specific ACLs, they default to "LoggedIn"
                    $current_user_acls[] = "LoggedIn";
                }
            } else {
                Session::delete(Env::get('CURRENT_USER_SESSION_NAME')); // Remove invalid session
            }
        }
    
        // Remove empty ACLs to prevent errors
        $current_user_acls = array_filter($current_user_acls, fn($level) => !empty($level));
    
        // ✅ Grant access if ANY level allows it
        foreach ($current_user_acls as $level) {
            if (isset($acl[$level][$controller_name]) &&
                (in_array($action_name, $acl[$level][$controller_name]) || in_array("*", $acl[$level][$controller_name]))
            ) {
                $grantAccess = true;
                break;
            }
        }
    
        // ❌ Deny access if ANY level explicitly denies it
        foreach ($current_user_acls as $level) {
            if (isset($acl[$level]['denied'][$controller_name]) &&
                in_array($action_name, $acl[$level]['denied'][$controller_name])
            ) {
                $grantAccess = false;
                break;
            }
        }
    
        return $grantAccess;
    }
    

    /**
     * Performs redirect operations.
     * 
     * @param string $location The view where we will redirect the user.
     * @param array $params The parameters for the action.
     * @return void
     */
    public static function redirect(string $location, array $params = []): void {
        if (Env::get('APP_ENV') !== 'testing') {
            // Convert dot notation to slash notation if needed
            if (!str_starts_with($location, '/')) {
                if (str_contains($location, '.')) {
                    $location = '/' . str_replace('.', '/', $location);
                } else {
                    $location = '/' . $location;
                }
            }

            // Append parameters to the URL path
            if (!empty($params)) {
                $location .= '/' . implode('/', array_map('urlencode', $params));
            }

            $fullUrl = rtrim(Env::get('APP_DOMAIN', '/'), '/') . $location;

            if (!headers_sent()) {
                header('Location: ' . $fullUrl);
                exit();
            } else {
                echo '<script type="text/javascript">';
                echo 'window.location.href="' . $fullUrl . '";';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0;url=' . $fullUrl . '" />';
                echo '</noscript>';
                exit;
            }
        }
    }

    /**
     * Resolves namespaces for controllers.
     *
     * @param string $controllerShort
     * @return string|bool The controller in namespaced form if it exists.  
     * Otherwise we redirect.
     */
    private static function resolveControllerClass(string $controllerShort): string|bool {
        $appClass = 'App\\Controllers\\' . $controllerShort;
        $coreClass = 'Core\\Controllers\\' . $controllerShort;

        if (class_exists($appClass)) {
            return $appClass;
        }

        if (class_exists($coreClass)) {
            return $coreClass;
        }
  
        return false;
    }

    /**
     * Supports operations for routing.  It parses the url to determine which 
     * page needs to be rendered.  That path is parsed to determine 
     * the correct controller and action to use.
     * 
     * @return void
     */
    public static function route(): void
    {
        try {
            // Parse URLs
            $requestUri  = $_SERVER['REQUEST_URI'] ?? '';
            $requestPath = $_SERVER['PATH_INFO'] ?? (parse_url($requestUri, PHP_URL_PATH) ?: '');

            $url = ArraySet::make(explode('/', ltrim($requestPath, '/')))
                ->filter(fn($v) => trim($v) !== '')
                ->values();

            // Identify user
            $userId = Session::exists(Env::get('CURRENT_USER_SESSION_NAME')) 
                ? Session::get(Env::get('CURRENT_USER_SESSION_NAME')) 
                : 'Guest';

            // Log only critical requests
            if (!empty($_GET) || in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE']) || $userId === 'Guest') {
                info("Request: $requestPath | User: $userId");
            }

            // Extract controller
            $controller = $url->has(0)->result()
                ? Str::ucwords($url->first()->result()) . 'Controller'
                : Env::get('DEFAULT_CONTROLLER', 'Home') . 'Controller';
            $controller_name = Str::replace('Controller', '', $controller);
            $url->shift();

            // Extract action
            $action = $url->has(0)->result() 
                ? $url->first()->result() . 'Action'
                : 'indexAction';
            $action_name = $url->has(0)->result() 
                ? $url->first()->result() 
                : 'index';
            $url->shift();

            // Redirect to no controller found view if it doesn't exist.
            if(!self::resolveControllerClass($controller)) {
                redirect('restricted.noController', [$controller]);
            }

            // ACL check
            if (!self::hasAccess($controller_name, $action_name) && !method_exists($controller, $action)) {
                static $deniedAttempts = [];
                $key = "{$userId}_{$controller_name}_{$action_name}";

                if (!isset($deniedAttempts[$key])) {
                    $deniedAttempts[$key] = 1;
                    warning("Access Denied: User '$userId' tried '$controller_name/$action_name'");
                } else {
                    $deniedAttempts[$key]++;
                    if ($deniedAttempts[$key] <= 3) {
                        warning("Repeated Access Denied: User '$userId' ($deniedAttempts[$key] times) on '$controller_name/$action_name'");
                    }
                }

                $controller = Config::get('config.access_restricted') . 'Controller';
                $controller_name = Config::get('config.access_restricted');
                $action = 'indexAction';
            }

            // Prepare controller class with namespace
            $controller = self::resolveControllerClass($controller);
            $dispatch = new $controller($controller_name, $action);

            // Execute action
            if (method_exists($controller, $action)) {
                call_user_func_array([$dispatch, $action], $url->all());
            } else {
                redirect('restricted.notFound', [$action_name, $controller_name]);
            }
        } catch (FrameworkException $e) {
            error("Unhandled Exception in Router: " . $e->getMessage());
            throw $e;
        }
    }

}