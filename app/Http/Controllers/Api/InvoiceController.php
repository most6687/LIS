<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class InvoiceController extends Controller
{

    public function index()
    {
        return Invoice::with('items')->get();
    }

    public function show($id)
    {
        return Invoice::with('items')->findOrFail($id);
    }

    public function store(Request $request)
    {

        $invoice = Invoice::create([
            'patient_id' => $request->patient_id,
            'order_id' => $request->order_id,
            'total_amount' => $request->total_amount,
            'paid_amount' => 0,
            'remaining_amount' => $request->total_amount,
            'status' => 'unpaid'
        ]);

        if ($request->items) {

            foreach ($request->items as $item) {

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'test_id' => $item['test_id'],
                    'price' => $item['price']
                ]);

            }

        }

        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {

        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'paid_amount' => $request->paid_amount,
            'remaining_amount' => $request->remaining_amount,
            'status' => $request->status
        ]);

        return response()->json($invoice);
    }

    public function destroy($id)
    {

        $invoice = Invoice::findOrFail($id);

        InvoiceItem::where('invoice_id', $invoice->id)->delete();

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

}
