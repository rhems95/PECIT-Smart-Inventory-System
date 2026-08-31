<?php

namespace Tests\Feature;

use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;
use App\Models\User;
use App\Support\OrderStatusTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_purchase_show_includes_status_tracker(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $purchase = PurchaseRequest::create([
            'purchase_number' => 'PUR-TRACK',
            'user_id' => $user->id,
            'status' => 'payment_submitted',
            'total_amount' => 250,
        ]);

        $response = $this->actingAs($user)->get(route('purchases.show', $purchase));

        $response->assertOk();
        $response->assertSee('Order placed');
        $response->assertSee('Pay at Accounting');
        $response->assertSee('Claim at Supply');
        $response->assertSee('psis-tracker-step-current', false);
    }

    public function test_faculty_request_show_includes_status_tracker(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Faculty');

        $request = SupplyRequest::create([
            'request_number' => 'REQ-TRACK',
            'user_id' => $user->id,
            'type' => 'faculty',
            'status' => 'admin_review',
            'purpose' => 'Classroom supplies',
            'total_amount' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('requests.show', $request));

        $response->assertOk();
        $response->assertSee('Submitted');
        $response->assertSee('Accounting review');
        $response->assertSee('Admin approval');
        $response->assertSee('Waiting for approval');
    }

    public function test_purchase_tracker_marks_released_steps_complete(): void
    {
        $purchase = new PurchaseRequest([
            'status' => 'released',
            'created_at' => now(),
            'verified_at' => now(),
            'released_at' => now(),
        ]);

        $tracker = OrderStatusTracker::forPurchase($purchase);

        $this->assertSame('Released', $tracker['headline']);
        $this->assertFalse($tracker['failed']);
        $this->assertSame(['done', 'done', 'done', 'done'], array_column($tracker['steps'], 'state'));
    }

    public function test_supply_request_tracker_marks_rejected(): void
    {
        $request = new SupplyRequest([
            'status' => 'rejected',
            'rejection_reason' => 'Over budget',
            'created_at' => now(),
        ]);

        $tracker = OrderStatusTracker::forSupplyRequest($request);

        $this->assertTrue($tracker['failed']);
        $this->assertSame('Rejected', $tracker['headline']);
        $this->assertStringContainsString('Over budget', $tracker['hint']);
        $this->assertSame('failed', $tracker['steps'][1]['state']);
    }
}
