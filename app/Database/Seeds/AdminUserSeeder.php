<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = auth()->getProvider();

        $user = new User([
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'password',
            'active'   => 1,
        ]);

        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->addGroup('administrator');
    }
}
