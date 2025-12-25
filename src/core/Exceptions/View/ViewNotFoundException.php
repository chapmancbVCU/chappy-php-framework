<?php
declare(strict_types=1);
namespace Core\Exceptions\View;
use Core\Exceptions\View\ViewException;

/**
 * Class to handle events related to views not being found.
 */
final class ViewNotFoundException extends ViewException {}