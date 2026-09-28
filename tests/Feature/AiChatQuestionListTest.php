<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatQuestionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_chat_page_lists_questions_and_has_a_type_box(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->get(route('ai.chat'))
            ->assertOk()
            ->assertSee('Choose a question')
            ->assertSee('Reorder recommendations')
            ->assertSee('Type a question')
            ->assertSee('How many items?')
            ->assertSee('Where do I view reports?');
    }

    public function test_ask_reports_inventory_item_count(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $category = Category::create(['name' => 'Office', 'slug' => 'office-count']);
        Inventory::create([
            'item_code' => 'CNT-A',
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 10,
            'quantity' => 20,
            'reserved_quantity' => 5,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'CNT-B',
            'item_name' => 'Pen',
            'category_id' => $category->id,
            'unit' => 'box',
            'unit_price' => 5,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many items are in the inventory?'])
            ->assertOk()
            ->assertJsonFragment(['reply' => 'Inventory has 2 item type(s). On hand 30, reserved 5, available 25. By category: Office: 2 type(s), available 25.']);
    }

    public function test_ask_understands_filipino_and_cebuano_inventory_count(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $category = Category::create(['name' => 'Office', 'slug' => 'office-lang']);
        Inventory::create([
            'item_code' => 'LNG-A',
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 10,
            'quantity' => 8,
            'reserved_quantity' => 1,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Ilan ang items sa inventory?'])
            ->assertOk()
            ->assertJsonPath('reply', 'May 1 klase ng item sa inventory. On hand 8, reserved 1, available 7. Ayon sa kategorya: Office: 1 type(s), available 7.');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Pila ka items sa inventory?'])
            ->assertOk()
            ->assertJsonPath('reply', 'Naay 1 klase sa item sa inventory. On hand 8, reserved 1, available 7. Pinaagi sa kategorya: Office: 1 type(s), available 7.');
    }

    public function test_ask_lists_all_items_with_available_count(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $category = Category::create(['name' => 'Office', 'slug' => 'office-list']);
        Inventory::create([
            'item_code' => 'LST-A',
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 10,
            'quantity' => 12,
            'reserved_quantity' => 2,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'LST-B',
            'item_name' => 'Marker',
            'category_id' => $category->id,
            'unit' => 'pc',
            'unit_price' => 5,
            'quantity' => 6,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'list all items and available count'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Bond Paper available 10 ream', $reply);
        $this->assertStringContainsString('• Bond Paper available 10 ream', $reply);
        $this->assertStringContainsString('Marker available 6 pc', $reply);
        $this->assertStringContainsString('Marker available 6 pc', $reply);
    }

    public function test_ask_counts_available_items_by_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $office = Category::create(['name' => 'Office Supplies', 'slug' => 'office-by-type']);
        $cleaning = Category::create(['name' => 'Cleaning', 'slug' => 'cleaning-by-type']);
        Inventory::create([
            'item_code' => 'TYP-A',
            'item_name' => 'Bond Paper',
            'category_id' => $office->id,
            'unit' => 'ream',
            'unit_price' => 10,
            'quantity' => 20,
            'reserved_quantity' => 5,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'TYP-B',
            'item_name' => 'Trash Bag',
            'category_id' => $cleaning->id,
            'unit' => 'pc',
            'unit_price' => 5,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many available item by type in inventory'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('2 item type(s)', $reply);
        $this->assertStringContainsString('available 25', $reply);
        $this->assertStringContainsString('Office Supplies: 1 type(s), available 15', $reply);
        $this->assertStringContainsString('Cleaning: 1 type(s), available 10', $reply);
    }

    public function test_ask_accepts_a_listed_question(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Low stock'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }

    public function test_ask_accepts_free_typed_questions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'What is the meaning of life?'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }

    public function test_supply_navigation_question_includes_reports_url(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Where do I view reports?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Reports', $reply);
        $this->assertStringContainsString(route('reports.index', [], false), $reply);
        $this->assertStringNotContainsString(route('reports.index', [], false).'.', $reply);
    }

    public function test_faculty_navigation_question_includes_new_request_url(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Faculty');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Where do I submit a request?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('New Request', $reply);
        $this->assertStringContainsString(route('requests.create', [], false), $reply);
        $this->assertStringNotContainsString(route('requests.create', [], false).'.', $reply);
    }

    public function test_student_cannot_get_inventory_link(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Where is inventory?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('not in your menu', $reply);
        $this->assertStringContainsString(route('shop.index', [], false), $reply);
        $this->assertStringNotContainsString(route('inventory.index', [], false), $reply);
    }

    public function test_student_shop_navigation_includes_shop_url(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Where is Uniform Shop?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Uniform Shop', $reply);
        $this->assertStringContainsString(route('shop.index', [], false), $reply);
        $this->assertStringNotContainsString(route('shop.index', [], false).'.', $reply);
    }

    public function test_ask_lists_all_departments_not_only_colleges(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many departments in total'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('8 active department', $reply);
        $this->assertStringContainsString('SHS', $reply);
        $this->assertStringContainsString('ADMIN', $reply);
        $this->assertStringContainsString('SUPPLY', $reply);
        $this->assertStringContainsString('not only the 5 colleges', $reply);
    }

    public function test_ask_reports_live_user_counts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many users'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Live user counts', $reply);
        $this->assertStringContainsString('Supply Personnel', $reply);
        $this->assertStringContainsString('1 active of 1', $reply);
    }

    public function test_student_cannot_see_staff_user_counts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many users'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('staff', $reply);
        $this->assertStringNotContainsString('Live user counts', $reply);
    }

    public function test_ask_lists_live_categories(): void
    {
        Category::create(['name' => 'Office Supplies', 'slug' => 'office-live-ai', 'is_active' => true]);
        Category::create(['name' => 'Uniforms', 'slug' => 'uniforms-live-ai', 'is_active' => true]);

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many categories'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('categories in PSIS', $reply);
        $this->assertStringContainsString('Office Supplies', $reply);
        $this->assertStringContainsString('Uniforms', $reply);
    }

    public function test_ask_lists_live_units_of_measurement(): void
    {
        $count = UnitOfMeasurement::query()->count();
        $this->assertGreaterThan(0, $count);

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many units of measurement we have'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString("{$count} unit(s) of measurement in PSIS", $reply);
        $this->assertStringContainsString('Piece (pcs)', $reply);
        $this->assertStringContainsString('Box (box)', $reply);
    }

    public function test_ask_how_many_units_is_not_item_stock(): void
    {
        $count = UnitOfMeasurement::query()->count();

        $user = User::factory()->create();
        $user->assignRole('Administrator');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many units do we have'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString("{$count} unit(s) of measurement in PSIS", $reply);
        $this->assertStringNotContainsString('item type(s)', $reply);
    }
}
