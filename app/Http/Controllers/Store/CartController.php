<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\MetaPixel;
use App\Support\CartLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    /**
     * Put a product in the cart and continue straight to the order page.
     */
    public function store(Request $request, CartService $cart, MetaPixel $metaPixel): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.$cart->maxQuantity()],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);

        if (! $cart->add($product, $quantity)) {
            return back()->withErrors(['product_id' => "{$product->name} can't be ordered right now."]);
        }

        if ($metaPixel->enabled()) {
            $line = $cart->lines()->first(fn (CartLine $line): bool => $line->product->is($product));

            if ($line !== null) {
                $metaPixel->addToCart($line, $quantity);
            }
        }

        return redirect()->route('store.checkout.show');
    }

    /**
     * Change the quantity of a cart line.
     */
    public function update(Request $request, int $variant, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.$cart->maxQuantity()],
        ]);

        $cart->update($variant, (int) $validated['quantity']);

        return redirect()->route('store.checkout.show');
    }

    /**
     * Remove a line from the cart.
     */
    public function destroy(int $variant, CartService $cart): RedirectResponse
    {
        $cart->remove($variant);

        return redirect()->route('store.checkout.show');
    }
}
