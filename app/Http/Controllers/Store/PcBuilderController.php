<?php

namespace App\Http\Controllers\Store;

use App\Enums\PcBuilderComponent;
use App\Http\Controllers\Controller;
use App\Services\PcBuilderService;
use App\Support\Storefront\PcBuilderPresenter;
use App\Support\Storefront\StoreLayout;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PcBuilderController extends Controller implements HasMiddleware
{
    /**
     * The builder is only reachable while it is switched on in Site Settings.
     *
     * @return list<Closure>
     */
    public static function middleware(): array
    {
        return [
            function (Request $request, Closure $next): Response {
                abort_unless(app(StoreLayout::class)->headerSettings()['show_pc_builder'], 404);

                return $next($request);
            },
        ];
    }

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
            return back()->withErrors(['product_id' => __("That product can't be used as a :component.", ['component' => $component->label()])]);
        }

        return redirect()->route('store.pc-builder.show')->with('status', __(':component added to your build.', ['component' => $component->label()]));
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
