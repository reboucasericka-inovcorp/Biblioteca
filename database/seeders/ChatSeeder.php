<?php

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('Admin')->first();
        if (! $admin) {
            return;
        }

        $room = ChatRoom::query()->firstOrCreate(
            ['name' => 'Geral'],
            [
                'avatar' => null,
                'created_by' => $admin->id,
            ]
        );

        $room->users()->syncWithoutDetaching(
            User::query()->take(20)->pluck('id')->all()
        );
    }
}
