<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Jewelry Categories
        $categoriesData = [
            [
                'name' => 'Rings',
                'slug' => 'rings',
                'description' => 'Elegant demi-fine rings for every finger.',
                'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=500',
                'status' => 'active',
                'subcategories' => ['Gold Rings', 'Diamond Rings', 'Bands'],
            ],
            [
                'name' => 'Necklaces',
                'slug' => 'necklaces',
                'description' => 'Beautiful pendants, chains, and chokers.',
                'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=500',
                'status' => 'active',
                'subcategories' => ['Pendants', 'Chokers', 'Chains'],
            ],
            [
                'name' => 'Earrings',
                'slug' => 'earrings',
                'description' => 'Stunning studs, hoops, and drop earrings.',
                'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500',
                'status' => 'active',
                'subcategories' => ['Studs', 'Hoops', 'Drops'],
            ],
            [
                'name' => 'Bracelets',
                'slug' => 'bracelets',
                'description' => 'Delicate bangles, charm and chain bracelets.',
                'image' => 'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?w=500',
                'status' => 'active',
                'subcategories' => ['Bangles', 'Chains', 'Charms'],
            ],
            [
                'name' => 'Shop By Bond',
                'slug' => 'shop-by-bond',
                'description' => 'Find the perfect gift for your loved ones.',
                'image' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=500',
                'status' => 'inactive',
                'subcategories' => ['Wife', 'Husband', 'Mother', 'Brothers', 'Sister', 'Friends'],
            ],
        ];

        // Specific jewelry placeholder images to avoid red backgrounds
        $jewelryImages = [
            'rings' => [
                'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600',
                'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?w=600',
                'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600',
            ],
            'necklaces' => [
                'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600',
                'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=600',
                'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600',
            ],
            'earrings' => [
                'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600',
                'https://images.unsplash.com/photo-1611085583191-a3b1a20d55d1?w=600',
                'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?w=600',
            ],
            'bracelets' => [
                'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?w=600',
                'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=600',
                'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=600',
            ],
            'shop-by-bond' => [
                'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600',
                'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600',
                'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600',
                'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?w=600',
            ],
        ];

        foreach ($categoriesData as $catData) {
            $category = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'image' => $catData['image'],
                    'status' => $catData['status'],
                ]
            );

            // Subcategories
            foreach ($catData['subcategories'] as $index => $subName) {
                $subSlug = Str::slug($subName);

                $subImage = null;
                if ($category->slug === 'shop-by-bond') {
                    $bondImages = [
                        'Wife' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500',
                        'Husband' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=500',
                        'Mother' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=500',
                        'Brothers' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=500',
                        'Sister' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=500',
                        'Friends' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=500',
                    ];
                    $subImage = $bondImages[$subName] ?? null;
                }

                $subCategory = SubCategory::firstOrCreate(
                    ['slug' => $subSlug],
                    [
                        'category_id' => $category->id,
                        'name' => $subName,
                        'description' => $subName.' subcategory',
                        'image' => $subImage,
                        'status' => 'active',
                    ]
                );

                // Seed some products for each subcategory (2 products per subcategory)
                for ($i = 1; $i <= 2; $i++) {
                    $pName = $category->name.' '.$subCategory->name.' Piece '.$i;
                    $pSlug = Str::slug($pName);

                    // Pick image based on category and index
                    $imagesList = $jewelryImages[$category->slug] ?? $jewelryImages['rings'];
                    $imagePath = $imagesList[($index + $i) % count($imagesList)];

                    Product::firstOrCreate(
                        ['slug' => $pSlug],
                        [
                            'category_id' => $category->id,
                            'sub_category_id' => $subCategory->id,
                            'name' => $pName,
                            'description' => 'This is a premium handcrafted '.$pName.' designed with pure gold plating and AAA grade stones.',
                            'image' => $imagePath,
                            'price' => rand(1499, 9999),
                            'sale_price' => (rand(1, 100) <= 40) ? rand(499, 999) : null,
                            'stock' => rand(5, 50),
                            'status' => 'active',
                            'size' => 'Free Size',
                            'color' => 'Gold, Rose Gold, Silver',
                            'fabric' => '925 Silver Base',
                            'is_new_arrival' => ($i === 1), // alternate flags
                            'is_trending' => ($i === 2),
                            'is_featured' => ($index % 3 === 0), // seed the Featured slider
                        ]
                    );
                }
            }
        }
    }
}
