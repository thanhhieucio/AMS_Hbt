<?php

namespace App\Policies;

class CustomFieldPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'customfields';
    }
}
