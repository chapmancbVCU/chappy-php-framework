<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use App\Models\Users;

class UserPasswordResetRequested {
    public Users $user;

    public function __construct(User $user) {
        $this->user = $user;
    }
}