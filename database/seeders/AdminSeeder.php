<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Kiểm tra xem admin đã tồn tại chưa
        $existingAdmin = User::where('email', 'admin@admin.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Admin user already exists. Updating password...');
            $existingAdmin->update([
                'password' => Hash::make('123456'),
            ]);
        } else {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123456'),
            ]);
            $this->command->info('Admin user created successfully!');
        }
        
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Password: 123456');
    }
}
