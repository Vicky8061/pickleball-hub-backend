<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Court;
use App\Models\CourtImage;
use App\Models\TimeSlot;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---------------------------------------------------
        // 1. DEFAULT USERS
        // ---------------------------------------------------

        // Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@pickleball.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Court Owner
        $owner = User::updateOrCreate(
            ['email' => 'owner@pickleball.com'],
            [
                'name' => 'Court Owner',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'status' => 'active',
            ]
        );

        // Regular Player / User
        $player = User::updateOrCreate(
            ['email' => 'user@pickleball.com'],
            [
                'name' => 'Player One',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );

        // ---------------------------------------------------
        // 2. COURTS
        // ---------------------------------------------------
        $courtsData = [
            [
                'name' => 'Grand Slam Pickleball Club',
                'description' => 'Premium indoor tournament-grade court with LED lighting, pro cushioning, and air conditioning.',
                'address' => 'Vesu Main Road, Surat, Gujarat',
                'latitude' => 21.1418,
                'longitude' => 72.7709,
                'price_per_hour' => 600.00,
                'court_type' => 'Indoor',
                'opening_time' => '06:00:00',
                'closing_time' => '23:00:00',
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Sunset Valley Pickleball Arena',
                'description' => 'Spacious outdoor courts surrounded by greenery. Equipped with night floodlights and player lounge.',
                'address' => 'Bandra West, Mumbai, Maharashtra',
                'latitude' => 19.0596,
                'longitude' => 72.8295,
                'price_per_hour' => 450.00,
                'court_type' => 'Outdoor',
                'opening_time' => '06:00:00',
                'closing_time' => '22:00:00',
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Elite Pro Pickleball Complex',
                'description' => 'Olympic-grade synthetic courts with high shock absorption. Coaching and paddle rentals available.',
                'address' => 'Koramangala 4th Block, Bangalore, Karnataka',
                'latitude' => 12.9352,
                'longitude' => 77.6245,
                'price_per_hour' => 750.00,
                'court_type' => 'Indoor',
                'opening_time' => '07:00:00',
                'closing_time' => '23:00:00',
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Ace Point Pickleball Courts',
                'description' => 'Standard outdoor hard court with premium grip and spectator seating. Perfect for evening matches.',
                'address' => 'Kalyani Nagar, Pune, Maharashtra',
                'latitude' => 18.5463,
                'longitude' => 73.9033,
                'price_per_hour' => 500.00,
                'court_type' => 'Outdoor',
                'opening_time' => '06:00:00',
                'closing_time' => '22:00:00',
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        // Standard hourly slots
        $slotHours = [
            ['06:00:00', '07:00:00'],
            ['07:00:00', '08:00:00'],
            ['08:00:00', '09:00:00'],
            ['09:00:00', '10:00:00'],
            ['16:00:00', '17:00:00'],
            ['17:00:00', '18:00:00'],
            ['18:00:00', '19:00:00'],
            ['19:00:00', '20:00:00'],
            ['20:00:00', '21:00:00'],
            ['21:00:00', '22:00:00'],
        ];

        $firstCourt = null;

        foreach ($courtsData as $index => $c) {
            $images = $c['images'];
            unset($c['images']);

            $court = Court::updateOrCreate(
                ['name' => $c['name'], 'owner_id' => $owner->id],
                $c
            );

            if ($index === 0) {
                $firstCourt = $court;
            }

            // Seed Images
            foreach ($images as $imgIdx => $imgUrl) {
                CourtImage::updateOrCreate(
                    ['court_id' => $court->id, 'image' => $imgUrl],
                    ['is_primary' => ($imgIdx === 0)]
                );
            }

            // Seed Time Slots
            foreach ($slotHours as $slot) {
                TimeSlot::updateOrCreate(
                    [
                        'court_id' => $court->id,
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                    ],
                    ['status' => 'active']
                );
            }
        }

        // ---------------------------------------------------
        // 3. PROMOTIONAL BANNERS
        // ---------------------------------------------------
        Banner::updateOrCreate(
            ['title' => 'Book Your Pickleball Court Today'],
            [
                'image' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1200&q=80',
                'redirect_url' => '/user/courts',
                'status' => 'active',
            ]
        );

        Banner::updateOrCreate(
            ['title' => 'Upcoming Tournaments & Championships'],
            [
                'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1200&q=80',
                'redirect_url' => '/user/tournaments',
                'status' => 'active',
            ]
        );

        // ---------------------------------------------------
        // 4. SAMPLE TOURNAMENT
        // ---------------------------------------------------
        if ($firstCourt) {
            Tournament::updateOrCreate(
                ['title' => 'City Open Pickleball Championship 2026', 'owner_id' => $owner->id],
                [
                    'court_id' => $firstCourt->id,
                    'description' => 'Annual amateur and pro doubles tournament. Trophies, medals, and cash prize pool for winners!',
                    'tournament_date' => now()->addDays(20)->toDateString(),
                    'registration_last_date' => now()->addDays(15)->toDateString(),
                    'banner' => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=80',
                    'start_time' => '08:00:00',
                    'end_time' => '18:00:00',
                    'entry_fee' => 500.00,
                    'max_participants' => 32,
                    'prize' => '₹25,000 Cash Prize + Trophy',
                    'status' => 'upcoming',
                ]
            );
        }
    }
}
