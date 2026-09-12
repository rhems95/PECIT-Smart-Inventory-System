<?php

namespace Tests\Feature;

use App\Enums\InventoryTransactionType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SupplyRequestService;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCardAndTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_writes_physical_ledger_row(): void
    {
        $item = $this->item(quantity: 10);
        $supply = $this->userWithRole('Supply Personnel');

        $this->actingAs($supply)
            ->post(route('supply.stock.in'), [
                'inventory_id' => $item->id,
                'quantity' => 5,
                'source_type' => 'manual_external',
                'notes' => 'Delivery from store',
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame(15, $item->quantity);

        $txn = Transaction::where('inventory_id', $item->id)->latest('id')->first();
        $this->assertNotNull($txn);
        $this->assertSame(InventoryTransactionType::StockIn, $txn->type);
        $this->assertSame(5, $txn->quantity_in);
        $this->assertSame(0, $txn->quantity_out);
        $this->assertSame(15, $txn->runningBalance());
    }

    public function test_reserve_does_not_change_on_hand_and_is_hidden_from_stock_card(): void
    {
        $item = $this->item(quantity: 10);
        $supply = $this->userWithRole('Supply Personnel');

        app(InventoryService::class)->reserve($item, 3, $supply, 'Hold for request');

        $item->refresh();
        $this->assertSame(10, $item->quantity);
        $this->assertSame(3, $item->reserved_quantity);

        $this->assertDatabaseHas('transactions', [
            'inventory_id' => $item->id,
            'type' => 'reserve',
            'quantity' => 3,
        ]);

        $this->actingAs($supply)
            ->get(route('inventory.stock-card', $item))
            ->assertOk()
            ->assertSee('No physical stock movements yet.');
    }

    public function test_release_creates_release_not_stock_out(): void
    {
        $faculty = $this->userWithRole('Faculty');
        $admin = $this->userWithRole('Administrator');
        $supply = $this->userWithRole('Supply Personnel');
        $item = $this->item(quantity: 10);

        $request = SupplyRequest::create([
            'request_number' => 'REQ-CARD-001',
            'user_id' => $faculty->id,
            'type' => 'faculty',
            'status' => 'admin_review',
            'total_amount' => 150,
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $item->id,
            'quantity_requested' => 2,
            'unit_price' => 75,
            'subtotal' => 150,
        ]);

        $svc = app(SupplyRequestService::class);
        $svc->approve($request, $admin);
        $svc->release($request->fresh(['items.inventory']), $supply);

        $this->assertDatabaseHas('transactions', [
            'inventory_id' => $item->id,
            'type' => 'release',
            'quantity_out' => 2,
        ]);
        $this->assertDatabaseMissing('transactions', [
            'inventory_id' => $item->id,
            'type' => 'stock_out',
        ]);
        $this->assertSame(8, $item->fresh()->quantity);
        $this->assertSame(0, $item->fresh()->reserved_quantity);
    }

    public function test_restore_is_not_a_stock_in(): void
    {
        $item = $this->item(quantity: 10);
        $user = $this->userWithRole('Supply Personnel');
        $svc = app(InventoryService::class);
        $svc->reserve($item, 4, $user);
        $svc->restore($item->fresh(), 4, $user, 'Cancelled');

        $this->assertDatabaseHas('transactions', [
            'inventory_id' => $item->id,
            'type' => 'restore',
        ]);
        $this->assertSame(0, (int) Transaction::where('inventory_id', $item->id)->where('type', 'restore')->value('quantity_in'));
        $this->assertSame(10, $item->fresh()->quantity);
    }

    public function test_faculty_cannot_open_stock_card(): void
    {
        $item = $this->item();

        $this->actingAs($this->userWithRole('Faculty'))
            ->get(route('inventory.stock-card', $item))
            ->assertForbidden();
    }

    public function test_supply_can_open_stock_card(): void
    {
        $item = $this->item();

        $this->actingAs($this->userWithRole('Supply Personnel'))
            ->get(route('inventory.stock-card', $item))
            ->assertOk()
            ->assertSee('Stock Card');
    }

    public function test_stock_out_requires_reason(): void
    {
        $item = $this->item(quantity: 10);

        $this->actingAs($this->userWithRole('Supply Personnel'))
            ->from(route('supply.stock.index'))
            ->post(route('supply.stock.out'), [
                'inventory_id' => $item->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('notes');

        $this->assertSame(10, $item->fresh()->quantity);
    }

    public function test_damage_deducts_available_stock(): void
    {
        $item = $this->item(quantity: 10);
        $supply = $this->userWithRole('Supply Personnel');

        $this->actingAs($supply)
            ->post(route('supply.stock.damage'), [
                'inventory_id' => $item->id,
                'quantity' => 2,
                'notes' => 'Wet from leak',
            ])
            ->assertRedirect();

        $this->assertSame(8, $item->fresh()->quantity);
        $this->assertDatabaseHas('transactions', [
            'inventory_id' => $item->id,
            'type' => 'damage',
            'quantity_out' => 2,
        ]);
    }

    public function test_return_to_supplier_requires_supplier_and_deducts_only_returned_qty(): void
    {
        $item = $this->item(quantity: 10);
        $supply = $this->userWithRole('Supply Personnel');

        $this->actingAs($supply)
            ->from(route('supply.stock.index'))
            ->post(route('supply.stock.return'), [
                'inventory_id' => $item->id,
                'quantity' => 2,
                'notes' => 'Defective pairs',
            ])
            ->assertSessionHasErrors('supplier_id');

        $supplier = Supplier::create([
            'name' => 'Acme Uniforms',
            'is_active' => true,
        ]);

        $this->actingAs($supply)
            ->post(route('supply.stock.return'), [
                'inventory_id' => $item->id,
                'quantity' => 2,
                'supplier_id' => $supplier->id,
                'notes' => 'Defective pairs',
                'reference_number' => 'DR-88',
            ])
            ->assertRedirect();

        $this->assertSame(8, $item->fresh()->quantity);
        $this->assertDatabaseHas('transactions', [
            'inventory_id' => $item->id,
            'type' => 'return_to_supplier',
            'quantity_out' => 2,
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_student_cannot_manage_suppliers(): void
    {
        $this->actingAs($this->userWithRole('Student'))
            ->get(route('admin.suppliers.index'))
            ->assertForbidden();
    }

    public function test_index_shows_stock_card_link_for_supply_not_faculty(): void
    {
        $item = $this->item();

        $this->actingAs($this->userWithRole('Supply Personnel'))
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Stock Card');

        $this->actingAs($this->userWithRole('Faculty'))
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertDontSee('Stock Card');
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function item(int $quantity = 5): Inventory
    {
        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-'.uniqid(),
        ]);

        return Inventory::create([
            'item_code' => 'CARD-'.strtoupper(substr(uniqid(), -6)),
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 75,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
    }
}
