<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Page::truncate();
        Schema::enableForeignKeyConstraints();

        Page::create([
            'key'       => 'about_us',
            'content'   => ['محتوى الصفحة هنا'],
        ]);

        Page::create([
            'key'       => 'privacy_policy',
            'content'   => ['محتوى الصفحة هنا'],
        ]);

        Page::create([
            'key'       => 'terms_and_condition',
            'content'   => ['محتوى الصفحة هنا'],
        ]);

        Page::create([
            'key'       => 'submit_poem_description',
            'content'   => ['محتوى الصفحة هنا'],
        ]);

        Page::create([
            'key'       => 'join_us_as_poet_description',
            'content'   => ['محتوى الصفحة هنا'],
        ]);

        Page::create([
            'key'       => 'default_rejection_reason',
            'content'   => ['محتوى الصفحة هنا'],
        ]);
    }
}
