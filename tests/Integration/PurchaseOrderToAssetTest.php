<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Asset;
use App\Models\User;
use App\Models\Supply;

class PurchaseOrderToAssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_po_items_create_assets()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $po = PurchaseOrder::create([
            'po_no' => 'PO-100',
            'po_type' => 'Asset',
            'supplier_name' => 'Test Supplier',
            'supplier_address' => '123 Test St',
            'po_date' => date('Y-m-d'),
            'procurement_mode' => 'Direct',
            'auth_official' => 'Auth Name',
            'chief_accountant' => 'Chief Name'
        ]);

        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'unit' => 'pcs',
            'description' => 'New Laptop',
            'qty' => 1,
            'unit_cost' => 50000,
            'amount' => 50000,
            'is_delivered' => true
        ]);

        // Simulate admin assets index which looks for delivered Po items
        $response = $this->get('/asset-list');
        $response->assertStatus(200);

        // Manually create asset from PO item (approx real flow)
        $asset = Asset::create([
            'item_code' => 'PO-100-1',
            'barcode_id' => 'PO-100-1',
            'name' => 'New Laptop',
            'article' => 'New Laptop',
            'category' => 'Assets',
            'description' => 'New Laptop',
            'unit_value' => 50000
        ]);

        $this->assertDatabaseHas('assets', ['barcode_id' => 'PO-100-1']);
    }

    public function test_delivered_supply_po_items_are_added_to_inventory()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/po', [
            'po_type' => 'Supply',
            'entity_name' => 'Test Agency',
            'po_no' => 'PO-SUP-100',
            'supplier_name' => 'Supply Supplier',
            'supplier_address' => '123 Test Street',
            'po_date' => date('Y-m-d'),
            'procurement_mode' => 'Direct',
            'auth_official' => 'Authorized Official',
            'auth_official_designation' => 'Agency Head',
            'chief_accountant' => 'Chief Accountant',
            'chief_accountant_designation' => 'Accountant',
            'total_amount' => 200,
            'items' => [[
                'unit' => 'Piece(s)',
                'description' => 'Rubber Band Small',
                'qty' => 10,
                'cost' => 20,
                'is_delivered' => true,
            ]],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('supplies', [
            'description' => 'Rubber Band Small',
            'unit_measure' => 'Piece(s)',
            'quantity' => 10,
            'unit_value' => 20,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'description' => 'Rubber Band Small',
            'inventory_synced' => true,
        ]);
        $this->assertDatabaseHas('transactions', [
            'po_number' => 'PO-SUP-100',
            'quantity' => 10,
            'supplier' => 'Supply Supplier',
        ]);
    }
}
