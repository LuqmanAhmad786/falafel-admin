<?php

use Illuminate\Database\Seeder;
use App\Models\Card;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Card::insert([
            [
                'card_category_id' => 1,
                'card_name' => 'Abstract Drawing',
                'card_image' => 'images/menu-images/Abstract Drawing Card-01.jpg',
                'card_amount' => 100,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 2,
                'card_name' => 'Muhammad PBUH',
                'card_image' => 'images/menu-images/Muhammad PBUH Card-01.jpg',
                'card_amount' => 50,
                'card_type' => 3,
                'is_featured'=>0
            ],
            [
                'card_category_id' => 2,
                'card_name' => 'Ramadan Mubarak',
                'card_image' => 'images/menu-images/Ramadan Mubarak Card_Artboard 2.jpg',
                'card_amount' => 50,
                'card_type' => 3,
                'is_featured'=>0
            ],
            [
                'card_category_id' => 2,
                'card_name' => 'Ramadan Mubarak',
                'card_image' => 'images/menu-images/Ramadan Mubarak Card-01.jpg',
                'card_amount' => 50,
                'card_type' => 3,
                'is_featured'=>0
            ],
            [
                'card_category_id' => 3,
                'card_name' => 'Encouragement',
                'card_image' => 'images/menu-images/Encouragement Card-01.jpg',
                'card_amount' => 20,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 3,
                'card_name' => 'Purrfect',
                'card_image' => 'images/menu-images/Purrfect Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>0
            ],
            [
                'card_category_id' => 4,
                'card_name' => 'Falafel',
                'card_image' => 'images/menu-images/Branded Card_Artboard 2.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 4,
                'card_name' => 'Falafel',
                'card_image' => 'images/menu-images/Branded Card_Artboard 3.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 4,
                'card_name' => 'Falafel',
                'card_image' => 'images/menu-images/Branded Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 5,
                'card_name' => 'Friend',
                'card_image' => 'images/menu-images/Friend Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 5,
                'card_name' => 'Thank You',
                'card_image' => 'images/menu-images/Thanks You Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 6,
                'card_name' => 'Gift Card',
                'card_image' => 'images/menu-images/Gift Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 6,
                'card_name' => 'Gift Card',
                'card_image' => 'images/menu-images/Gift Card-02.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 6,
                'card_name' => 'My treat',
                'card_image' => 'images/menu-images/My treat Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 7,
                'card_name' => 'Graduation',
                'card_image' => 'images/menu-images/Graduation Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 8,
                'card_name' => 'Happy Birthday',
                'card_image' => 'images/menu-images/Happy Birthday Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 8,
                'card_name' => 'Happy Birthday',
                'card_image' => 'images/menu-images/Happy Birthday Card-02.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 8,
                'card_name' => 'Happy Birthday',
                'card_image' => 'images/menu-images/Happy Birthday Card-03.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 9,
                'card_name' => 'Eid Day',
                'card_image' => 'images/menu-images/Eid Day Card (N).jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 9,
                'card_name' => 'Happy New Year',
                'card_image' => 'images/menu-images/Happy New Year 2021 Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 9,
                'card_name' => 'Independence Day',
                'card_image' => 'images/menu-images/Independence Day Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 9,
                'card_name' => 'Thanks Giving',
                'card_image' => 'images/menu-images/Thanks Giving Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],

            [
                'card_category_id' => 10,
                'card_name' => 'Fathers Day',
                'card_image' => 'images/menu-images/Fathers Day Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 10,
                'card_name' => 'Love Card',
                'card_image' => 'images/menu-images/Love Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
            [
                'card_category_id' => 10,
                'card_name' => 'Mother Day',
                'card_image' => 'images/menu-images/Mother Day Card-01.jpg',
                'card_amount' => 10,
                'card_type' => 3,
                'is_featured'=>1
            ],
        ]);
    }
}
