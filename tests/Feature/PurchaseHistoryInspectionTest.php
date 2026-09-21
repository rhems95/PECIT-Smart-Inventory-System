<?php

namespace Tests\Feature;

use App\Enums\ReceivingInspectionStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\RequestItem;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SupplyRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseHistoryInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_supply_can_mark_received_item_correct_or_wrong(): void
    {
        $supply = $this->userWithRole('Supply Personnel');
        $item = $this->item();
        $supplier = Supplier::create([
            'supplier_code' => 'SUP-TEST-1',
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $this->actingAs($supply)
            ->post(route('supply.stock.in'), [
                'inventory_id' => $item->id,
                'quantity' => 10,
                'source_type' => 'purchase_order',
                'supplier_id' => $supplier->id,
                'reference_number' => 'PO-100',
                'delivery_receipt_number' => 'DR-100',
                'notes' => 'Office delivery',
            ])
            ->assertRedirect();

        $txn = Transaction::query()->latest('id')->first();
        $this->assertNotNull($txn);
        $this->assertSame(ReceivingInspectionStatus::Pending, $txn->inspection_status);

        $this->assertSame($supply->id, $txn->purchased_by);

        $this->actingAs($supply)
            ->get(route('supply.purchase-history'))
            ->assertOk()
            ->assertSee('Bond Paper')
            ->assertSee('PO-100')
            ->assertSee('Bought by')
            ->assertSee($supply->name)
            ->assertSee('Not checked');

        $this->actingAs($supply)
            ->from(route('supply.purchase-history'))
            ->post(route('supply.purchase-history.inspect', $txn), [
                'inspection_status' => 'incorrect',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('inspection_notes');

        $this->actingAs($supply)
            ->post(route('supply.purchase-history.inspect', $txn), [
                'inspection_status' => 'incorrect',
                'inspection_notes' => 'Supplier sent folders instead of bond paper.',
            ])
            ->assertRedirect();

        $txn->refresh();
        $this->assertSame(ReceivingInspectionStatus::Incorrect, $txn->inspection_status);
        $this->assertSame('Supplier sent folders instead of bond paper.', $txn->inspection_notes);
        $this->assertSame($supply->id, $txn->inspected_by);
    }

    public function test_purchase_history_shows_selected_buyer(): void
    {
        $supply = $this->userWithRole('Supply Personnel');
        $buyer = $this->userWithRole('Accounting');
        $item = $this->item();
        $supplier = Supplier::create([
            'supplier_code' => 'SUP-TEST-2',
            'name' => 'Test Supplier 2',
            'is_active' => true,
        ]);

        $this->actingAs($supply)
            ->post(route('supply.stock.in'), [
                'inventory_id' => $item->id,
                'quantity' => 4,
                'source_type' => 'purchase_order',
                'supplier_id' => $supplier->id,
                'reference_number' => 'PO-200',
                'purchased_by' => $buyer->id,
            ])
            ->assertRedirect();

        $this->actingAs($supply)
            ->get(route('supply.purchase-history'))
            ->assertOk()
            ->assertSee($buyer->name);
    }

    public function test_student_shop_purchases_appear_in_purchase_history(): void
    {
        $supply = $this->userWithRole('Supply Personnel');
        $student = User::factory()->create([
            'name' => 'Ana Shopper',
            'employee_id' => 'STU-HIST-1',
        ]);
        $student->assignRole('Student');

        $item = $this->shopLanyard();

        $purchase = app(\App\Services\PurchaseRequestService::class)->checkout($student, [
            ['inventory_id' => $item->id, 'quantity' => 2, 'size' => null],
        ]);

        $this->actingAs($supply)
            ->get(route('supply.purchase-history'))
            ->assertOk()
            ->assertSee('Student shop purchases')
            ->assertSee('Ana Shopper')
            ->assertSee('STU-HIST-1')
            ->assertSee($purchase->purchase_number)
            ->assertSee('ID Lanyard');
    }

    public function test_department_released_items_appear_in_purchase_history(): void
    {
        $supply = $this->userWithRole('Supply Personnel');
        $admin = $this->userWithRole('Administrator');
        $department = Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-HIST',
            'is_active' => true,
            'faculty_budget_limit' => 10000,
        ]);
        $faculty = User::factory()->create([
            'name' => 'Prof. Maria CCS',
            'department_id' => $department->id,
        ]);
        $faculty->assignRole('Faculty');
        $item = $this->item();

        $request = SupplyRequest::create([
            'request_number' => 'REQ-HIST-DEPT',
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'admin_review',
            'purpose' => 'Classroom',
            'total_amount' => 150,
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $item->id,
            'quantity_requested' => 2,
            'quantity_approved' => 2,
            'unit_price' => 75,
            'subtotal' => 150,
        ]);

        $svc = app(SupplyRequestService::class);
        $svc->approve($request, $admin);
        $svc->release($request->fresh(['items.inventory', 'user']), $supply);

        $this->actingAs($supply)
            ->get(route('supply.purchase-history'))
            ->assertOk()
            ->assertSee('Department released items')
            ->assertSee('REQ-HIST-DEPT')
            ->assertSee('College of Computer Studies')
            ->assertSee('Prof. Maria CCS')
            ->assertSee('Bond Paper');
    }

    public function test_faculty_cannot_open_purchase_history(): void
    {
        $this->actingAs($this->userWithRole('Faculty'))
            ->get(route('supply.purchase-history'))
            ->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function item(): Inventory
    {
        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-'.uniqid(),
        ]);

        return Inventory::create([
            'item_code' => 'BUY-'.strtoupper(substr(uniqid(), -6)),
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 75,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
    }

    private function shopLanyard(): Inventory
    {
        $category = Category::create([
            'name' => 'Uniforms',
            'slug' => 'uniforms-'.uniqid(),
        ]);

        return Inventory::create([
            'item_code' => 'LANYARD-'.strtoupper(substr(uniqid(), -6)),
            'item_name' => 'ID Lanyard',
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 50,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
            'student_shop' => true,
        ]);
    }
}
