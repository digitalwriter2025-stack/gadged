<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreReviewRequest;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class ReviewController extends Controller
{
    /**
     * Store a customer's review; it shows on the product page once staff approve it.
     */
    public function __invoke(StoreReviewRequest $request, Product $product, ReviewService $reviews): RedirectResponse
    {
        abort_unless($product->isPublished(), 404);

        $productUrl = route('store.products.show', $product->slug).'#reviews';

        try {
            $reviews->submit($request->user(), $product, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->to($productUrl)->withErrors(['review' => $exception->getMessage()]);
        }

        return redirect()->to($productUrl)->with('status', 'review-submitted');
    }
}
