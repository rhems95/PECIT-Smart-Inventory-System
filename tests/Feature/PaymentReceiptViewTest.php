<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReceiptViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_accounting_can_view_receipt_through_app_route(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('receipts/demo.jpg', 'fake-image');

        $student = User::factory()->create();
        $student->assignRole('Student');
        $accounting = User::factory()->create();
        $accounting->assignRole('Accounting');

        $purchase = $this->purchaseWithReceipt($student, 'receipts/demo.jpg');

        $this->actingAs($student)
            ->get(route('purchases.receipt.show', $purchase))
            ->assertOk();

        $this->actingAs($accounting)
            ->get(route('purchases.receipt.show', $purchase))
            ->assertOk();
    }

    public function test_other_student_cannot_view_someone_elses_receipt(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('receipts/demo.jpg', 'fake-image');

        $owner = User::factory()->create();
        $owner->assignRole('Student');
        $other = User::factory()->create();
        $other->assignRole('Student');

        $purchase = $this->purchaseWithReceipt($owner, 'receipts/demo.jpg');

        $this->actingAs($other)
            ->get(route('purchases.receipt.show', $purchase))
            ->assertForbidden();
    }

    private function purchaseWithReceipt(User $student, string $path): PurchaseRequest
    {
        $purchase = PurchaseRequest::create([
            'purchase_number' => 'PUR-RCPT-1',
            'user_id' => $student->id,
            'status' => 'payment_submitted',
            'total_amount' => 100,
        ]);

        Payment::create([
            'reference_number' => 'PAY-RCPT-1',
            'purchase_request_id' => $purchase->id,
            'user_id' => $student->id,
            'amount' => 100,
            'status' => 'pending',
            'payment_method' => 'cash',
            'receipt_path' => $path,
        ]);

        return $purchase;
    }
}
