<?php
declare(strict_types=1);
namespace Core;

use Core\Exceptions\LayoutNotFoundException;
use Core\Exceptions\ViewNotFoundException;
use stdClass;
use Exception;
use Core\Lib\Utilities\ArraySet;
use Core\Lib\Utilities\Env;

/**
 * Handles operations related to views and its content.
 */
class View extends stdClass {
    private const APP_COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'components'.DS;
    private const APP_LAYOUT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'layouts'.DS;
    private const APP_VIEW_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS;
    private const APP_WIDGET_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'widgets'.DS;
    protected $_body;
    protected $_content = [];
    protected $_currentBuffer;
    private const FRAMEWORK_COMPONENT_PATH = CHAPPY_ROOT.DS.'views'.DS.'components'.DS;
    private const FRAMEWORK_LAYOUT_PATH = CHAPPY_ROOT.DS.'views'.DS.'layouts'.DS;
    private const FRAMEWORK_VIEW_PATH = CHAPPY_ROOT.DS.'views'.DS;
    protected $_head;
    protected $_layout;
    protected $_outputBuffer;
    protected $_siteTitle;
    protected array $widgets = [];
    
    /**
     * Default constructor.
     */
    public function __construct() {
        $this->_layout = Env::get('DEFAULT_LAYOUT', 'default'); // Default layout: 'default'
        $this->_siteTitle = Env::get('SITE_TITLE', 'My Website'); // Default site title
    }
    
    /**
     * Registers a new widget.
     *
     * @param string $section The category for the widget.
     * @param string $viewPath The widget described as parentDirectory.widgetName.
     * @param array $data Any data associated with the widget.
     * @return void
     */
    public function addWidget(string $section, string $viewPath, array $data = []): void {
        $this->widgets[$section][] = ['view' => $viewPath, 'data' => $data];
    }

    /**
     * Includes a component into a view.
     *
     * @param string $component The name of the component.
     * @param bool $frameworkComponentPath Uses path for component inside 
     * framework when true.
     * @return void
     */
    public function component(string $component, bool $frameworkComponentPath = false): void {
        $componentPath = !$frameworkComponentPath ? self::APP_COMPONENT_PATH . $component . '.php' :
            self::FRAMEWORK_COMPONENT_PATH.$component.'.php';
        
        if(!file_exists($componentPath)) {
            throw new Exception('The component "' . $component . '" does not exist');
        } else {
            require $componentPath;
        }
    }

    /**
     * The content of the page.  The two types are head and body.  If 
     * necessary, we can implement additional types of content.
     *
     * @param string $type The type of content we want to render.
     * @return mixed The type of content we want to render.  If it is not 
     * a known type of content we return false;
     */
    public function content(string $type): mixed {
        return ArraySet::make($this->_content)->get($type)->result() ?? false;
    }

    /**
     * Sets the end for a particular section of content.  When called it 
     * takes _outputBuffer, cleans it, and outputs it to the screen.  In the 
     * absence of a previous call to the start() function a message requesting 
     * you to call start() is displayed.
     *
     * @return void
     */
    public function end(): void {
        if(!empty($this->_currentBuffer)){
            $this->_content[$this->_currentBuffer] = ob_get_clean();
            $this->_currentBuffer = null;
        } else {
            die('You must first run the start method.');
        }
    }

    /**
     * Performs render operations for a particular view.
     * Example input: home/index.
     * 
     * @param string $viewName The name of the view we want to render.
     * @param bool $frameworkViewPath When true we use built in controllers under Core, 
     * otherwise controllers defined by users are utilized.
     * @return void
     */
    public function render(string $viewName, bool $frameworkViewPath = false, bool $frameworkLayoutPath = false): void {
        $viewArray = explode('.', $viewName);
        $viewString = implode(DS, $viewArray) . '.php';
        $layoutString = $this->_layout . '.php';

        $viewPath = !$frameworkViewPath ? self::APP_VIEW_PATH . $viewString : self::FRAMEWORK_VIEW_PATH . $viewString;

        $layoutPath = !$frameworkLayoutPath ? self::APP_LAYOUT_PATH . $layoutString :
            self::FRAMEWORK_LAYOUT_PATH . $layoutString;
            
        if (!file_exists($layoutPath)) {
            throw new LayoutNotFoundException('The layout "' . $this->_layout . '" does not exist');
        }

        if (file_exists($viewPath)) {
            require $viewPath;
            require $layoutPath;
        } else {
            throw new ViewNotFoundException('The view "' . $viewName . '" does not exist');
        }
    }

    public function renderJsx(string $viewComponent, array $props = [], string $entry = 'resources/js/app.jsx'): void
    {
        // allow existing controller style ($this->view->props) to keep working
        if (empty($props) && isset($this->props) && is_array($this->props)) {
            $props = $this->props;
        }

        $this->component = $viewComponent; // e.g., 'Home' or 'Admin/Users'
        $this->props     = $props;
        $this->entry     = $entry;     // allow swapping bundles if you add more

        // Reuse your normal renderer with a single host template
        $this->render('react.host', true);   // maps to resources/view/react/host.php
    }

    /**
     * Renders a single widget view file with given data.
     *
     * @param string $viewPath Relative to resources/views/, without .php
     *                         e.g., 'widgets/dashboard/revenueCard'
     * @param array $data Data to extract into the view.
     * @return string Rendered HTML output.
     */
    public function renderWidget(string $viewPath, array $data = []): string
    {
        $file = self::APP_WIDGET_PATH . str_replace('.', DS, $viewPath) . '.php';

        if (!file_exists($file)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $file;
        return ob_get_clean();
    }
    
    /**
     * Renders widgets for a given section.
     *
     * @param string $slot Widget slot name (e.g., 'dashboard.cards').
     * @param array $widgets All registered widgets (usually $this->widgets or WidgetRegistry::getAll()).
     * @return string Rendered HTML for the widget slot.
     */
    public function renderWidgets(string $slot, array $widgets = []): string {
        $output = '';

        if (!isset($widgets[$slot]) || empty($widgets[$slot])) {
            return $output;
        }

        foreach ($widgets[$slot] as $widget) {
            $view = $widget['view'] ?? '';
            $data = $widget['data'] ?? [];
            $output .= $this->renderWidget($view, $data);
        }

        return $output;
    }

    /**
     * Sets the layout for the view.
     *
     * @param string $path The path for our view.
     * @return void
     */
    public function setLayout(string $path): void {
        $this->_layout = $path;
    }

    /**
     * Setter function for site title of current page.
     *
     * @param string $title The site title for a particular page.
     * @return void
     */
    public function setSiteTitle(string $title): void {
        $this->_siteTitle = $title;
    }

    /**
     * Getter function for current page's site title.
     *
     * @return string The site title for a particular page.
     */
    public function siteTitle(): string {
        return $this->_siteTitle;
    }

    /**
     * When called this function establishes the beginning for a section 
     * of content.  Anything between calls of this function and end() will be 
     * included in our view.
     *
     * @param string $type The name of the type of content we want to include 
     * in our view.
     * @return void
     */
    public function start(string $type): void {
        if(empty($type)) die('you must define a type');
        $this->_currentBuffer = $type;
        ob_start();
    } 
}