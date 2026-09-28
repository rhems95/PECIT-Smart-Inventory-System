<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MostRequestedItemsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_shows_most_requested_graph_data(): void
    {
        [$paper, $pen] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Most requested items')
            ->assertSee($paper->item_name)
            ->assertSee($pen->item_name)
            ->assertSee('mostRequestedChart')
            ->assertSee('demandTrendChart')
            ->assertSee('Predicted trend')
            ->assertSee('Jun 2026')
            ->assertSee('Nov 2026')
            ->assertSee('label: \'Faculty\'', false)
            ->assertSee('label: \'Students\'', false)
            ->assertSee('Month')
            ->assertSee('Semester')
            ->assertSee('Year')
            ->assertSee('Monthly summary')
            ->assertSee('Faculty requests')
            ->assertSee('Student purchases')
            ->assertSee('Export PDF')
            ->assertSee('Export Excel');
    }

    public function test_monthly_summary_can_be_exported(): void
    {
        $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $this->actingAs($supply)
            ->get(route('reports.summary.pdf'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($supply)
            ->get(route('reports.summary.excel'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_reports_graph_can_be_filtered_by_month(): void
    {
        [$paper, $pen] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $faculty = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->first();
        $department = $faculty->department;
        $this->facultyRequest($faculty, $department, $pen, 20, '2026-02-10 09:00:00');

        $this->actingAs($supply)
            ->get(route('reports.index', ['period' => 'month', 'month' => '2026-02']))
            ->assertOk()
            ->assertSee('February 2026')
            ->assertSee($pen->item_name)
            ->assertSee('labels: ["'.$pen->item_name.'"]', false)
            ->assertDontSee('labels: ["'.$paper->item_name.'"]', false);
    }

    public function test_reports_graph_can_be_filtered_by_semester(): void
    {
        [$paper] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $faculty = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->first();
        $department = $faculty->department;
        $stapler = Inventory::create([
            'item_code' => 'TOP-STAPLER',
            'item_name' => 'Stapler Wire Test',
            'category_id' => $paper->category_id,
            'unit' => 'box',
            'unit_price' => 25,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $this->facultyRequest($faculty, $department, $stapler, 20, '2026-02-10 09:00:00');

        $this->actingAs($supply)
            ->get(route('reports.index', ['period' => 'semester', 'semester' => '2026-2027-1']))
            ->assertOk()
            ->assertSee('1st semester AY 2026-2027')
            ->assertSee($paper->item_name)
            ->assertSee('Jun 2026')
            ->assertSee('Nov 2026')
            ->assertSee('Predicted trend')
            ->assertDontSee('labels: ["'.$stapler->item_name.'"]', false);

        $this->actingAs($supply)
            ->get(route('reports.index', ['period' => 'semester', 'semester' => '2025-2026-2']))
            ->assertOk()
            ->assertSee('2nd semester AY 2025-2026')
            ->assertSee($stapler->item_name)
            ->assertSee('labels: ["'.$stapler->item_name.'"]', false)
            ->assertDontSee('labels: ["'.$paper->item_name.'"]', false);
    }

    public function test_demand_trend_has_its_own_semester_filter(): void
    {
        [$paper] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $faculty = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->first();
        $department = $faculty->department;
        $stapler = Inventory::create([
            'item_code' => 'TOP-TREND-STAPLER',
            'item_name' => 'Trend Stapler Wire',
            'category_id' => $paper->category_id,
            'unit' => 'box',
            'unit_price' => 25,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $this->facultyRequest($faculty, $department, $stapler, 20, '2026-02-10 09:00:00');

        $this->actingAs($supply)
            ->get(route('reports.index', [
                'period' => 'year',
                'year' => '2025',
                'trend_semester' => '2025-2026-2',
            ]))
            ->assertOk()
            ->assertSee('id="demand-trend"', false)
            ->assertSee('id="trend-semester"', false)
            ->assertSee('psisKeepReportSection', false)
            ->assertSee('Dec 2025')
            ->assertSee('May 2026')
            ->assertSee($stapler->item_name)
            ->assertSee('2025 — faculty requests');
    }

    public function test_reports_graph_can_be_filtered_by_year(): void
    {
        [$paper, $pen] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $faculty = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Faculty'))->first();
        $department = $faculty->department;
        $this->facultyRequest($faculty, $department, $paper, 9, '2025-03-10 09:00:00');

        $this->actingAs($supply)
            ->get(route('reports.index', ['period' => 'year', 'year' => '2025']))
            ->assertOk()
            ->assertSee('2025 — faculty requests')
            ->assertSee($paper->item_name)
            ->assertSee('labels: ["'.$paper->item_name.'"]', false)
            ->assertDontSee('labels: ["'.$pen->item_name.'"]', false);
    }

    public function test_ai_answers_most_requested_this_month(): void
    {
        [$paper] = $this->seedRequestedItems();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Most requested this month'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString($paper->item_name, $reply);
        $this->assertStringContainsString('15', $reply);
    }

    /**
     * @return array{0: Inventory, 1: Inventory}
     */
    private function seedRequestedItems(): array
    {
        $department = Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-TOP',
            'is_active' => true,
        ]);
        $faculty = User::factory()->create(['department_id' => $department->id]);
        $faculty->assignRole('Faculty');

        $category = Category::create(['name' => 'Office', 'slug' => 'office-top']);
        $paper = Inventory::create([
            'item_code' => 'TOP-PAPER',
            'item_name' => 'Bond Paper A4',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 80,
            'quantity' => 40,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        $pen = Inventory::create([
            'item_code' => 'TOP-PEN',
            'item_name' => 'Ballpen Black',
            'category_id' => $category->id,
            'unit' => 'box',
            'unit_price' => 20,
            'quantity' => 30,
            'reserved_quantity' => 0,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);

        $this->facultyRequest($faculty, $department, $paper, 15);
        $this->facultyRequest($faculty, $department, $pen, 4);

        return [$paper, $pen];
    }

    private function facultyRequest(User $faculty, Department $department, Inventory $item, int $qty, ?string $createdAt = null): void
    {
        $request = SupplyRequest::create([
            'request_number' => 'REQ-TOP-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $faculty->id,
            'department_id' => $department->id,
            'type' => 'faculty',
            'status' => 'pending',
            'purpose' => 'Most requested test',
            'total_amount' => $item->unit_price * $qty,
        ]);

        if ($createdAt) {
            $request->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $item->id,
            'quantity_requested' => $qty,
            'unit_price' => $item->unit_price,
            'subtotal' => $item->unit_price * $qty,
        ]);
    }
}
