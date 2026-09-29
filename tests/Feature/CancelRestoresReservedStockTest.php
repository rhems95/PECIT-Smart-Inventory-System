<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\User;
use App\Services\PurchaseRequestService;
use App\Services\SupplyRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelRestoresReservedStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_can_cancel_during_admin_review(): void
    {
        $faculty = $this->userWithRole('Faculty');
        $request = $this->facultyRequest($faculty, 'admin_review', reserved: false);

        $this->actingAs($faculty)
            ->get(route('requests.show', $request))
            ->assertOk()
            ->assertSee('Cancel Request');

        $this->actingAs($faculty)
            ->post(route('requests.cancel', $request))
            ->assertRedirect();

        $this->assertSame('cancelled', $request->fresh()->status);
        $this->assertQty(10, $request->items->first()->inventory->fresh()->quantity);
        $this->assertQty(0, $request->items->first()->inventory->fresh()->reserved_quantity);
    }

    public function test_cancelling_approved_request_restores_reserved_stock(): void
    {
        $faculty = $this->userWithRole('Faculty');
        $admin = $this->userWithRole('Administrator');
        $request = $this->facultyRequest($faculty, 'admin_review', reserved: false);

        app(SupplyRequestService::class)->approve($request, $admin);

        $item = $request->items->first()->inventory->fresh();
        $this->assertQty(10, $item->quantity);
        $this->assertQty(3, $item->reserved_quantity);

        $this->actingAs($faculty)
            ->post(route('requests.cancel', $request))
            ->assertRedirect();

        $item->refresh();
        $this->assertSame('cancelled', $request->fresh()->status);
        $this->assertQty(10, $item->quantity);
        $this->assertQty(0, $item->reserved_quantity);
        $this->assertQty(10, $item->availableQuantity());
    }

    public function test_cancelling_verified_purchase_restores_reserved_size_stock(): void
    {
        $student = $this->userWithRole('Student');
        $accounting = $this->userWithRole('Accounting');
        $uniform = $this->shopUniform();

        $this->actingAs($student)
            ->withSession(['cart' => [
                'u1' => ['inventory_id' => $uniform->id, 'quantity' => 2, 'size' => 'M'],
            ]])
            ->post(route('purchases.checkout'))
            ->assertRedirect();

        $purchase = PurchaseRequest::query()->latest('id')->first();
        $this->assertNotNull($purchase);

        app(PurchaseRequestService::class)->verifyPayment($purchase, $accounting);

        $uniform->refresh()->load('sizeStocks');
        $this->assertQty(2, $uniform->sizeStockFor('M')?->reserved_quantity);
        $this->assertQty(3, $uniform->availableQuantity('M'));

        $this->actingAs($student)
            ->from(route('purchases.show', $purchase))
            ->post(route('purchases.cancel', $purchase))
            ->assertRedirect(route('purchases.index'));

        $uniform->refresh()->load('sizeStocks');
        $this->assertSame('cancelled', $purchase->fresh()->status);
        $this->assertQty(5, $uniform->sizeStockFor('M')?->quantity);
        $this->assertQty(0, $uniform->sizeStockFor('M')?->reserved_quantity);
        $this->assertQty(5, $uniform->availableQuantity('M'));
    }

    public function test_cannot_cancel_released_request(): void
    {
        $faculty = $this->userWithRole('Faculty');
        $request = $this->facultyRequest($faculty, 'released', reserved: false);

        $this->actingAs($faculty)
            ->post(route('requests.cancel', $request))
            ->assertForbidden();
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function facultyRequest(User $faculty, string $status, bool $reserved): SupplyRequest
    {
        $category = Category::create([
            'name' => 'Office',
            'slug' => 'office-'.uniqid(),
        ]);

        $inventory = Inventory::create([
            'item_code' => 'OFF-'.uniqid(),
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 100,
            'quantity' => 10,
            'reserved_quantity' => $reserved ? 3 : 0,
            'minimum_stock' => 2,
            'status' => 'available',
            'student_shop' => false,
        ]);

        $request = SupplyRequest::create([
            'request_number' => 'REQ-'.uniqid(),
            'user_id' => $faculty->id,
            'type' => 'faculty',
            'status' => $status,
            'purpose' => 'Classroom',
            'total_amount' => 300,
        ]);

        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $inventory->id,
            'quantity_requested' => 3,
            'quantity_approved' => 3,
            'unit_price' => 100,
            'subtotal' => 300,
        ]);

        return $request->fresh('items.inventory');
    }

    protected function shopUniform(): Inventory
    {
        $category = Category::create([
            'name' => 'Uniforms',
            'slug' => 'uniforms-'.uniqid(),
        ]);

        $item = Inventory::create([
            'item_code' => 'UNI-QA-'.uniqid(),
            'item_name' => 'QA Uniform',
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 500,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
            'student_shop' => true,
        ]);

        $item->sizeStocks()->create([
            'size' => 'M',
            'quantity' => 5,
            'reserved_quantity' => 0,
        ]);
        $item->syncAggregatesFromSizeStocks();

        return $item->fresh('sizeStocks');
    }
}
