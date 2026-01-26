<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\DeliveryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function userOrders(Request $request)
    {
        $orders = Order::with(['items.product', 'jastiper:id,username'])
                      ->where('user_id', $request->user()->id)
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_address' => 'required|string|min:10',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        $cartItems = Cart::with('product')
                        ->where('user_id', $user->id)
                        ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock untuk ' . $item->product->name . ' tidak cukup. Tersedia: ' . $item->product->stock
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_code' => 'ORD' . time() . rand(100, 999),
                'user_id' => $user->id,
                'total_amount' => 0,
                'status' => 'pending',
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;
            
            foreach ($cartItems as $cartItem) {
                $subtotal = $cartItem->product->price * $cartItem->quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price_at_time' => $cartItem->product->price,
                    'subtotal' => $subtotal,
                ]);
                
                $totalAmount += $subtotal;
                $cartItem->product->decrement('stock', $cartItem->quantity);
            }
            
            $order->update(['total_amount' => $totalAmount]);
            Cart::where('user_id', $user->id)->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $order->load('items.product'),
                'message' => 'Order created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        
        if ($order->user_id !== $user->id && 
            $order->jastiper_id !== $user->id && 
            $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['items.product', 'user:id,username', 'jastiper:id,username']);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order details retrieved successfully'
        ]);
    }

    public function availableOrders(Request $request)
    {
        $orders = Order::with(['user:id,username', 'items.product:id,name'])
                      ->where('status', 'pending')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => [
                'available_orders' => $orders,
                'total_available' => Order::where('status', 'pending')->count()
            ],
            'message' => 'Available orders retrieved successfully'
        ]);
    }

    public function acceptOrder(Request $request, Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah diambil atau tidak tersedia'
            ], 400);
        }

        $order->update([
            'jastiper_id' => $request->user()->id,
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $order->load('user:id,username'),
            'message' => 'Order accepted successfully'
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:delivered,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        if ($order->jastiper_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $newStatus = $request->status;
            
            if ($newStatus === 'delivered') {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now()
                ]);
                
                $message = 'Order marked as delivered';
                
            } elseif ($newStatus === 'completed') {
                $commission = $order->total_amount * 0.10;
                
                $order->update([
                    'status' => 'completed',
                    'jastiper_commission' => $commission,
                    'completed_at' => now()
                ]);
                
                DeliveryHistory::create([
                    'jastiper_id' => $user->id,
                    'order_id' => $order->id,
                    'commission' => $commission,
                    'delivered_at' => now()
                ]);
                
                $message = 'Order completed. Komisi: Rp ' . number_format($commission);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activeDeliveries(Request $request)
    {
        $orders = Order::with(['user:id,username'])
                      ->where('jastiper_id', $request->user()->id)
                      ->whereIn('status', ['accepted', 'delivered'])
                      ->orderBy('accepted_at', 'desc')
                      ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Active deliveries retrieved successfully'
        ]);
    }

    public function deliveryHistory(Request $request)
    {
        $orders = Order::with(['user:id,username'])
                      ->where('jastiper_id', $request->user()->id)
                      ->where('status', 'completed')
                      ->orderBy('completed_at', 'desc')
                      ->paginate(10);
        
        $totalEarnings = Order::where('jastiper_id', $request->user()->id)
                             ->where('status', 'completed')
                             ->sum('jastiper_commission');
        
        return response()->json([
            'success' => true,
            'data' => [
                'history' => $orders,
                'total_earnings' => $totalEarnings,
                'total_deliveries' => $orders->total()
            ],
            'message' => 'Delivery history retrieved successfully'
        ]);
    }
}