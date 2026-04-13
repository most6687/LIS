<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Inventory",
 *     description="API endpoints for managing laboratory inventory and supplies"
 * )
 */
class InventoryController extends Controller
{
    /**
     * Get all inventory items
     *
     * @OA\Get(
     *     path="/api/inventory",
     *     operationId="getInventory",
     *     tags={"Inventory"},
     *     summary="Retrieve all inventory items",
     *     description="Get a list of all items in the laboratory inventory",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer", example=1),
     *                 @OA\Property(property="Item_Name", type="string", example="Glucose Test Strips"),
     *                 @OA\Property(property="Quantity", type="integer", example=100),
     *                 @OA\Property(property="Min_Level", type="integer", example=10),
     *                 @OA\Property(property="Expiry_Date", type="string", format="date"),
     *                 @OA\Property(property="Category", type="string", example="Consumables"),
     *                 @OA\Property(property="Storage_Location", type="string", example="Shelf A1"),
     *                 @OA\Property(property="Unit_Price", type="number", format="double", example=5.50),
     *                 @OA\Property(property="Last_Restock_Date", type="string", format="date"),
     *                 @OA\Property(property="Needs_Restock", type="boolean", example=false),
     *                 @OA\Property(property="Supplier_Info", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $inventory = Inventory::all();
        return response()->json($inventory);
    }

    /**
     * Get items that need restocking
     *
     * @OA\Get(
     *     path="/api/inventory/needs-restock",
     *     operationId="getItemsNeedRestock",
     *     tags={"Inventory"},
     *     summary="Get items that need restocking",
     *     description="Retrieve inventory items that have been flagged as needing restock",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Min_Level", type="integer"),
     *                 @OA\Property(property="Needs_Restock", type="boolean", example=true),
     *                 @OA\Property(property="Category", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function needsRestock()
    {
        $items = Inventory::needsRestock()->get();
        return response()->json($items);
    }

    /**
     * Get low stock items
     *
     * @OA\Get(
     *     path="/api/inventory/low-stock",
     *     operationId="getLowStockItems",
     *     tags={"Inventory"},
     *     summary="Get low stock items",
     *     description="Retrieve inventory items with quantities below minimum level",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Min_Level", type="integer"),
     *                 @OA\Property(property="Category", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function lowStock()
    {
        $items = Inventory::lowStock()->get();
        return response()->json($items);
    }

    /**
     * Get expired items
     *
     * @OA\Get(
     *     path="/api/inventory/expired",
     *     operationId="getExpiredItems",
     *     tags={"Inventory"},
     *     summary="Get expired items",
     *     description="Retrieve inventory items that have passed their expiry date",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Expiry_Date", type="string", format="date"),
     *                 @OA\Property(property="Category", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function expired()
    {
        $items = Inventory::expired()->get();
        return response()->json($items);
    }

    /**
     * Get items expiring soon
     *
     * @OA\Get(
     *     path="/api/inventory/expiring-soon",
     *     operationId="getExpiringItems",
     *     tags={"Inventory"},
     *     summary="Get items expiring soon",
     *     description="Retrieve inventory items that will expire within specified days (default 30)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         required=false,
     *         description="Number of days to check for expiry",
     *         @OA\Schema(type="integer", example=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Expiry_Date", type="string", format="date"),
     *                 @OA\Property(property="Category", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function expiringSoon(Request $request)
    {
        $days = $request->get('days', 30);
        $items = Inventory::expiringSoon($days)->get();
        return response()->json($items);
    }

    /**
     * Get items by category
     *
     * @OA\Get(
     *     path="/api/inventory/category/{category}",
     *     operationId="getItemsByCategory",
     *     tags={"Inventory"},
     *     summary="Get items by category",
     *     description="Retrieve all inventory items in a specific category",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category name",
     *         @OA\Schema(type="string", example="Consumables")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Category", type="string"),
     *                 @OA\Property(property="Quantity", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byCategory($category)
    {
        $items = Inventory::byCategory($category)->get();
        return response()->json($items);
    }

    /**
     * Get a specific inventory item
     *
     * @OA\Get(
     *     path="/api/inventory/{id}",
     *     operationId="getInventoryItem",
     *     tags={"Inventory"},
     *     summary="Retrieve an inventory item by ID",
     *     description="Get details of a specific inventory item",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inventory item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Inventory_ID", type="integer"),
     *             @OA\Property(property="Item_Name", type="string"),
     *             @OA\Property(property="Quantity", type="integer"),
     *             @OA\Property(property="Min_Level", type="integer"),
     *             @OA\Property(property="Expiry_Date", type="string", format="date"),
     *             @OA\Property(property="Category", type="string"),
     *             @OA\Property(property="Storage_Location", type="string"),
     *             @OA\Property(property="Unit_Price", type="number", format="double"),
     *             @OA\Property(property="Last_Restock_Date", type="string", format="date"),
     *             @OA\Property(property="Needs_Restock", type="boolean"),
     *             @OA\Property(property="Supplier_Info", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory item not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $item = Inventory::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Create a new inventory item
     *
     * @OA\Post(
     *     path="/api/inventory",
     *     operationId="storeInventoryItem",
     *     tags={"Inventory"},
     *     summary="Create a new inventory item",
     *     description="Store a newly created inventory item in the system",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Inventory item data",
     *         @OA\JsonContent(
     *             required={"Item_Name", "Quantity"},
     *             @OA\Property(property="Item_Name", type="string", maxLength=100, example="Glucose Test Strips"),
     *             @OA\Property(property="Quantity", type="integer", example=100),
     *             @OA\Property(property="Min_Level", type="integer", nullable=true, example=10),
     *             @OA\Property(property="Expiry_Date", type="string", format="date", nullable=true, example="2026-12-31"),
     *             @OA\Property(property="Category", type="string", maxLength=50, nullable=true, example="Consumables"),
     *             @OA\Property(property="Storage_Location", type="string", maxLength=50, nullable=true, example="Shelf A1"),
     *             @OA\Property(property="Unit_Price", type="number", format="double", nullable=true, example=5.50),
     *             @OA\Property(property="Supplier_Info", type="string", maxLength=200, nullable=true, example="MedSupply Inc.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inventory item created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Inventory item created successfully"),
     *             @OA\Property(property="item", type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Min_Level", type="integer"),
     *                 @OA\Property(property="Expiry_Date", type="string", format="date"),
     *                 @OA\Property(property="Category", type="string"),
     *                 @OA\Property(property="Storage_Location", type="string"),
     *                 @OA\Property(property="Unit_Price", type="number", format="double"),
     *                 @OA\Property(property="Last_Restock_Date", type="string", format="date"),
     *                 @OA\Property(property="Needs_Restock", type="boolean"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'Item_Name' => 'required|string|max:100',
            'Quantity' => 'required|integer|min:0',
            'Min_Level' => 'nullable|integer|min:0',
            'Expiry_Date' => 'nullable|date',
            'Supplier_Info' => 'nullable|string|max:200',
            'Category' => 'nullable|string|max:50',
            'Storage_Location' => 'nullable|string|max:50',
            'Unit_Price' => 'nullable|numeric|min:0',
        ]);

        // تحديد Needs_Restock بناءً على الكمية
        $minLevel = $request->Min_Level ?? 10;
        $needsRestock = $request->Quantity <= $minLevel;

        $item = Inventory::create([
            'Item_Name' => $request->Item_Name,
            'Quantity' => $request->Quantity,
            'Min_Level' => $minLevel,
            'Expiry_Date' => $request->Expiry_Date,
            'Supplier_Info' => $request->Supplier_Info,
            'Category' => $request->Category,
            'Last_Restock_Date' => $request->Quantity > 0 ? Carbon::today() : null,
            'Needs_Restock' => $needsRestock,
            'Storage_Location' => $request->Storage_Location,
            'Unit_Price' => $request->Unit_Price ?? 0,
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Inventory',
            'Action' => 'Item Added',
            'Description' => 'New inventory item added: ' . $item->Item_Name
        ]);

        return response()->json([
            'message' => 'Inventory item created successfully',
            'item' => $item
        ], 201);
    }

    /**
     * Update an inventory item
     *
     * @OA\Put(
     *     path="/api/inventory/{id}",
     *     operationId="updateInventoryItem",
     *     tags={"Inventory"},
     *     summary="Update an inventory item",
     *     description="Update details of an existing inventory item",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inventory item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated inventory item data",
     *         @OA\JsonContent(
     *             @OA\Property(property="Item_Name", type="string", maxLength=100, example="Updated Item Name"),
     *             @OA\Property(property="Quantity", type="integer", example=150),
     *             @OA\Property(property="Min_Level", type="integer", nullable=true, example=10),
     *             @OA\Property(property="Expiry_Date", type="string", format="date", nullable=true),
     *             @OA\Property(property="Category", type="string", maxLength=50, nullable=true),
     *             @OA\Property(property="Storage_Location", type="string", maxLength=50, nullable=true),
     *             @OA\Property(property="Unit_Price", type="number", format="double", nullable=true),
     *             @OA\Property(property="Supplier_Info", type="string", maxLength=200, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Inventory item updated successfully"),
     *             @OA\Property(property="item", type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory item not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $request->validate([
            'Item_Name' => 'sometimes|string|max:100',
            'Quantity' => 'sometimes|integer|min:0',
            'Min_Level' => 'nullable|integer|min:0',
            'Expiry_Date' => 'nullable|date',
            'Supplier_Info' => 'nullable|string|max:200',
            'Category' => 'nullable|string|max:50',
            'Storage_Location' => 'nullable|string|max:50',
            'Unit_Price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only(['Item_Name', 'Quantity', 'Min_Level', 'Expiry_Date', 'Supplier_Info', 'Category', 'Storage_Location', 'Unit_Price']);

        // تحديث Needs_Restock إذا تغيرت الكمية
        if ($request->has('Quantity')) {
            $minLevel = $request->Min_Level ?? $item->Min_Level ?? 10;
            $data['Needs_Restock'] = $request->Quantity <= $minLevel;
        }

        $item->update($data);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Inventory',
            'Action' => 'Item Updated',
            'Description' => 'Inventory item updated: ' . $item->Item_Name
        ]);

        return response()->json([
            'message' => 'Inventory item updated successfully',
            'item' => $item
        ]);
    }

    /**
     * Restock an inventory item
     *
     * @OA\Post(
     *     path="/api/inventory/{id}/restock",
     *     operationId="restockInventoryItem",
     *     tags={"Inventory"},
     *     summary="Restock an inventory item",
     *     description="Add stock quantity to an inventory item",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inventory item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restock quantity",
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", minimum=1, example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item restocked successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item restocked successfully"),
     *             @OA\Property(property="item", type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="Last_Restock_Date", type="string", format="date"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory item not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function restock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = Inventory::findOrFail($id);
        $item->restock($request->quantity);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Inventory',
            'Action' => 'Item Restocked',
            'Description' => 'Restocked ' . $request->quantity . ' units of ' . $item->Item_Name
        ]);

        return response()->json([
            'message' => 'Item restocked successfully',
            'item' => $item
        ]);
    }

    /**
     * Consume inventory item
     *
     * @OA\Post(
     *     path="/api/inventory/{id}/consume",
     *     operationId="consumeInventoryItem",
     *     tags={"Inventory"},
     *     summary="Consume inventory item",
     *     description="Record consumption/usage of inventory items",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inventory item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Consumption quantity",
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", minimum=1, example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item consumed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item consumed successfully"),
     *             @OA\Property(property="item", type="object",
     *                 @OA\Property(property="Inventory_ID", type="integer"),
     *                 @OA\Property(property="Item_Name", type="string"),
     *                 @OA\Property(property="Quantity", type="integer"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Insufficient stock or validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Insufficient stock"),
     *             @OA\Property(property="available_quantity", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory item not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function consume(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = Inventory::findOrFail($id);

        if ($item->consume($request->quantity)) {
            ActivityLog::create([
                'User_ID' => Auth::user()->User_ID,
                'Module' => 'Inventory',
                'Action' => 'Item Consumed',
                'Description' => 'Consumed ' . $request->quantity . ' units of ' . $item->Item_Name
            ]);

            return response()->json([
                'message' => 'Item consumed successfully',
                'item' => $item
            ]);
        }

        return response()->json([
            'message' => 'Insufficient stock',
            'available_quantity' => $item->Quantity
        ], 422);
    }

    /**
     * Delete an inventory item
     *
     * @OA\Delete(
     *     path="/api/inventory/{id}",
     *     operationId="destroyInventoryItem",
     *     tags={"Inventory"},
     *     summary="Delete an inventory item",
     *     description="Soft delete an inventory item from the system",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inventory item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Inventory item deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory item not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        $itemName = $item->Item_Name;
        $item->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Inventory',
            'Action' => 'Item Deleted',
            'Description' => 'Inventory item deleted: ' . $itemName
        ]);

        return response()->json(['message' => 'Inventory item deleted successfully']);
    }

    /**
     * Get inventory statistics
     *
     * @OA\Get(
     *     path="/api/inventory/statistics/dashboard",
     *     operationId="getInventoryStatistics",
     *     tags={"Inventory"},
     *     summary="Get inventory statistics",
     *     description="Retrieve inventory dashboard statistics including counts and totals",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_items", type="integer", example=45),
     *             @OA\Property(property="low_stock_count", type="integer", example=8),
     *             @OA\Property(property="needs_restock_count", type="integer", example=5),
     *             @OA\Property(property="out_of_stock_count", type="integer", example=2),
     *             @OA\Property(property="expired_count", type="integer", example=1),
     *             @OA\Property(property="expiring_soon_count", type="integer", example=3),
     *             @OA\Property(property="total_inventory_value", type="number", format="double", example=1250.75),
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Category", type="string", example="Consumables"),
     *                     @OA\Property(property="count", type="integer", example=15),
     *                     @OA\Property(property="total_quantity", type="integer", example=250)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function statistics()
    {
        $totalItems = Inventory::count();
        $lowStockCount = Inventory::lowStock()->count();
        $needsRestockCount = Inventory::needsRestock()->count();
        $outOfStockCount = Inventory::outOfStock()->count();
        $expiredCount = Inventory::expired()->count();
        $expiringSoonCount = Inventory::expiringSoon(30)->count();

        $totalValue = Inventory::sum(DB::raw('Quantity * Unit_Price'));

        $categories = Inventory::select('Category')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(Quantity) as total_quantity')
            ->groupBy('Category')
            ->get();

        return response()->json([
            'total_items' => $totalItems,
            'low_stock_count' => $lowStockCount,
            'needs_restock_count' => $needsRestockCount,
            'out_of_stock_count' => $outOfStockCount,
            'expired_count' => $expiredCount,
            'expiring_soon_count' => $expiringSoonCount,
            'total_inventory_value' => $totalValue,
            'categories' => $categories,
        ]);
    }
}