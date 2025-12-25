<?php
declare(strict_types=1);
namespace Core\Exceptions\View;
use Core\Exceptions\FrameworkException;

/**
 * Class to handle events related to layouts not being found.
 */
final class LayoutNotFoundException extends ViewException {}