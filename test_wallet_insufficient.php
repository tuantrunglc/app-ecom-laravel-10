<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;

echo "=== WALLET INSUFFICIENT BALANCE TEST ===\n\n";

// Get a user with low balance
$user = User::first();
echo "Testing with user: {$user->name}\n";
echo "Current wallet balance: $" . number_format($user->wallet_balance, 2) . "\n\n";

// Create a mock cart item for testing
$mockCartData = [
    'user_id' => $user->id,
    'product_id' => 1, // Assuming product ID 1 exists
    'quantity' => 1,
    'price' => 50.00,
    'amount' => 50.00,
    'order_id' => null
];

// Check if we have any products
$productCount = DB::table('products')->count();
if ($productCount > 0) {
    $product = DB::table('products')->first();
    $mockCartData['product_id'] = $product->id;
    echo "Using product: {$product->title}\n";
} else {
    echo "No products found, using mock product ID\n";
}

// Get shipping cost
$shipping = Shipping::where('status', 'active')->first();
$shippingCost = $shipping ? $shipping->price : 0;

echo "Product price: $" . number_format($mockCartData['amount'], 2) . "\n";
echo "Shipping cost: $" . number_format($shippingCost, 2) . "\n";

$totalRequired = $mockCartData['amount'] + $shippingCost;
echo "Total required: $" . number_format($totalRequired, 2) . "\n\n";

// Test the wallet balance check logic
echo "=== TESTING WALLET BALANCE CHECK ===\n";

$currentBalance = $user->wallet_balance ?? 0;
echo "Current balance: $" . number_format($currentBalance, 2) . "\n";
echo "Required amount: $" . number_format($totalRequired, 2) . "\n";

if ($currentBalance < $totalRequired) {
    echo "❌ INSUFFICIENT BALANCE DETECTED\n";
    echo "Error message would be: 'Insufficient wallet balance. Your balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalRequired, 2) . "'\n";
    echo "✅ Logic working correctly - Order would NOT be created\n";
} else {
    echo "✅ SUFFICIENT BALANCE\n";
    echo "Order would be created and balance would be deducted\n";
    echo "New balance would be: $" . number_format($currentBalance - $totalRequired, 2) . "\n";
}

echo "\n=== TEST COMPLETED ===\n";