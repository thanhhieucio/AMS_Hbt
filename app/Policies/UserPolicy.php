<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'users';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
