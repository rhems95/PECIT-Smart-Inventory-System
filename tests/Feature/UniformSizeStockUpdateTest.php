<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniformSizeStockUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_includes_on_hand_inputs_per_size(): void
    {
        $user = $this->supplyUser();
        $item = $this->uniformWithSizeStock(onHand: 4, reserved: 1);

        $this->actingAs($user)
            ->get(route('inventory.edit', $item))
            ->assertOk()
            ->assertSee('name="size_quantities[M]"', false)
            ->assertSee('On-hand by size');
    }

    public function test_inventory_update_saves_on_hand_by_size(): void
    {
        $user = $this->supplyUser();
        $item = $this->uniformWithSizeStock(onHand: 4, reserved: 1);

        $this->actingAs($user)
            ->put(route('inventory.update', $item), $this->itemPayload($item, [
                'M' => 10,
                'L' => 3,
            ]))
            ->assertRedirect(route('inventory.show', $item));

        $item->refresh();
        $item->load('sizeStocks');

        $this->assertSame(10, $item->sizeStockFor('M')?->quantity);
        $this->assertSame(9, $item->availableQuantity('M'));
        $this->assertSame(3, $item->sizeStockFor('L')?->quantity);
        $this->assertSame(13, $item->quantity);
    }

    public function test_cannot_set_on_hand_below_reserved_for_a_size(): void
    {
        $user = $this->supplyUser();
        $item = $this->uniformWithSizeStock(onHand: 4, reserved: 3);

        $this->actingAs($user)
            ->from(route('inventory.edit', $item))
            ->put(route('inventory.update', $item), $this->itemPayload($item, [
                'M' => 1,
            ]))
            ->assertRedirect(route('inventory.edit', $item))
            ->assertSessionHas('error');

        $item->refresh();
        $this->assertSame(4, $item->sizeStockFor('M')?->quantity);
    }

    protected function supplyUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        return $user;
    }

    protected function uniformWithSizeStock(int $onHand, int $reserved): Inventory
    {
        $category = Category::create([
            'name' => 'Uniforms',
            'slug' => 'uniforms',
        ]);

        $item = Inventory::create([
            'item_code' => 'UNI-SIZE-TEST',
            'item_name' => 'CIT Exclusive Uniform',
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 500,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
            'status' => 'available',
            'student_shop' => true,
        ]);

        $item->sizeStocks()->create([
            'size' => 'M',
            'quantity' => $onHand,
            'reserved_quantity' => $reserved,
        ]);
        $item->syncAggregatesFromSizeStocks();

        return $item->fresh('sizeStocks');
    }

    /**
     * @param  array<string, int>  $sizeQuantities
     * @return array<string, mixed>
     */
    protected function itemPayload(Inventory $item, array $sizeQuantities): array
    {
        $quantities = [
            'XS' => 0,
            'S' => 0,
            'M' => $item->sizeStockFor('M')?->quantity ?? 0,
            'L' => 0,
            'XL' => 0,
            '2XL' => 0,
            '3XL' => 0,
        ];

        foreach ($sizeQuantities as $size => $qty) {
            $quantities[$size] = $qty;
        }

        return [
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'minimum_stock' => $item->minimum_stock,
            'location' => $item->location,
            'student_shop' => 1,
            'size_quantities' => $quantities,
        ];
    }
}
