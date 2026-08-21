<?php

namespace App\Policies;

class CategoryPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'categories';
    }
}
