<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinancialIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_quotation_cannot_be_linked_to_two_sales(): void
    {
        [$client, $quotation] = $this->clientAndQuotation();

        $this->createSale($client, $quotation);

        $this->expectException(QueryException::class);
        $this->createSale($client, $quotation);
    }

    public function test_payment_service_rejects_an_amount_above_the_remaining_balance(): void
    {
        [$client, $quotation] = $this->clientAndQuotation();
        $sale = $this->createSale($client, $quotation);

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->create($sale, [
            'amount' => 1001,
            'payment_method' => 'cash',
            'paid_at' => now()->toDateString(),
        ], null);
    }

    public function test_reversed_payments_remain_in_the_audit_trail_and_no_longer_count_as_paid(): void
    {
        [$client, $quotation] = $this->clientAndQuotation();
        $sale = $this->createSale($client, $quotation);
        $service = app(PaymentService::class);

        $payment = $service->create($sale, [
            'amount' => 250,
            'payment_method' => 'cash',
            'paid_at' => now()->toDateString(),
        ], null);

        $this->assertSame('partial', $sale->fresh()->status);

        $service->reverse($payment, 'تم تسجيل الدفعة بالخطأ', null);

        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('payment_reversals', [
            'payment_id' => $payment->id,
            'amount' => 250,
            'reason' => 'تم تسجيل الدفعة بالخطأ',
        ]);
        $this->assertSame(0.0, (float) $sale->payments()->sum('amount'));
        $this->assertSame('pending', $sale->fresh()->status);
        $this->assertNotNull(Payment::withTrashed()->find($payment->id));
    }

    private function clientAndQuotation(): array
    {
        $client = Client::query()->create([
            'name' => 'Test Client',
            'status' => 'active',
        ]);

        $quotation = Quotation::query()->create([
            'client_id' => $client->id,
            'quotation_number' => 'QTN-TEST-' . fake()->unique()->numberBetween(1, 999999),
            'subtotal' => 1000,
            'vat' => 0,
            'total' => 1000,
            'status' => 'open',
        ]);

        return [$client, $quotation];
    }

    private function createSale(Client $client, Quotation $quotation): Sale
    {
        return Sale::query()->create([
            'client_id' => $client->id,
            'quotation_id' => $quotation->id,
            'subtotal' => 1000,
            'vat' => 0,
            'total' => 1000,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);
    }
}
