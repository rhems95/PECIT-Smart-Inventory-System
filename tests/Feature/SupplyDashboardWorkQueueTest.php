<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplyDashboardWorkQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_supply_dashboard_shows_work_queue_instead_of_pending_requests(): void
    {
        [$facultyRequest, $purchase, $lowStock] = $this->seedQueueData();

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->post(route('supply.stock.in'), [
                'inventory_id' => $lowStock->id,
                'quantity' => 4,
                'source_type' => 'purchase_order',
                'supplier_id' => Supplier::query()->first()->id,
                'reference_number' => 'PO-QUEUE-1',
            ])
            ->assertRedirect();

        $this->actingAs($supply)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ready to release')
            ->assertSee('Inspect deliveries')
            ->assertSee('Inspect today')
            ->assertSee('Actionable low stock')
            ->assertSee('Reserved')
            ->assertSee("Today's movements")
            ->assertSee('Oldest waiting')
            ->assertSee($facultyRequest->request_number)
            ->assertSee($purchase->purchase_number)
            ->assertSee('PO-QUEUE-1')
            ->assertSee($lowStock->item_name)
            ->assertSee('Stock In')
            ->assertDontSee('Pending Requests');
    }

    public function test_faculty_dashboard_keeps_pending_and_hides_supply_queue(): void
    {
        $this->seedQueueData();

        $faculty = User::factory()->create();
        $faculty->assignRole('Faculty');

        $this->actingAs($faculty)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pending Requests')
            ->assertDontSee('Ready to release')
            ->assertDontSee('Inspect deliveries')
            ->assertDontSee('Actionable low stock');
    }

    public function test_admin_dashboard_keeps_pending_and_shows_work_queue(): void
    {
        [$facultyRequest] = $this->seedQueueData();

        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pending Requests')
            ->assertSee('Ready to release')
            ->assertSee($facultyRequest->request_number);
    }

    /**
     * @return array{0: SupplyRequest, 1: PurchaseRequest, 2: Inventory}
     */
    private function seedQueueData(): array
    {
        $department = Department::query()->firstOrCreate(
            ['code' => 'CCS'],
            ['name' => 'College of Computer Studies', 'is_active' => true],
        );

        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-supplies-queue',
        ]);

        $lowStock = Inventory::create([
            'item_code' => 'OFF-QUEUE-LOW',
            'item_name' => 'Stapler Queue Test',
            'category_id' => $category->id,
            'unit' => 'pc',
            'unit_price' => 50,
            'quantity' => 2,
            'reserved_quantity' => 1,
            'minimum_stock' => 8,
            'status' => 'available',
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-QUEUE-1',
            'name' => 'Queue Test Supplier',
            'is_active' => true,
        ]);

        $faculty = User::factory()->create([
            'name' => 'Faculty Queue',
            'department_id' => $department->id,
        ]);
        $faculty->assignRole('Faculty');

        $facultyRequest = SupplyRequest::create([
            'request_number' => 'REQ-QUEUE-001',
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'approved',
            'purpose' => 'Ready for Supply',
            'total_amount' => 100,
            'approved_at' => now()->subDays(2),
        ]);

        $student = User::factory()->create([
            'name' => 'Student Queue',
            'department_id' => $department->id,
        ]);
        $student->assignRole('Student');

        $purchase = PurchaseRequest::create([
            'purchase_number' => 'PUR-QUEUE-001',
            'user_id' => $student->id,
            'status' => 'payment_verified',
            'total_amount' => 250,
            'verified_at' => now()->subDay(),
        ]);

        return [$facultyRequest, $purchase, $lowStock];
    }
}
