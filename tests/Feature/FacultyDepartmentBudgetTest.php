<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\User;
use App\Services\SupplyRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyDepartmentBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_cannot_submit_over_department_budget(): void
    {
        $department = $this->department();
        $faculty = $this->faculty($department);
        $item = $this->item(6000);

        $this->actingAs($faculty)
            ->post(route('requests.store'), [
                'purpose' => 'Classroom supplies',
                'items' => [
                    ['inventory_id' => $item->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('requests', 0);
    }

    public function test_faculty_can_submit_until_budget_is_used(): void
    {
        $department = $this->department();
        $faculty = $this->faculty($department);
        $item = $this->item(4000);

        $this->actingAs($faculty)
            ->post(route('requests.store'), [
                'purpose' => 'First request',
                'items' => [['inventory_id' => $item->id, 'quantity' => 2]],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('requests', 1);

        $this->actingAs($faculty)
            ->post(route('requests.store'), [
                'purpose' => 'Would exceed',
                'items' => [['inventory_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('requests', 1);
    }

    public function test_cancelled_request_frees_department_budget(): void
    {
        $department = $this->department();
        $faculty = $this->faculty($department);
        $item = $this->item(10000);

        $this->actingAs($faculty)
            ->post(route('requests.store'), [
                'purpose' => 'Full budget',
                'items' => [['inventory_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertRedirect();

        $request = $faculty->supplyRequests()->first();
        app(SupplyRequestService::class)->cancel($request, $faculty);

        $this->actingAs($faculty)
            ->post(route('requests.store'), [
                'purpose' => 'After cancel',
                'items' => [['inventory_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseCount('requests', 2);
    }

    public function test_new_request_form_shows_remaining_budget(): void
    {
        $department = $this->department();
        $faculty = $this->faculty($department);

        $this->actingAs($faculty)
            ->get(route('requests.create'))
            ->assertOk()
            ->assertSee('faculty supply budget')
            ->assertSee('semester')
            ->assertSee('10,000.00');
    }

    public function test_prior_semester_usage_does_not_consume_current_budget(): void
    {
        \Carbon\Carbon::setTestNow('2026-09-15 10:00:00');

        try {
            $department = $this->department();
            $faculty = $this->faculty($department);
            $item = $this->item(10000);

            $this->actingAs($faculty)
                ->post(route('requests.store'), [
                    'purpose' => 'Previous semester',
                    'items' => [['inventory_id' => $item->id, 'quantity' => 1]],
                ])
                ->assertRedirect();

            $old = $faculty->supplyRequests()->first();
            $old->forceFill(['created_at' => '2026-02-10 08:00:00'])->save();

            $this->actingAs($faculty)
                ->post(route('requests.store'), [
                    'purpose' => 'This semester',
                    'items' => [['inventory_id' => $item->id, 'quantity' => 1]],
                ])
                ->assertRedirect()
                ->assertSessionMissing('error');

            $this->assertDatabaseCount('requests', 2);
        } finally {
            \Carbon\Carbon::setTestNow();
        }
    }

    private function department(): Department
    {
        return Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-BUDGET',
            'is_active' => true,
            'faculty_budget_limit' => 10000,
        ]);
    }

    private function faculty(Department $department): User
    {
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->assignRole('Faculty');

        return $user;
    }

    private function item(float $price): Inventory
    {
        $category = Category::create([
            'name' => 'Office',
            'slug' => 'office-'.uniqid(),
        ]);

        return Inventory::create([
            'item_code' => 'BUD-'.strtoupper(substr(uniqid(), -6)),
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => $price,
            'quantity' => 50,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
    }
}
