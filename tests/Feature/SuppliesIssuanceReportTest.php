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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliesIssuanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_issuance_log_splits_faculty_and_students_and_can_export(): void
    {
        [$paper, $lanyard] = $this->seedReleasedRows();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Supplies issuance log')
            ->assertSee('Faculty')
            ->assertSee('Students')
            ->assertSee($paper->item_name)
            ->assertSee($lanyard->item_name)
            ->assertSee('College of Computer Studies')
            ->assertSee('Export PDF')
            ->assertSee('Export Excel');

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', ['period' => 'all', 'source' => 'faculty']))
            ->assertOk()
            ->assertSee($paper->item_name)
            ->assertDontSee($lanyard->item_name);

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', ['period' => 'all', 'source' => 'student']))
            ->assertOk()
            ->assertSee($lanyard->item_name)
            ->assertDontSee($paper->item_name);

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance.pdf', ['period' => 'all']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition');

        $yearPdf = $this->actingAs($supply)
            ->get(route('reports.supplies-issuance.pdf', ['period' => 'year', 'year' => now()->format('Y')]))
            ->assertOk();
        $this->assertStringStartsWith('%PDF', $yearPdf->getContent());

        $semesterPdf = $this->actingAs($supply)
            ->get(route('reports.supplies-issuance.pdf', ['period' => 'semester']))
            ->assertOk();
        $this->assertStringStartsWith('%PDF', $semesterPdf->getContent());

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance.excel', ['period' => 'all']))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_issuance_log_filters_by_department_and_day(): void
    {
        [$paper] = $this->seedReleasedRows();
        $cc = Department::query()->firstOrCreate(
            ['code' => 'CC'],
            ['name' => 'College of Criminology', 'is_active' => true],
        );
        $facultyCc = User::factory()->create(['department_id' => $cc->id]);
        $facultyCc->assignRole('Faculty');
        $other = Inventory::create([
            'item_code' => 'OFF-OTHER',
            'item_name' => 'Whiteboard Marker',
            'category_id' => $paper->category_id,
            'unit' => 'pc',
            'unit_price' => 40,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $oldRequest = SupplyRequest::create([
            'request_number' => 'REQ-OLD-001',
            'user_id' => $facultyCc->id,
            'department_id' => $cc->id,
            'type' => 'faculty',
            'status' => 'released',
            'purpose' => 'Old',
            'total_amount' => 40,
            'released_at' => now()->subMonths(2),
        ]);
        RequestItem::create([
            'request_id' => $oldRequest->id,
            'inventory_id' => $other->id,
            'quantity_requested' => 1,
            'quantity_approved' => 1,
            'quantity_released' => 1,
            'unit_price' => 40,
            'subtotal' => 40,
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');
        $ccs = Department::query()->where('code', 'CCS')->first();

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', [
                'period' => 'all',
                'department_id' => $ccs->id,
            ]))
            ->assertOk()
            ->assertSee($paper->item_name)
            ->assertDontSee($other->item_name);

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', [
                'period' => 'day',
                'date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($paper->item_name)
            ->assertDontSee($other->item_name);
    }

    public function test_issuance_log_shows_fractional_qty(): void
    {
        [$paper] = $this->seedReleasedRows();
        $faculty = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->first();
        $gasoline = Inventory::create([
            'item_code' => 'OFF-GAS-FRAC',
            'item_name' => 'Issuance Gasoline Pump',
            'category_id' => $paper->category_id,
            'unit' => 'L',
            'unit_price' => 50,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $request = SupplyRequest::create([
            'request_number' => 'REQ-GAS-FRAC',
            'user_id' => $faculty->id,
            'department_id' => $faculty->department_id,
            'type' => 'faculty',
            'status' => 'released',
            'purpose' => 'Pump',
            'total_amount' => 285,
            'released_at' => now(),
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $gasoline->id,
            'quantity_requested' => 5.7,
            'quantity_approved' => 5.7,
            'quantity_released' => 5.7,
            'unit_price' => 50,
            'subtotal' => 285,
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('reports.supplies-issuance', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Issuance Gasoline Pump')
            ->assertSee('5.7');
    }

    /**
     * @return array{0: Inventory, 1: Inventory}
     */
    private function seedReleasedRows(): array
    {
        $department = Department::query()->firstOrCreate(
            ['code' => 'CCS'],
            ['name' => 'College of Computer Studies', 'is_active' => true],
        );
        $category = Category::create([
            'name' => 'Office Supplies',
            'slug' => 'office-issuance',
        ]);
        $paper = Inventory::create([
            'item_code' => 'OFF-ISSUE-PAPER',
            'item_name' => 'Issuance Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 210,
            'quantity' => 40,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
            'status' => 'available',
        ]);
        $lanyard = Inventory::create([
            'item_code' => 'UNI-ISSUE-LANYARD',
            'item_name' => 'Issuance ID Lanyard',
            'category_id' => $category->id,
            'unit' => 'pc',
            'unit_price' => 25,
            'quantity' => 80,
            'reserved_quantity' => 0,
            'minimum_stock' => 5,
            'status' => 'available',
            'student_shop' => true,
        ]);

        $faculty = User::factory()->create(['department_id' => $department->id]);
        $faculty->assignRole('Faculty');
        $request = SupplyRequest::create([
            'request_number' => 'REQ-ISSUE-001',
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'released',
            'purpose' => 'Office use',
            'total_amount' => 420,
            'released_at' => now(),
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $paper->id,
            'quantity_requested' => 2,
            'quantity_approved' => 2,
            'quantity_released' => 2,
            'unit_price' => 210,
            'subtotal' => 420,
        ]);

        $student = User::factory()->create([
            'name' => 'Issuance Student',
            'department_id' => $department->id,
        ]);
        $student->assignRole('Student');
        $purchase = PurchaseRequest::create([
            'purchase_number' => 'PUR-ISSUE-001',
            'user_id' => $student->id,
            'status' => 'released',
            'total_amount' => 50,
            'released_at' => now(),
        ]);
        PurchaseRequestItem::create([
            'purchase_request_id' => $purchase->id,
            'inventory_id' => $lanyard->id,
            'quantity' => 2,
            'unit_price' => 25,
            'subtotal' => 50,
        ]);

        return [$paper, $lanyard];
    }
}
