<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clean up old product images
        Storage::disk('public')->deleteDirectory('products');
        Storage::disk('public')->makeDirectory('products');

        // Copy seed images to storage
        $seedImagesPath = database_path('seeders/images');
        $imageMap = [];

        if (File::isDirectory($seedImagesPath)) {
            foreach (File::files($seedImagesPath) as $file) {
                $destination = 'products/'.$file->getFilename();
                Storage::disk('public')->put($destination, File::get($file->getPathname()));
                $imageMap[$file->getFilenameWithoutExtension()] = $destination;
            }
        }

        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@nova.test',
            'is_admin' => true,
        ]);

        // Testklanten
        $user = User::factory()->create([
            'name' => 'Jan Doe',
            'email' => 'jan@example.com',
            'is_admin' => false,
        ]);

        $marie = User::factory()->create([
            'name' => 'Marie Janssen',
            'email' => 'marie@example.com',
            'is_admin' => false,
        ]);

        // Categories (5)
        $audio = Category::create(['name' => 'Audio', 'slug' => 'audio']);
        $workspace = Category::create(['name' => 'Workspace', 'slug' => 'workspace']);
        $accessoires = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);
        $gaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $lifestyle = Category::create(['name' => 'Lifestyle', 'slug' => 'lifestyle']);

        // Products (20)
        $products = [
            // Audio (4)
            Product::create([
                'name' => 'Nova Pro Wireless',
                'slug' => 'nova-pro-wireless',
                'description' => 'Premium noise-cancelling koptelefoon met 40 uur batterijduur.',
                'price_in_cents' => 29900,
                'stock' => 15,
                'category_id' => $audio->id,
                'image_path' => $imageMap['headphones'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Nova Pro Wireless V2',
                'slug' => 'nova-pro-wireless-v2',
                'description' => 'Tweede generatie met verbeterde batterijduur tot 50 uur en lichter design.',
                'price_in_cents' => 34900,
                'stock' => 10,
                'category_id' => $audio->id,
                'image_path' => $imageMap['headphones-v2'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Compact Bluetooth Speaker',
                'slug' => 'compact-bluetooth-speaker',
                'description' => 'Waterbestendige draagbare speaker met 360° geluid en 20 uur batterij.',
                'price_in_cents' => 8990,
                'stock' => 30,
                'category_id' => $audio->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Studio Condenser Microfoon',
                'slug' => 'studio-condenser-microfoon',
                'description' => 'Professionele USB-microfoon voor podcasting en streaming.',
                'price_in_cents' => 11900,
                'stock' => 20,
                'category_id' => $audio->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            // Workspace (5)
            Product::create([
                'name' => 'Mechanical Keychron',
                'slug' => 'mechanical-keychron',
                'description' => 'Draadloos mechanisch toetsenbord met RGB achtergrondverlichting.',
                'price_in_cents' => 12900,
                'stock' => 25,
                'category_id' => $workspace->id,
                'image_path' => $imageMap['keyboard'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'UltraWide 34" Display',
                'slug' => 'ultrawide-34-display',
                'description' => 'Gebogen monitor voor ultieme productiviteit.',
                'price_in_cents' => 49900,
                'stock' => 8,
                'category_id' => $workspace->id,
                'image_path' => $imageMap['monitor'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Ergonomische Bureaustoel',
                'slug' => 'ergonomische-bureaustoel',
                'description' => 'Verstelbare lumbaalsteun en ademende mesh-rug voor lange werksessies.',
                'price_in_cents' => 39900,
                'stock' => 5,
                'category_id' => $workspace->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Laptop Stand Pro',
                'slug' => 'laptop-stand-pro',
                'description' => 'Aluminium laptopstandaard met 6 instelbare hoogtes.',
                'price_in_cents' => 4990,
                'stock' => 35,
                'category_id' => $workspace->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'LED Bureau Lamp',
                'slug' => 'led-bureau-lamp',
                'description' => 'Dimbare bureaulamp met kleurtemperatuurregeling en USB oplaadpoort.',
                'price_in_cents' => 3990,
                'stock' => 45,
                'category_id' => $workspace->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            // Accessoires (4)
            Product::create([
                'name' => 'USB-C Hub Pro',
                'slug' => 'usb-c-hub-pro',
                'description' => 'Compact docking station met 7 poorten.',
                'price_in_cents' => 5990,
                'stock' => 50,
                'category_id' => $accessoires->id,
                'image_path' => $imageMap['usb-hub'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Desk Mat XL',
                'slug' => 'desk-mat-xl',
                'description' => 'Premium bureauonderlegger 90x40cm.',
                'price_in_cents' => 3490,
                'stock' => 40,
                'category_id' => $accessoires->id,
                'image_path' => $imageMap['desk-mat'] ?? null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Draadloze Oplader 15W',
                'slug' => 'draadloze-oplader-15w',
                'description' => 'Snelle draadloze oplader compatibel met Qi-apparaten.',
                'price_in_cents' => 2990,
                'stock' => 60,
                'category_id' => $accessoires->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Cable Management Kit',
                'slug' => 'cable-management-kit',
                'description' => 'Compleet kabelbeheerset met clips, sleeves en velcrobanden.',
                'price_in_cents' => 1490,
                'stock' => 80,
                'category_id' => $accessoires->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            // Gaming (4)
            Product::create([
                'name' => 'Gaming Muis Pro',
                'slug' => 'gaming-muis-pro',
                'description' => 'Draadloze gaming muis met 25K DPI sensor en 70 uur batterij.',
                'price_in_cents' => 8990,
                'stock' => 20,
                'category_id' => $gaming->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'RGB Gaming Headset',
                'slug' => 'rgb-gaming-headset',
                'description' => '7.1 surround sound gaming headset met noise-cancelling microfoon.',
                'price_in_cents' => 7990,
                'stock' => 18,
                'category_id' => $gaming->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Gaming Controller',
                'slug' => 'gaming-controller',
                'description' => 'Draadloze controller met haptische feedback en aanpasbare triggers.',
                'price_in_cents' => 6990,
                'stock' => 22,
                'category_id' => $gaming->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Gaming Chair Racer',
                'slug' => 'gaming-chair-racer',
                'description' => 'Ergonomische gamingstoel met lendenkussen en neksupport.',
                'price_in_cents' => 29900,
                'stock' => 7,
                'category_id' => $gaming->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            // Lifestyle (3)
            Product::create([
                'name' => 'Smart Watch Nova',
                'slug' => 'smart-watch-nova',
                'description' => 'Smartwatch met gezondheidsmonitoring, GPS en 7 dagen batterij.',
                'price_in_cents' => 19900,
                'stock' => 12,
                'category_id' => $lifestyle->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Premium Rugzak 20L',
                'slug' => 'premium-rugzak-20l',
                'description' => 'Waterbestendige laptoptas met USB-oplaadpoort en anti-diefstalrits.',
                'price_in_cents' => 8990,
                'stock' => 25,
                'category_id' => $lifestyle->id,
                'image_path' => null,
                'is_active' => true,
            ]),
            Product::create([
                'name' => 'Thermosfles 500ml',
                'slug' => 'thermosfles-500ml',
                'description' => 'RVS thermosfles, houdt dranken 24u koud of 12u warm.',
                'price_in_cents' => 2990,
                'stock' => 100,
                'category_id' => $lifestyle->id,
                'image_path' => null,
                'is_active' => true,
            ]),
        ];

        // Sample orders voor Jan Doe
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Shipped,
            'total_in_cents' => 55700,
            'shipping_name' => 'Jan Doe',
            'shipping_address' => 'Straatnaam 123',
            'shipping_city' => 'Amsterdam',
            'shipping_postal_code' => '1234 AB',
            'stripe_session_id' => 'cs_test_demo_123',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $products[0]->id,
            'product_name' => $products[0]->name,
            'product_price_in_cents' => $products[0]->price_in_cents,
            'quantity' => 1,
            'subtotal_in_cents' => $products[0]->price_in_cents,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $products[4]->id,
            'product_name' => $products[4]->name,
            'product_price_in_cents' => $products[4]->price_in_cents,
            'quantity' => 2,
            'subtotal_in_cents' => $products[4]->price_in_cents * 2,
        ]);

        $pastOrder = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'total_in_cents' => 49900,
            'shipping_name' => 'Jan Doe',
            'shipping_address' => 'Straatnaam 123',
            'shipping_city' => 'Amsterdam',
            'shipping_postal_code' => '1234 AB',
        ]);

        OrderItem::create([
            'order_id' => $pastOrder->id,
            'product_id' => $products[5]->id,
            'product_name' => $products[5]->name,
            'product_price_in_cents' => $products[5]->price_in_cents,
            'quantity' => 1,
            'subtotal_in_cents' => $products[5]->price_in_cents,
        ]);

        // Sample order voor Marie
        $marieOrder = Order::create([
            'user_id' => $marie->id,
            'status' => OrderStatus::Paid,
            'total_in_cents' => 19900,
            'shipping_name' => 'Marie Janssen',
            'shipping_address' => 'Kerkstraat 45',
            'shipping_city' => 'Utrecht',
            'shipping_postal_code' => '3512 JK',
            'stripe_session_id' => 'cs_test_demo_456',
        ]);

        OrderItem::create([
            'order_id' => $marieOrder->id,
            'product_id' => $products[17]->id,
            'product_name' => $products[17]->name,
            'product_price_in_cents' => $products[17]->price_in_cents,
            'quantity' => 1,
            'subtotal_in_cents' => $products[17]->price_in_cents,
        ]);
    }
}
