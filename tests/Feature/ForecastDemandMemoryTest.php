<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastDemandMemoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_forecast_shows_faculty_and_student_demand(): void
    {
        [$paper, $lanyard] = $this->seedDemand();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('ai.restock'))
            ->assertOk()
            ->assertSee('Most requested (faculty)')
            ->assertSee('Most purchased (students)')
            ->assertSee('Need restock this month')
            ->assertSee($paper->item_name)
            ->assertSee($lanyard->item_name);
    }

    public function test_ai_remembers_faculty_student_and_restock_this_month(): void
    {
        [$paper, $lanyard] = $this->seedDemand();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $faculty = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Most requested by faculty'])
            ->assertOk()
            ->json('reply');
        $this->assertStringContainsString($paper->item_name, $faculty);
        $this->assertStringContainsString('15', $faculty);

        $student = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Most purchased by students'])
            ->assertOk()
            ->json('reply');
        $this->assertStringContainsString($lanyard->item_name, $student);
        $this->assertStringContainsString('8', $student);

        $restock = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'What needs restock this month'])
            ->assertOk()
            ->json('reply');
        $this->assertStringContainsString($paper->item_name, $restock);
        $this->assertStringContainsString('restock', strtolower($restock));
    }

    public function test_ai_forecasts_items_that_will_trend_this_semester(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 12:00:00', 'Asia/Manila'));
        $paper = $this->seedOctoberLastYearDemand();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('ai.restock'))
            ->assertOk()
            ->assertSee('This semester forecast')
            ->assertSee($paper->item_name)
            ->assertSee('Suggest restock');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'What will trend this semester?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString($paper->item_name, $reply);
        $this->assertStringContainsString('Oct 2026', $reply);
        $this->assertStringContainsString('restock', strtolower($reply));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @return array{0: Inventory, 1: Inventory}
     */
    private function seedDemand(): array
    {
        $department = Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-FC',
            'is_active' => true,
        ]);
        $faculty = User::factory()->create(['department_id' => $department->id]);
        $faculty->assignRole('Faculty');
        $student = User::factory()->create(['department_id' => $department->id]);
        $student->assignRole('Student');

        $category = Category::create(['name' => 'Office', 'slug' => 'office-fc']);
        $paper = Inventory::create([
            'item_code' => 'FC-PAPER',
            'item_name' => 'Forecast Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 80,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $lanyard = Inventory::create([
            'item_code' => 'FC-LANYARD',
            'item_name' => 'Forecast ID Lanyard',
            'category_id' => $category->id,
            'unit' => 'pc',
            'unit_price' => 25,
            'quantity' => 80,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
            'status' => 'available',
            'student_shop' => true,
        ]);

        $request = SupplyRequest::create([
            'request_number' => 'REQ-FC-001',
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'pending',
            'purpose' => 'Forecast demand',
            'total_amount' => $paper->unit_price * 15,
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $paper->id,
            'quantity_requested' => 15,
            'unit_price' => $paper->unit_price,
            'subtotal' => $paper->unit_price * 15,
        ]);

        $purchase = PurchaseRequest::create([
            'purchase_number' => 'PUR-FC-001',
            'user_id' => $student->id,
            'status' => 'payment_submitted',
            'total_amount' => $lanyard->unit_price * 8,
        ]);
        PurchaseRequestItem::create([
            'purchase_request_id' => $purchase->id,
            'inventory_id' => $lanyard->id,
            'quantity' => 8,
            'unit_price' => $lanyard->unit_price,
            'subtotal' => $lanyard->unit_price * 8,
        ]);

        return [$paper, $lanyard];
    }

    private function seedOctoberLastYearDemand(): Inventory
    {
        $department = Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-SEM',
            'is_active' => true,
        ]);
        $faculty = User::factory()->create(['department_id' => $department->id]);
        $faculty->assignRole('Faculty');
        $category = Category::create(['name' => 'Office', 'slug' => 'office-sem']);
        $paper = Inventory::create([
            'item_code' => 'SEM-PAPER',
            'item_name' => 'Semester Trend Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 80,
            'quantity' => 4,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $request = SupplyRequest::create([
            'request_number' => 'REQ-SEM-OCT',
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'released',
            'purpose' => 'Last year October',
            'total_amount' => 80 * 40,
        ]);
        $request->forceFill(['created_at' => '2025-10-15 10:00:00'])->save();
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $paper->id,
            'quantity_requested' => 40,
            'unit_price' => 80,
            'subtotal' => 3200,
        ]);

        return $paper;
    }
}
