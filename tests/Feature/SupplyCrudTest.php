<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Supply;
use App\Models\Transaction;
use App\Models\User;

class SupplyCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_stock_transaction_and_delete_supply()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create supply
        $resp = $this->post('/supplies', [
            'article' => 'Test Supply',
            'description' => 'Test Description',
            'supplier' => 'Test Supplier',
            'unit_measure' => 'pcs',
            'unit_value' => 10.5,
            'initial_quantity' => 20
        ]);

        $resp->assertStatus(302);

        $supply = Supply::first();
        $this->assertNotNull($supply);
        $this->assertEquals(20, $supply->quantity);

        // Details
        $details = $this->get('/supplies/'.$supply->id.'/details');
        $details->assertStatus(200);

        // Update
        $update = $this->put('/supplies/'.$supply->id, [
            'barcode_id' => $supply->barcode_id,
            'article' => 'Updated Supply',
            'description' => 'Updated',
            'supplier' => 'Test Supplier',
            'unit_measure' => 'pcs',
            'unit_value' => 12.0,
            'quantity' => 25
        ]);

        $update->assertStatus(302);
        $supply->refresh();
        $this->assertEquals('Updated Supply', $supply->article);
        $this->assertEquals(25, $supply->quantity);

        // Stock transaction IN
        $in = $this->post('/supplies/'.$supply->id.'/transaction', [
            'qty' => 5,
            'transaction_type' => 'IN',
            'supplier' => 'Test Supplier',
            'transaction_date' => date('Y-m-d'),
            'remarks' => 'Stock IN'
        ]);
        $in->assertStatus(302);
        $supply->refresh();
        $this->assertEquals(30, $supply->quantity);

        $stockCard = $this->get('/supplies/'.$supply->id.'/stock-card');
        $stockCard->assertOk()
            ->assertSee('STOCK CARD')
            ->assertSee('Receipt')
            ->assertSee('Issuance')
            ->assertSee('Balance')
            ->assertSee('Updated Supply');

        // Receive a partial delivery and apply weighted-average pricing.
        $receive = $this->post('/supplies/'.$supply->id.'/receive', [
            'quantity' => 50,
            'unit_price' => 40,
            'supplier' => 'New Supplier',
            'po_number' => 'PO-2026-001',
            'delivery_receipt' => 'DR-2026-001',
            'office' => 'Records Office',
            'receipt_status' => 'Partial',
            'transaction_date' => date('Y-m-d'),
        ]);
        $receive->assertStatus(302);
        $supply->refresh();
        $this->assertEquals(80, $supply->quantity);
        $this->assertEquals(29.50, (float) $supply->unit_value);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $supply->id,
            'transaction_type' => 'IN',
            'quantity' => 50,
            'po_number' => 'PO-2026-001',
            'delivery_receipt' => 'DR-2026-001',
            'office' => 'Records Office',
            'receipt_status' => 'Partial',
        ]);

        $sameTraits = $this->post('/supplies/'.$supply->id.'/receive', [
            'quantity' => 10,
            'unit_price' => 35,
            'supplier' => 'Another Supplier',
            'po_number' => 'PO-2026-002',
            'receipt_status' => 'Complete',
        ]);
        $sameTraits->assertStatus(302);
        $supply->refresh();
        $this->assertEquals(90, $supply->quantity);
        $this->assertEquals(30.11, (float) $supply->unit_value);
        $this->assertSame(1, Supply::where('description', 'Updated')->where('unit_measure', 'pcs')->count());

        // Stock transaction OUT
        $out = $this->post('/supplies/'.$supply->id.'/transaction', [
            'qty' => 10,
            'transaction_type' => 'OUT',
            'supplier' => 'Test Supplier',
            'transaction_date' => date('Y-m-d'),
            'remarks' => 'Stock OUT'
        ]);
        $out->assertStatus(302);
        $supply->refresh();
        $this->assertEquals(80, $supply->quantity);

        // Delete
        $del = $this->delete('/supplies/'.$supply->id);
        $del->assertStatus(302);
        $this->assertDatabaseMissing('supplies', ['id' => $supply->id]);
    }
}
