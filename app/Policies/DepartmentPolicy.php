<?php

namespace App\Policies;

use App\Models\User;

class DepartmentPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'departments';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
