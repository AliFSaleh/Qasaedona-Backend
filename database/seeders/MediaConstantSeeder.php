<?php

namespace Database\Seeders;

use App\Models\MediaConstant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MediaConstantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        MediaConstant::truncate();
        Schema::enableForeignKeyConstraints();

        MediaConstant::create([
            'id'   => 1,
            'key'   => 'whatsApp_number',
            'value' => '987654321'
        ]);

        MediaConstant::create([
            'id'   => 2,
            'key'   => 'email',
            'value' => 'email@gmail.com'
        ]);
    }
}
