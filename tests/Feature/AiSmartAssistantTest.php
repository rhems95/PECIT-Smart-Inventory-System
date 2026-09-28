<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\RequestItem;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSmartAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_answers_stock_for_one_item_by_name(): void
    {
        $this->seedPaper();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Ilan ang Bond Paper?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Bond Paper', $reply);
        $this->assertStringContainsString('available 8', $reply);
        $this->assertStringContainsString('minimum 2', $reply);
    }

    public function test_ai_follows_up_on_that_item(): void
    {
        $this->seedPaper();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), [
                'message' => 'that item',
                'history' => [
                    ['role' => 'user', 'text' => 'Ilan ang Bond Paper?'],
                    ['role' => 'assistant', 'text' => 'Bond Paper (SMART-PAPER): available 8'],
                ],
            ])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Bond Paper', $reply);
        $this->assertStringContainsString('available 8', $reply);
    }

    public function test_ai_looks_up_faculty_request_number(): void
    {
        $paper = $this->seedPaper();
        $faculty = $this->facultyUser();
        $request = SupplyRequest::create([
            'request_number' => 'REQ-SMART-1',
            'user_id' => $faculty->id,
            'department_id' => $faculty->department_id,
            'type' => 'faculty',
            'status' => 'admin_review',
            'purpose' => 'Lookup test',
            'total_amount' => 80,
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $paper->id,
            'quantity_requested' => 1,
            'unit_price' => 80,
            'subtotal' => 80,
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Status of REQ-SMART-1'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('REQ-SMART-1', $reply);
        $this->assertStringContainsString('admin review', $reply);
    }

    public function test_ai_next_action_for_supply(): void
    {
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'What should I do next?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Next for Supply', $reply);
        $this->assertStringContainsString('release', $reply);
    }

    public function test_ai_compares_this_month_to_last_month(): void
    {
        $paper = $this->seedPaper();
        $faculty = $this->facultyUser();
        $this->facultyRequest($faculty, $paper, 4);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Compare this month to last month'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('faculty request', $reply);
        $this->assertStringContainsString($paper->item_name, $reply);
    }

    public function test_faculty_budget_check_uses_live_remaining(): void
    {
        $paper = $this->seedPaper();
        $faculty = $this->facultyUser();

        $reply = $this->actingAs($faculty)
            ->postJson(route('ai.ask'), ['message' => 'Can I request 2 Bond Paper?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('remaining', $reply);
        $this->assertStringContainsString($paper->item_name, $reply);
        $this->assertStringContainsString('160.00', $reply);
    }

    public function test_student_cannot_see_non_shop_item_lookup(): void
    {
        $this->seedPaper();
        $student = User::factory()->create();
        $student->assignRole('Student');

        $reply = $this->actingAs($student)
            ->postJson(route('ai.ask'), ['message' => 'Ilan ang Bond Paper?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringNotContainsString('available 8', $reply);
    }

    public function test_item_lookup_lists_all_rows_with_the_same_name(): void
    {
        $category = Category::create(['name' => 'Office', 'slug' => 'office-dup']);
        Inventory::create([
            'item_code' => 'CELLO-A',
            'item_name' => 'Cellophane',
            'category_id' => $category->id,
            'unit' => 'roll',
            'unit_price' => 10,
            'quantity' => 4,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'CELLO-B',
            'item_name' => 'Cellophane',
            'category_id' => $category->id,
            'unit' => 'pack',
            'unit_price' => 12,
            'quantity' => 9,
            'reserved_quantity' => 1,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'CELLO-TAPE',
            'item_name' => 'Cellophane Tape',
            'category_id' => $category->id,
            'unit' => 'roll',
            'unit_price' => 15,
            'quantity' => 3,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Stock of Cellophane'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Found 3 inventory items', $reply);
        $this->assertStringContainsString('• Cellophane (CELLO-A)', $reply);
        $this->assertStringContainsString("\n", $reply);
        $this->assertStringContainsString('CELLO-A', $reply);
        $this->assertStringContainsString('CELLO-B', $reply);
        $this->assertStringContainsString('CELLO-TAPE', $reply);
        $this->assertStringContainsString('available 4', $reply);
        $this->assertStringContainsString('available 8', $reply);
        $this->assertStringContainsString('available 3', $reply);
    }

    public function test_item_code_lookup_stays_on_one_row(): void
    {
        $category = Category::create(['name' => 'Office', 'slug' => 'office-code']);
        Inventory::create([
            'item_code' => 'CELLO-A',
            'item_name' => 'Cellophane',
            'category_id' => $category->id,
            'unit' => 'roll',
            'unit_price' => 10,
            'quantity' => 4,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);
        Inventory::create([
            'item_code' => 'CELLO-B',
            'item_name' => 'Cellophane',
            'category_id' => $category->id,
            'unit' => 'pack',
            'unit_price' => 12,
            'quantity' => 9,
            'reserved_quantity' => 1,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Stock of CELLO-B'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('CELLO-B', $reply);
        $this->assertStringContainsString('available 8', $reply);
        $this->assertStringNotContainsString('CELLO-A', $reply);
        $this->assertStringNotContainsString('Found 2 inventory items', $reply);
    }

    public function test_item_lookup_can_mention_last_supplier(): void
    {
        $paper = $this->seedPaper();
        $supplier = Supplier::create([
            'supplier_code' => 'SUP-SMART',
            'name' => 'Smart Office Supply',
            'is_active' => true,
        ]);
        Transaction::create([
            'transaction_number' => 'TXN-SMART-1',
            'inventory_id' => $paper->id,
            'type' => 'stock_in',
            'quantity' => 10,
            'quantity_in' => 10,
            'quantity_before' => 0,
            'quantity_after' => 10,
            'supplier_id' => $supplier->id,
            'performed_by' => User::factory()->create()->id,
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Stock of Bond Paper'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('Smart Office Supply', $reply);
    }

    public function test_similar_wording_does_not_trigger_item_lookup(): void
    {
        $this->seedPaper();
        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Please print the paper for the meeting'])
            ->assertOk()
            ->json('reply');

        $this->assertStringNotContainsString('SMART-PAPER', $reply);
        $this->assertStringNotContainsString('available 8', $reply);
    }

    public function test_category_word_in_a_sentence_does_not_dump_stock(): void
    {
        $category = Category::create(['name' => 'Computer Supplies', 'slug' => 'computer-false-ai']);
        Inventory::create([
            'item_code' => 'CPU-MOUSE',
            'item_name' => 'USB Mouse',
            'category_id' => $category->id,
            'unit' => 'pcs',
            'unit_price' => 150,
            'quantity' => 6,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'I work in the computer department'])
            ->assertOk()
            ->json('reply');

        $this->assertStringNotContainsString('USB Mouse', $reply);
        $this->assertStringNotContainsString('CPU-MOUSE', $reply);
    }

    public function test_computer_supplies_question_still_lists_category(): void
    {
        $category = Category::create(['name' => 'Computer Supplies', 'slug' => 'computer-true-ai']);
        Inventory::create([
            'item_code' => 'CPU-KB',
            'item_name' => 'USB Keyboard',
            'category_id' => $category->id,
            'unit' => 'pcs',
            'unit_price' => 200,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 1,
            'status' => 'available',
        ]);

        $supply = User::factory()->create();
        $supply->assignRole('Supply Personnel');

        $reply = $this->actingAs($supply)
            ->postJson(route('ai.ask'), ['message' => 'Computer supplies'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('USB Keyboard', $reply);
        $this->assertStringContainsString('avail 5', $reply);
    }

    private function seedPaper(): Inventory
    {
        $category = Category::create(['name' => 'Office', 'slug' => 'office-smart']);

        return Inventory::create([
            'item_code' => 'SMART-PAPER',
            'item_name' => 'Bond Paper',
            'category_id' => $category->id,
            'unit' => 'ream',
            'unit_price' => 80,
            'quantity' => 10,
            'reserved_quantity' => 2,
            'minimum_stock' => 2,
            'status' => 'available',
        ]);
    }

    private function facultyUser(): User
    {
        $department = Department::create([
            'name' => 'College of Computer Studies',
            'code' => 'CCS-SMART',
            'is_active' => true,
            'faculty_budget_limit' => 10000,
        ]);
        $faculty = User::factory()->create(['department_id' => $department->id]);
        $faculty->assignRole('Faculty');

        return $faculty;
    }

    private function facultyRequest(User $faculty, Inventory $item, int $qty): void
    {
        $request = SupplyRequest::create([
            'request_number' => 'REQ-SMART-CMP',
            'user_id' => $faculty->id,
            'department_id' => $faculty->department_id,
            'type' => 'faculty',
            'status' => 'pending',
            'purpose' => 'Compare test',
            'total_amount' => $item->unit_price * $qty,
        ]);
        RequestItem::create([
            'request_id' => $request->id,
            'inventory_id' => $item->id,
            'quantity_requested' => $qty,
            'unit_price' => $item->unit_price,
            'subtotal' => $item->unit_price * $qty,
        ]);
    }
}
