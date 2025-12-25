<?php
declare(strict_types=1);
namespace Core\Exceptions\View;
use Core\Exceptions\View\ViewException;

/**
 * Class to handle events related to components not being found.
 */
final class ComponentNotFoundException extends ViewException {}