<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_supply_can_delete_unused_inventory_item(): void
    {
        $item = $this->item();
        $supply = $this->userWithRole('Supply Personnel');

        $this->actingAs($supply)
            ->delete(route('inventory.destroy', $item))
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('inventory', ['id' => $item->id]);
    }

    public function test_admin_can_delete_unused_inventory_item(): void
    {
        $item = $this->item();
        $admin = $this->userWithRole('Administrator');

        $this->actingAs($admin)
            ->delete(route('inventory.destroy', $item))
            ->assertRedirect(route('inventory.index'));

        $this->assertDatabaseMissing('inventory', ['id' => $item->id]);
    }

    public function test_faculty_cannot_delete_inventory_item(): void
    {
        $item = $this->item();
        $faculty = $this->userWithRole('Faculty');

        $this->actingAs($faculty)
            ->delete(route('inventory.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('inventory', ['id' => $item->id]);
    }

    public function test_cannot_delete_item_used_on_a_faculty_request(): void
    {
        $item = $this->item();
        $faculty = $this->userWithRole('Faculty');

        $request = SupplyRequest::create([
            'request_number' => 'REQ-DEL-001',
            'user_id' => $faculty->id,
            'type' => 'faculty',
            'status' => 'pending',
            'total_amount' => 100,
        ]);

        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $item->id,
            'quantity_requested' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $supply = $this->userWithRole('Supply Personnel');

        $this->actingAs($supply)
            ->from(route('inventory.show', $item))
            ->delete(route('inventory.destroy', $item))
            ->assertRedirect(route('inventory.show', $item))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('inventory', ['id' => $item->id]);
    }

    public function test_index_shows_delete_for_supply_not_faculty(): void
    {
        $item = $this->item();

        $this->actingAs($this->userWithRole('Supply Personnel'))
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Delete');

        $this->actingAs($this->userWithRole('Faculty'))
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertDontSee('>Delete<', false);
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function item(): Inventory
    {
        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-supplies-del-'.uniqid(),
        ]);

        return Inventory::create([
            'item_code' => 'DEL-'.strtoupper(substr(uniqid(), -6)),
            'item_name' => 'Stapler',
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 75,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
    }
}
