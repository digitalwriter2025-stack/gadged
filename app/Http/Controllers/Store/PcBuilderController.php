<?php

namespace App\Http\Controllers\Store;

use App\Enums\PcBuilderComponent;
use App\Http\Controllers\Controller;
use App\Services\PcBuilderService;
use App\Support\Storefront\PcBuilderPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PcBuilderController extends Controller
{
    /**
     * Show the shopper's current build.
     */
    public function show(PcBuilderPresenter $presenter): View
    {
        return view('store.pc-builder.show', $presenter->present());
    }

    /**
     * List products that can fill a component slot.
     */
    public function choose(Request $request, PcBuilderComponent $component, PcBuilderPresenter $presenter): View
    {
        $sort = $request->query('sort');
        $sort = is_string($sort) && array_key_exists($sort, PcBuilderPresenter::SORTS) ? $sort : 'price_asc';

        return view('store.pc-builder.choose', $presenter->presentChoices($component, $sort));
    }

    /**
     * Put a product into a component slot.
     */
    public function store(Request $request, PcBuilderComponent $component, PcBuilderService $builder): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
        ]);

        if (! $builder->choose($component, (int) $validated['product_id'])) {
            return back()->withErrors(['product_id' => "That product can't be used as a {$component->label()}."]);
        }

        return redirect()->route('store.pc-builder.show')->with('status', "{$component->label()} added to your build.");
    }

    /**
     * Empty a component slot.
     */
    public function destroy(PcBuilderComponent $component, PcBuilderService $builder): RedirectResponse
    {
        $builder->remove($component);

        return redirect()->route('store.pc-builder.show');
    }

    /**
     * Start the build over.
     */
    public function clear(PcBuilderService $builder): RedirectResponse
    {
        $builder->clear();

        return redirect()->route('store.pc-builder.show');
    }
}
