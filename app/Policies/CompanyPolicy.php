<?php

namespace App\Policies;

use App\Models\User;

class CompanyPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'companies';
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }
}
