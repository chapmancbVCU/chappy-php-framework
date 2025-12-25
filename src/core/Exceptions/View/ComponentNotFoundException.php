<?php
declare(strict_types=1);
namespace Core\Exceptions\View;
use Core\Exceptions\FrameworkException;

/**
 * Class to handle events related to components not being found.
 */
final class ComponentNotFoundException extends ViewException {}