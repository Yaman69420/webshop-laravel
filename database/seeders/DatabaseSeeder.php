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
                $destination = 'products/' . $file->getFilename();
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

        // Regular user
        $user = User::factory()->create([
            'name' => 'Jan Doe',
            'email' => 'jan@example.com',
            'is_admin' => false,
        ]);

        // Categories
        $audio = Category::create(['name' => 'Audio', 'slug' => 'audio']);
        $workspace = Category::create(['name' => 'Workspace', 'slug' => 'workspace']);
        $accessoires = Category::create(['name' => 'Accessoires', 'slug' => 'accessoires']);

        // Products
        $products = [
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
        ];

        // Sample order for Jan Doe
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Shipped,
            'total_in_cents' => 67397,
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
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $products[1]->id,
            'product_name' => $products[1]->name,
            'product_price_in_cents' => $products[1]->price_in_cents,
            'quantity' => 2,
        ]);

        // Past delivered order
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
            'product_id' => $products[2]->id,
            'product_name' => $products[2]->name,
            'product_price_in_cents' => $products[2]->price_in_cents,
            'quantity' => 1,
        ]);
    }
}
