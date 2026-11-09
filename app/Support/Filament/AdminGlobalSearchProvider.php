<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Pages\ManageSteadfastCourier;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

/**
 * The admin topbar search. Besides the records Filament finds on its own, it
 * matches the sidebar menu ("setting" → Site Settings, Shipping Methods …)
 * and every tab, section and field on the settings pages ("logo", "whatsapp",
 * "pixel"), linking straight to the tab that holds the match.
 */
class AdminGlobalSearchProvider extends DefaultGlobalSearchProvider
{
    /**
     * Pages whose form labels are searchable. Their tabs, sections and fields are read from the form itself.
     *
     * @var list<class-string<Page>>
     */
    public const SETTINGS_PAGES = [
        ManageSiteSettings::class,
        ManageSteadfastCourier::class,
    ];

    private const SETTINGS_RESULTS_LIMIT = 15;

    /**
     * Settings matches are listed tabs first, then sections, then fields.
     */
    private const KIND_ORDER = ['tab' => 0, 'section' => 1, 'field' => 2];

    public function getResults(string $query): ?GlobalSearchResults
    {
        $terms = preg_split('/\s+/u', trim($query), flags: PREG_SPLIT_NO_EMPTY) ?: [];
        $results = GlobalSearchResults::make();

        if ($terms === []) {
            return $results;
        }

        if (filled($menuResults = $this->menuResults($terms))) {
            $results->category('Admin Menu', $menuResults);
        }

        if (filled($settingsResults = $this->settingsResults($terms))) {
            $results->category('Settings', $settingsResults);
        }

        // Record groups come keyed by lowercase plural labels ("shipping methods"); title-case them like the menu.
        foreach (parent::getResults($query)?->getCategories() ?? [] as $category => $records) {
            $results->category(Str::ucwords($category), $records);
        }

        return $results;
    }

    /**
     * Sidebar items the signed-in user can open, matched on their label and group.
     *
     * @param  list<string>  $terms
     * @return list<GlobalSearchResult>
     */
    private function menuResults(array $terms): array
    {
        $results = [];

        foreach (Filament::getNavigation() as $group) {
            $groupLabel = (string) $group->getLabel();

            foreach (collect($group->getItems()) as $item) {
                foreach ([$item, ...collect($item->getChildItems())->values()] as $navigationItem) {
                    /** @var NavigationItem $navigationItem */
                    $url = $navigationItem->getUrl();

                    if (blank($url) || ! $this->matches($terms, $navigationItem->getLabel(), $groupLabel)) {
                        continue;
                    }

                    $results[] = new GlobalSearchResult(
                        title: $navigationItem->getLabel(),
                        url: $url,
                        details: filled($groupLabel) ? [$groupLabel] : [],
                    );
                }
            }
        }

        return $results;
    }

    /**
     * @param  list<string>  $terms
     * @return list<GlobalSearchResult>
     */
    private function settingsResults(array $terms): array
    {
        $entries = [];

        foreach (self::SETTINGS_PAGES as $page) {
            if (! $page::canAccess()) {
                continue;
            }

            $form = app($page)->getSchema('form');

            if ($form !== null) {
                $this->collectSettingsEntries($page, $form->getComponents(withHidden: true), null, null, [], null, $entries);
            }
        }

        $matches = [];

        foreach ($entries as $index => $entry) {
            if (! $this->matches($terms, $entry['title'], ...$entry['context'])) {
                continue;
            }

            // Labels that match on their own come before those matched only through their tab or section.
            $rank = ($this->matches($terms, $entry['title']) ? 0 : 10) + self::KIND_ORDER[$entry['kind']];

            $matches[] = ['rank' => $rank, 'index' => $index, 'entry' => $entry];
        }

        usort($matches, fn (array $a, array $b): int => [$a['rank'], $a['index']] <=> [$b['rank'], $b['index']]);

        return array_map(
            fn (array $match): GlobalSearchResult => new GlobalSearchResult(
                title: $match['entry']['title'],
                url: $match['entry']['url'],
                details: [implode(' › ', $match['entry']['trail'])],
            ),
            array_slice($matches, 0, self::SETTINGS_RESULTS_LIMIT),
        );
    }

    /**
     * Walks a settings form, recording each tab, section and field with the URL that opens its tab.
     *
     * Only tabs kept in the query string are locations; inner tabs such as English / বাংলা are walked through.
     *
     * @param  class-string<Page>  $page
     * @param  array<mixed>  $components
     * @param  array<string, string>  $urlParameters  query string that opens the enclosing tab
     * @param  ?string  $tabQueryStringKey  set while walking the tabs of a Tabs component kept in the query string
     * @param  list<array{kind: 'tab'|'section'|'field', title: string, context: list<string>, trail: list<string>, url: string}>  $entries
     */
    private function collectSettingsEntries(
        string $page,
        array $components,
        ?string $tab,
        ?string $section,
        array $urlParameters,
        ?string $tabQueryStringKey,
        array &$entries,
    ): void {
        $pageLabel = $page::getNavigationLabel();
        $url = $page::getUrl($urlParameters);
        $trail = array_values(array_filter([$pageLabel, $tab]));
        $context = array_values(array_filter([$tab, $section]));

        foreach ($components as $component) {
            if (! $component instanceof Component) {
                continue;
            }

            $childTab = $tab;
            $childSection = $section;
            $childUrlParameters = $urlParameters;

            if ($component instanceof Tab && filled($tabQueryStringKey)) {
                $childTab = $this->plainText($component->getLabel());
                $childSection = null;
                $childUrlParameters = [$tabQueryStringKey => $component->getKey(isAbsolute: false)];

                $entries[] = [
                    'kind' => 'tab',
                    'title' => $childTab,
                    'context' => [$pageLabel],
                    'trail' => [$pageLabel],
                    'url' => $page::getUrl($childUrlParameters),
                ];
            } elseif ($component instanceof Section && filled($heading = $this->plainText($component->getHeading()))) {
                $childSection = $heading;

                $entries[] = ['kind' => 'section', 'title' => $heading, 'context' => $context, 'trail' => $trail, 'url' => $url];
            } elseif ($component instanceof Field && ! $component instanceof Hidden && filled($label = $this->plainText($component->getLabel()))) {
                $entries[] = ['kind' => 'field', 'title' => $label, 'context' => $context, 'trail' => $trail, 'url' => $url];
            }

            $childTabQueryStringKey = $component instanceof Tabs && $component->isTabPersistedInQueryString()
                ? $component->getTabQueryStringKey()
                : null;

            foreach ($component->getChildSchemas(withHidden: true) as $childSchema) {
                $this->collectSettingsEntries(
                    $page,
                    $childSchema->getComponents(withHidden: true),
                    $childTab,
                    $childSection,
                    $childUrlParameters,
                    $childTabQueryStringKey,
                    $entries,
                );
            }
        }
    }

    /**
     * Whether every search word appears somewhere in the given texts, ignoring case.
     *
     * @param  list<string>  $terms
     */
    private function matches(array $terms, ?string ...$texts): bool
    {
        return Str::containsAll(implode(' ', array_filter($texts)), $terms, ignoreCase: true);
    }

    private function plainText(string|Htmlable|null $text): string
    {
        return trim(strip_tags($text instanceof Htmlable ? $text->toHtml() : (string) $text));
    }
}
