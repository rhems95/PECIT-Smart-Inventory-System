<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplyRecentPurchasesAndAuditLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_supply_dashboard_shows_recent_student_purchases_with_department(): void
    {
        $department = Department::query()->firstOrCreate(
            ['code' => 'CCS'],
            ['name' => 'College of Computer Studies', 'is_active' => true],
        );

        $student = User::factory()->create([
            'name' => 'Ana Santos',
            'department_id' => $department->id,
        ]);
        $student->assignRole('Student');

        PurchaseRequest::create([
            'purchase_number' => 'PR-TEST-001',
            'user_id' => $student->id,
            'status' => 'payment_submitted',
            'total_amount' => 850,
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent Faculty Requests')
            ->assertSee('Requester', false)
            ->assertSee('Recent Student Purchases')
            ->assertSeeInOrder([
                'Recent Student Purchases',
                'Number',
                'Student',
                'Department',
                'Status',
                'Date',
                'PR-TEST-001',
                'Ana Santos',
                'College of Computer Studies',
            ])
            ->assertDontSee('Purchase #');
    }

    public function test_faculty_dashboard_does_not_show_recent_student_purchases(): void
    {
        $faculty = User::factory()->create();
        $faculty->assignRole('Faculty');

        $this->actingAs($faculty)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Recent Student Purchases')
            ->assertDontSee('Recent verified payments');
    }

    public function test_accounting_dashboard_shows_recent_verified_payments(): void
    {
        $department = Department::query()->firstOrCreate(
            ['code' => 'CC'],
            ['name' => 'College of Criminology', 'is_active' => true],
        );

        $student = User::factory()->create([
            'name' => 'Luis Mendoza',
            'department_id' => $department->id,
        ]);
        $student->assignRole('Student');

        PurchaseRequest::create([
            'purchase_number' => 'PR-VERIFIED-001',
            'user_id' => $student->id,
            'status' => 'payment_verified',
            'total_amount' => 1200,
            'verified_at' => now(),
        ]);

        PurchaseRequest::create([
            'purchase_number' => 'PR-WAITING-001',
            'user_id' => $student->id,
            'status' => 'payment_submitted',
            'total_amount' => 400,
        ]);

        $accounting = User::factory()->create();
        $accounting->assignRole('Accounting');

        $this->actingAs($accounting)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent verified payments')
            ->assertSee('Luis Mendoza')
            ->assertSee('College of Criminology')
            ->assertSee('PR-VERIFIED-001')
            ->assertDontSee('PR-WAITING-001');

        $this->actingAs($accounting)
            ->get(route('accounting.payments'))
            ->assertOk()
            ->assertSee('Awaiting verification')
            ->assertSee('PR-WAITING-001')
            ->assertSee('Recent verified payments')
            ->assertSee('PR-VERIFIED-001');
    }

    public function test_audit_logs_page_lists_who_changed_what(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin)->post('/logout');

        $this->post('/login', [
            'login_as' => 'staff',
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Signed in')
            ->assertSee($admin->name);
    }

    public function test_available_stock_card_lists_item_names_on_dashboard_not_inventory_link(): void
    {
        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-supplies-dash',
        ]);

        Inventory::create([
            'item_code' => 'OFF-PAPER-DASH',
            'item_name' => 'Bond Paper A4',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 100,
            'quantity' => 12,
            'reserved_quantity' => 2,
            'minimum_stock' => 5,
            'status' => 'available',
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="available-stock"', false)
            ->assertSee('aria-controls="available-stock"', false)
            ->assertSee('Bond Paper A4')
            ->assertSee('10');
    }
}
