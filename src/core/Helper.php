<?php
declare(strict_types=1);
namespace Core;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Env;
use App\Models\{ProfileImages, Users};
use core\Auth\AuthService;

/**
 * Helper and utility functions.
 */
class Helper {
  /**
   * Creates list of menu items.
   *
   * @param array $menu The list of menu items containing the name and the 
   * URL path.
   * @param string $dropdownClass The name of the classes that maybe set 
   * depending on user input.
   * @return string|false Returns the contents of the active output buffer on 
   * success or false on failure.
   */
  public static function buildMenuListItems(array $menu, string $dropdownClass=""): string|false {
    ob_start();
    $currentPage = self::currentPage();
    foreach($menu as $key => $val):
      $active = '';
      if($key == '%USERNAME%'){
        $key = (AuthService::currentUser())? "Hello " .AuthService::currentUser()->fname : $key;
        
      }
      if(Arr::isArray($val)): ?>
        <li class="nav-item dropdown">
          <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <?=$key?>
          </a>
          <ul class="dropdown-menu <?=$dropdownClass?>">
          <?php foreach ($val as $k => $v): 
            $active = ($v == $currentPage) ? 'active' : ''; ?>
            <?php if ($k == 'separator'): ?>
              <li><hr class="dropdown-divider"></li>
            <?php elseif ($v === route('auth.logout')): ?>
              <li>
                <form method="POST" action="<?=route('auth.logout')?>" class="dropdown-item p-0 m-0">
                  <?= csrf() ?>
                  <button type="submit" class="btn btn-link dropdown-item text-start w-100"><?=$k?></button>
                </form>
              </li>
            <?php else: ?>
              <li><a class="dropdown-item <?=$active?>" href="<?=$v?>"><?=$k?></a></li>
            <?php endif; ?>
          <?php endforeach; ?>

          </ul>
        </li>
      <?php else:
        $active = ($val == $currentPage) ? 'active' : ''; ?>
        <li class="nav-item">
            <a class="nav-link <?=$active?>" href="<?=$val?>"><?=$key?></a>
        </li>
      <?php endif; ?>
    <?php endforeach;
    return ob_get_clean();
  }

  /**
   * Determines current page based on REQUEST_URI.
   * 
   * @return string|null $currentPage  The current page.
   */
  public static function currentPage(): string|null {
    $currentPage = $_SERVER['REQUEST_URI'];
    if($currentPage == Env::get('APP_DOMAIN', '/') || $currentPage == Env::get('APP_DOMAIN', '/').'home/index') {
      $currentPage = Env::get('APP_DOMAIN', '/') . 'home';
    }
    return $currentPage;
  }

  /**
   * Gets the properties of the given object
   *
   * @param object $object An object instance.
   * @return array An associative array of defined object accessible 
   * non-static properties for the specified object in scope. If a property 
   * have not been assigned a value, it will be returned with a null value.
   */
  public static function getObjectProperties(object $object): array {
    return get_object_vars($object);
  }

  /**
   * Retrieves URL user's current profile image.
   * 
   * @return ProfileImages|false|null The associative array for the profile image's 
   * record.
   */
  public static function getProfileImage(): ProfileImages|false|null {
    $user = AuthService::currentUser();
    if($user) {
      return ProfileImages::findCurrentProfileImage($user->id);
    }
    return null;
  }
}
