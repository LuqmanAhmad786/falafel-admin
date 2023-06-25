<?php

use Illuminate\Database\Seeder;
use App\Models\CardCategory;

class CardCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CardCategory::insert([
            ['category_name' => 'Falafel Special'],
            ['category_name' => 'Thank You'],
            ['category_name' => 'Birthday'],
            ['category_name' => 'Appreciation'],
            ['category_name' => 'Summer']
        ]);
    }
}
