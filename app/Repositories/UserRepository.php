<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByRole($roleName)
    {
        return User::whereHas('role', function($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }

    public function findAdmins()
    {
        return $this->findByRole('admin');
    }

    public function findRegularUsers()
    {
        return $this->findByRole('user');
    }

    public function getUsersApproachingQuota($threshold = 0.9)
    {
        return User::whereHas('projects', function($query) use ($threshold) {
            // Users who have used >= threshold% of their disk quota
        })->get();
    }
}
