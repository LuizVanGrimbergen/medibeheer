<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use Illuminate\Http\Request;

class ResolveSelectedRole
{
    public function __invoke(Request $request): ?string
    {
        $role = $request->query('role');

        if (! is_string($role)) {
            return null;
        }

        return UserRole::tryFrom($role)?->value;
    }
}
