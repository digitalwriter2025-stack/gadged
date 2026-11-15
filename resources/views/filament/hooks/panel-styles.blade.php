<style>
    /*
     * Admin panel theme. Filament's own CSS lives in cascade layers, so these unlayered
     * rules win without !important. Plain CSS keeps it working without a Vite build.
     */
    :root {
        --gx-radius-lg: 0.75rem;
        --gx-radius-xl: 1rem;
        --gx-card-bg: #fff;
        --gx-card-ring: rgb(15 23 42 / 0.06);
        --gx-card-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 6px 20px -8px rgb(15 23 42 / 0.08);
        --gx-card-shadow-hover: 0 2px 4px rgb(15 23 42 / 0.05), 0 16px 32px -12px rgb(15 23 42 / 0.16);
        --gx-chrome-bg: rgb(255 255 255 / 0.82);
        --gx-chrome-border: rgb(15 23 42 / 0.07);
    }

    .dark {
        --gx-card-bg: var(--gray-900);
        --gx-card-ring: rgb(255 255 255 / 0.07);
        --gx-card-shadow: 0 1px 2px rgb(0 0 0 / 0.3), 0 8px 24px -10px rgb(0 0 0 / 0.5);
        --gx-card-shadow-hover: 0 2px 4px rgb(0 0 0 / 0.35), 0 18px 36px -12px rgb(0 0 0 / 0.65);
        --gx-chrome-bg: color-mix(in oklab, var(--gray-900) 82%, transparent);
        --gx-chrome-border: rgb(255 255 255 / 0.07);
    }

    /* ---------- Canvas ---------- */
    .fi-body:has(.fi-layout) {
        background:
            radial-gradient(60rem 26rem at 100% -8rem, color-mix(in oklab, var(--primary-500) 9%, transparent), transparent 70%),
            radial-gradient(50rem 30rem at 0% 100%, rgb(139 92 246 / 0.07), transparent 70%),
            radial-gradient(40rem 24rem at 60% 40%, rgb(14 165 233 / 0.05), transparent 70%),
            var(--gray-50);
        background-attachment: fixed;
    }

    .dark .fi-body:has(.fi-layout) {
        background:
            radial-gradient(60rem 26rem at 100% -8rem, color-mix(in oklab, var(--primary-500) 12%, transparent), transparent 70%),
            radial-gradient(50rem 30rem at 0% 100%, rgb(139 92 246 / 0.1), transparent 70%),
            var(--gray-950);
        background-attachment: fixed;
    }

    ::selection {
        background: color-mix(in oklab, var(--primary-500) 25%, transparent);
    }

    /* ---------- Topbar ---------- */
    .fi-topbar {
        background-color: var(--gx-chrome-bg);
        backdrop-filter: saturate(1.6) blur(14px);
        -webkit-backdrop-filter: saturate(1.6) blur(14px);
        box-shadow: 0 1px 0 var(--gx-chrome-border);
    }

    .fi-topbar .fi-logo {
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .fi-user-avatar {
        box-shadow: 0 0 0 2px var(--gx-card-bg), 0 0 0 4px color-mix(in oklab, var(--primary-500) 35%, transparent);
    }

    .gx-cache-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin-inline-end: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-700);
        background: var(--primary-50);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 25%, transparent);
        transition: background-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .gx-cache-btn:hover {
        background: var(--primary-100);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 45%, transparent);
    }

    .gx-cache-btn:active {
        transform: scale(0.97);
    }

    .gx-cache-btn .fi-icon {
        width: 1rem;
        height: 1rem;
        color: var(--primary-600);
        transition: transform 0.4s;
    }

    .gx-cache-btn:hover .fi-icon {
        transform: rotate(180deg);
    }

    .dark .gx-cache-btn {
        color: var(--primary-300);
        background: color-mix(in oklab, var(--primary-500) 12%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 30%, transparent);
    }

    .dark .gx-cache-btn .fi-icon {
        color: var(--primary-400);
    }

    @media (max-width: 639px) {
        .gx-cache-btn span {
            display: none;
        }

        .gx-cache-btn {
            padding: 0.5rem;
        }
    }

    /* ---------- Sidebar ---------- */
    .fi-sidebar {
        background-color: var(--gx-card-bg);
    }

    @media (min-width: 1024px) {
        .fi-sidebar {
            background-color: var(--gx-chrome-bg);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 1px 0 0 var(--gx-chrome-border);
        }
    }

    .fi-sidebar-nav {
        scrollbar-width: thin;
        scrollbar-color: color-mix(in oklab, var(--gray-400) 40%, transparent) transparent;
    }

    .fi-sidebar-group-label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--gray-400);
    }

    .dark .fi-sidebar-group-label {
        color: var(--gray-500);
    }

    .fi-sidebar-item-btn {
        border-radius: var(--gx-radius-lg);
        padding: 0.5625rem 0.75rem;
        transition: background-color 0.15s, color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .fi-sidebar-item-btn:hover {
        background-color: color-mix(in oklab, var(--primary-500) 7%, transparent);
    }

    .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: var(--primary-600);
    }

    .dark .fi-sidebar-item-btn:hover {
        background-color: rgb(255 255 255 / 0.05);
    }

    .dark .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: var(--primary-400);
    }

    .fi-sidebar-item-label {
        font-weight: 500;
    }

    /* Each navigation group gets its own icon colour, so sections are easy to tell apart at a glance. */
    .fi-sidebar-group { --gx-nav: var(--gray-400); }
    .fi-sidebar-group[data-group-label='Sales'] { --gx-nav: #f97316; }
    .fi-sidebar-group[data-group-label='Accounts'] { --gx-nav: #10b981; }
    .fi-sidebar-group[data-group-label='Catalog'] { --gx-nav: #3b82f6; }
    .fi-sidebar-group[data-group-label='Inventory'] { --gx-nav: #14b8a6; }
    .fi-sidebar-group[data-group-label='Marketing'] { --gx-nav: #d946ef; }
    .fi-sidebar-group[data-group-label='Customers'] { --gx-nav: #8b5cf6; }
    .fi-sidebar-group[data-group-label='Content'] { --gx-nav: #f59e0b; }
    .fi-sidebar-group[data-group-label='Settings'] { --gx-nav: #64748b; }
    .fi-sidebar-group[data-group-label='Access Control'] { --gx-nav: #e11d48; }

    .fi-sidebar-group .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-icon,
    .fi-sidebar-group .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-btn:hover .fi-sidebar-item-icon,
    .dark .fi-sidebar-group .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: var(--gx-nav);
    }

    .fi-sidebar-group .fi-sidebar-item:not(.fi-active) .fi-sidebar-item-btn:hover {
        background-color: color-mix(in oklab, var(--gx-nav) 10%, transparent);
    }

    .fi-sidebar-group .fi-sidebar-group-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .fi-sidebar-group .fi-sidebar-group-label::before {
        content: '';
        width: 0.4375rem;
        height: 0.4375rem;
        border-radius: 9999px;
        background: var(--gx-nav);
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background: linear-gradient(120deg, #ff5a36, var(--primary-600) 55%, #c026d3);
        box-shadow: 0 8px 18px -8px color-mix(in oklab, var(--primary-600) 75%, transparent);
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon {
        color: #fff;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-badge {
        color: var(--primary-700);
        background: rgb(255 255 255 / 0.92);
        box-shadow: none;
    }

    /* ---------- Page header ---------- */
    .fi-header-heading {
        font-weight: 700;
        letter-spacing: -0.025em;
    }

    .fi-header-subheading {
        color: var(--gray-500);
    }

    .fi-breadcrumbs-item-label {
        font-weight: 500;
    }

    /* ---------- Cards: sections, tables, stats ---------- */
    .fi-section:not(.fi-section-not-contained),
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat {
        border-radius: var(--gx-radius-xl);
        background-color: var(--gx-card-bg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow);
    }

    .fi-section:not(.fi-section-not-contained) .fi-section:not(.fi-section-not-contained) {
        box-shadow: 0 0 0 1px var(--gx-card-ring);
    }

    .fi-section-header-heading {
        font-weight: 600;
        letter-spacing: -0.01em;
    }

    .fi-wi-chart .fi-section {
        transition: box-shadow 0.2s;
    }

    .fi-wi-chart .fi-section:hover {
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow-hover);
    }

    /* ---------- Stat cards: solid gradient tiles, one hue per metric ---------- */
    .fi-wi-stats-overview-stat,
    .dark .fi-wi-stats-overview-stat {
        --gx-from: var(--primary-500);
        --gx-to: var(--primary-700);
        overflow: hidden;
        isolation: isolate;
        padding: 1.25rem 1.375rem 1.5rem;
        color: #fff;
        background:
            radial-gradient(18rem 10rem at 110% -20%, rgb(255 255 255 / 0.28), transparent 60%),
            linear-gradient(135deg, var(--gx-from), var(--gx-to));
        box-shadow:
            inset 0 0 0 1px rgb(255 255 255 / 0.14),
            0 14px 28px -16px color-mix(in oklab, var(--gx-to) 85%, transparent);
        transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s;
    }

    /* Two soft rings in the corner, like a speaker grille, so tiles don't read as flat swatches. */
    .fi-wi-stats-overview-stat::before {
        content: '';
        position: absolute;
        z-index: -1;
        right: -3.5rem;
        bottom: -4.5rem;
        width: 11rem;
        height: 11rem;
        border-radius: 9999px;
        box-shadow:
            0 0 0 1.5rem rgb(255 255 255 / 0.07),
            0 0 0 3rem rgb(255 255 255 / 0.04);
        pointer-events: none;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-3px);
        box-shadow:
            inset 0 0 0 1px rgb(255 255 255 / 0.2),
            0 22px 36px -16px color-mix(in oklab, var(--gx-to) 95%, transparent);
    }

    .fi-wi-stats-overview-stat[data-accent='orange'] { --gx-from: #ff7a45; --gx-to: #df0100; }
    .fi-wi-stats-overview-stat[data-accent='blue'] { --gx-from: #3b82f6; --gx-to: #6d28d9; }
    .fi-wi-stats-overview-stat[data-accent='amber'] { --gx-from: #fbbf24; --gx-to: #ea580c; }
    .fi-wi-stats-overview-stat[data-accent='emerald'] { --gx-from: #34d399; --gx-to: #047857; }
    .fi-wi-stats-overview-stat[data-accent='violet'] { --gx-from: #a855f7; --gx-to: #db2777; }
    .fi-wi-stats-overview-stat[data-accent='teal'] { --gx-from: #2dd4bf; --gx-to: #0369a1; }
    .fi-wi-stats-overview-stat[data-accent='rose'] { --gx-from: #fb7185; --gx-to: #be123c; }

    .fi-wi-stats-overview-stat-content {
        row-gap: 0.625rem;
    }

    .fi-wi-stats-overview-stat-label-ctn {
        flex-direction: row-reverse;
        justify-content: space-between;
        align-items: flex-start;
    }

    .fi-wi-stats-overview-stat-label-ctn > .fi-icon {
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        padding: 0.6875rem;
        border-radius: 0.875rem;
        color: #fff;
        background: rgb(255 255 255 / 0.2);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.25);
        backdrop-filter: blur(4px);
    }

    .fi-wi-stats-overview-stat-label,
    .dark .fi-wi-stats-overview-stat-label {
        padding-top: 0.25rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: rgb(255 255 255 / 0.85);
    }

    .fi-wi-stats-overview-stat-value,
    .dark .fi-wi-stats-overview-stat-value {
        margin-top: -0.5rem;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #fff;
        text-shadow: 0 1px 2px rgb(0 0 0 / 0.12);
    }

    /* The trend colour (green/red) is carried by the arrow icon in a white chip, which stays legible on every hue. */
    .fi-wi-stats-overview-stat-description,
    .dark .fi-wi-stats-overview-stat-description {
        display: inline-flex;
        align-self: flex-start;
        align-items: center;
        gap: 0.375rem;
        width: fit-content;
        max-width: 100%;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
        background: rgb(0 0 0 / 0.14);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.14);
    }

    .fi-wi-stats-overview-stat-description .fi-icon,
    .dark .fi-wi-stats-overview-stat-description .fi-icon {
        color: #fff;
    }

    .fi-wi-stats-overview-stat-chart-border-color {
        color: rgb(255 255 255 / 0.9);
    }

    .fi-wi-stats-overview-stat-chart-bg-color {
        color: rgb(255 255 255 / 0.18);
    }

    .fi-wi-stats-overview-stat:focus-visible {
        outline: 3px solid color-mix(in oklab, var(--gx-to) 60%, #fff);
        outline-offset: 3px;
    }

    /* ---------- Tables ---------- */
    .fi-ta-ctn {
        overflow: hidden;
    }

    .fi-ta-header-cell {
        background-color: color-mix(in oklab, var(--gray-50) 70%, transparent);
    }

    .dark .fi-ta-header-cell {
        background-color: rgb(255 255 255 / 0.02);
    }

    .fi-ta-header-cell,
    .fi-ta-header-cell-sort-btn {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--gray-500);
    }

    .fi-ta-table > tbody > .fi-ta-row {
        transition: background-color 0.12s;
    }

    .fi-ta-table > tbody > .fi-ta-row:hover {
        background-color: color-mix(in oklab, var(--primary-500) 4%, transparent);
    }

    .fi-ta-image img {
        border-radius: 0.5rem;
    }

    /* Product thumbnails: contained on a white tile so transparent PNGs read cleanly. */
    .fi-ta-image img.gx-thumb {
        object-fit: contain;
        padding: 0.25rem;
        border-radius: 0.75rem;
        background-color: #fff;
        box-shadow: 0 0 0 1px var(--gx-card-ring);
        transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .fi-ta-row:hover img.gx-thumb {
        transform: scale(1.06);
    }

    .gx-price-was {
        color: var(--gray-400);
        font-size: 0.75rem;
    }

    .gx-price-off {
        display: inline-block;
        margin-inline-start: 0.25rem;
        padding: 0 0.375rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        color: var(--success-700);
        background: color-mix(in oklab, var(--success-500) 12%, transparent);
    }

    .dark .gx-price-off {
        color: var(--success-400);
    }

    .gx-cell-title {
        display: block;
        font-weight: 600;
    }

    .gx-cell-meta {
        display: block;
        margin-top: 0.125rem;
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    /* Service highlights: an icon tile beside the badge text, as in the storefront strip. */
    .gx-hl-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        text-align: start;
    }

    .gx-hl-icon {
        display: grid;
        flex-shrink: 0;
        place-items: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.625rem;
        color: var(--primary-600);
        background: color-mix(in oklab, var(--primary-500) 10%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 14%, transparent);
    }

    .dark .gx-hl-icon {
        color: var(--primary-400);
    }

    .gx-hl-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .gx-hl-preview-strip {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        border-radius: 0.75rem;
        background-color: #fff;
        box-shadow: 0 0 0 1px var(--gx-card-ring), 0 1px 2px rgb(15 23 42 / 0.05);
    }

    .dark .gx-hl-preview-strip {
        background-color: rgb(255 255 255 / 0.03);
    }

    .gx-hl-preview-note {
        margin-top: 0.625rem;
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .gx-hl-warning {
        display: inline-block;
        margin-top: 0.375rem;
        padding: 0.25rem 0.625rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--warning-700);
        background: color-mix(in oklab, var(--warning-500) 12%, transparent);
    }

    .dark .gx-hl-warning {
        color: var(--warning-400);
    }

    /* Home sections list: a position chip, an icon tile tinted like the type badge, and hidden blocks faded. */
    .gx-hs-position {
        display: inline-grid;
        place-items: center;
        min-width: 2rem;
        height: 2rem;
        padding-inline: 0.375rem;
        border-radius: 0.625rem;
        font-size: 0.875rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--gray-700);
        background: var(--gray-100);
        box-shadow: inset 0 0 0 1px var(--gx-card-ring);
    }

    .dark .gx-hs-position {
        color: var(--gray-200);
        background: rgb(255 255 255 / 0.06);
    }

    .gx-hs-block {
        display: inline-flex;
        align-items: center;
        gap: 0.875rem;
        text-align: start;
    }

    .gx-hs-icon {
        display: grid;
        flex-shrink: 0;
        place-items: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        color: var(--color-600);
        background: color-mix(in oklab, var(--color-500) 12%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--color-500) 18%, transparent);
    }

    .dark .gx-hs-icon {
        color: var(--color-400);
    }

    .gx-hs-icon svg {
        width: 1.375rem;
        height: 1.375rem;
    }

    .gx-hs-heading {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 0.5rem;
    }

    .gx-hs-block .gx-cell-meta {
        font-size: 0.8125rem;
    }

    .gx-hs-hidden-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.0625rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--gray-600);
        background: var(--gray-100);
        box-shadow: inset 0 0 0 1px var(--gx-card-ring);
    }

    .dark .gx-hs-hidden-tag {
        color: var(--gray-300);
        background: rgb(255 255 255 / 0.06);
    }

    .gx-hs-row-hidden .gx-hs-position,
    .gx-hs-row-hidden .gx-hs-icon,
    .gx-hs-row-hidden .fi-ta-text .fi-badge {
        filter: grayscale(1);
        opacity: 0.55;
    }

    .gx-hs-row-hidden .gx-cell-title,
    .gx-hs-row-hidden .gx-cell-meta {
        opacity: 0.6;
    }

    /* Move up / down: dark arrows that turn brand-red on hover; a spent direction fades out. */
    .gx-hs-move .fi-icon {
        color: var(--gray-600);
        transition: color 0.15s;
    }

    .dark .gx-hs-move .fi-icon {
        color: var(--gray-300);
    }

    .gx-hs-move:not(.fi-disabled):hover .fi-icon {
        color: var(--primary-600);
    }

    .gx-hs-move.fi-disabled {
        cursor: not-allowed;
    }

    .gx-hs-move.fi-disabled .fi-icon {
        color: var(--gray-300);
    }

    .dark .gx-hs-move.fi-disabled .fi-icon {
        color: var(--gray-600);
    }

    /* Orders list: until an order is booked, its courier cell is a "Send to courier" button. */
    .gx-courier-send .fi-badge {
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--success-700);
        background-color: var(--gx-card-bg);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--success-600) 35%, transparent), 0 1px 2px rgb(15 23 42 / 0.06);
        transition: background-color 0.15s, color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .gx-courier-send .fi-badge .fi-icon {
        color: var(--success-600);
        transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .dark .gx-courier-send .fi-badge {
        color: var(--success-400);
        background-color: color-mix(in oklab, var(--success-500) 8%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--success-500) 35%, transparent);
    }

    .dark .gx-courier-send .fi-badge .fi-icon {
        color: var(--success-400);
    }

    .fi-ta-col:hover .gx-courier-send .fi-badge {
        color: #fff;
        background-color: var(--success-600);
        box-shadow: inset 0 0 0 1px var(--success-600), 0 6px 14px -6px color-mix(in oklab, var(--success-600) 70%, transparent);
    }

    .fi-ta-col:hover .gx-courier-send .fi-badge .fi-icon {
        color: #fff;
        transform: translate(2px, -2px);
    }

    .fi-ta-col:active .gx-courier-send .fi-badge {
        transform: scale(0.97);
    }

    .fi-ta-col:has(.gx-courier-send):focus-visible {
        outline: none;
    }

    .fi-ta-col:focus-visible .gx-courier-send .fi-badge {
        outline: 2px solid var(--success-500);
        outline-offset: 2px;
    }

    /* List page tabs (Products / Orders): a pill bar on a card. */
    .fi-resource-list-records-page .fi-tabs {
        gap: 0.25rem;
        padding: 0.375rem;
        border-radius: var(--gx-radius-xl);
        background-color: var(--gx-card-bg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow);
        scrollbar-width: none;
    }

    .fi-resource-list-records-page .fi-tabs-item {
        flex-shrink: 0;
        border-radius: 0.625rem;
        transition: background-color 0.15s, color 0.15s;
    }

    .fi-resource-list-records-page .fi-tabs-item:hover {
        background-color: color-mix(in oklab, var(--primary-500) 6%, transparent);
    }

    .fi-resource-list-records-page .fi-tabs-item.fi-active,
    .dark .fi-resource-list-records-page .fi-tabs-item.fi-active {
        background-color: color-mix(in oklab, var(--primary-500) 10%, transparent);
    }

    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-tabs-item-label,
    .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-icon {
        color: var(--primary-600);
        font-weight: 600;
    }

    .dark .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-tabs-item-label,
    .dark .fi-resource-list-records-page .fi-tabs-item.fi-active .fi-icon {
        color: var(--primary-400);
    }

    .fi-ta-empty-state-icon-bg {
        background: color-mix(in oklab, var(--primary-500) 12%, transparent);
    }

    .fi-ta-empty-state-icon-bg .fi-icon {
        color: var(--primary-600);
    }

    /* ---------- Forms & controls ---------- */
    .fi-btn {
        border-radius: 0.625rem;
        font-weight: 600;
        transition: background-color 0.15s, box-shadow 0.15s, transform 0.1s, filter 0.15s;
    }

    .fi-btn:active {
        transform: translateY(1px);
    }

    .fi-btn.fi-color-primary:not(.fi-outlined) {
        background-image: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.08), 0 6px 14px -6px color-mix(in oklab, var(--primary-600) 65%, transparent);
    }

    .fi-btn.fi-color-primary:not(.fi-outlined):hover {
        filter: brightness(1.06);
    }

    .fi-input-wrp {
        border-radius: 0.625rem;
        transition: box-shadow 0.15s;
    }

    .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 4px color-mix(in oklab, var(--primary-500) 14%, transparent);
    }

    .fi-fo-field-label-content {
        font-weight: 600;
    }

    .fi-badge {
        border-radius: 9999px;
        font-weight: 600;
    }

    .fi-tabs {
        border-radius: var(--gx-radius-lg);
    }

    .fi-dropdown-panel {
        border-radius: var(--gx-radius-lg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), 0 18px 40px -12px rgb(15 23 42 / 0.25);
    }

    .fi-modal-window {
        border-radius: 1.25rem;
    }

    /* ---------- Dashboard welcome banner ---------- */
    .gx-welcome {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.5rem;
        border-radius: 1.25rem;
        color: #fff;
        background:
            radial-gradient(30rem 20rem at 100% 0%, rgb(251 146 60 / 0.55), transparent 60%),
            radial-gradient(28rem 18rem at 0% 120%, rgb(56 189 248 / 0.35), transparent 60%),
            linear-gradient(120deg, #7a0000 0%, #c0001a 30%, #a3168f 65%, #4338ca 100%);
        box-shadow: 0 0 0 1px rgb(255 255 255 / 0.08), 0 22px 44px -20px rgb(163 22 143 / 0.65);
    }

    .gx-welcome-glow {
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image:
            linear-gradient(rgb(255 255 255 / 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgb(255 255 255 / 0.04) 1px, transparent 1px);
        background-size: 2rem 2rem;
        mask-image: linear-gradient(90deg, transparent, #000 70%);
        -webkit-mask-image: linear-gradient(90deg, transparent, #000 70%);
    }

    .gx-welcome-date {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(255 255 255 / 0.9);
        background: rgb(255 255 255 / 0.14);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.18);
    }

    .gx-welcome-date .fi-icon {
        width: 0.875rem;
        height: 0.875rem;
        color: #fde68a;
    }

    .gx-welcome-title {
        margin-top: 0.875rem;
        font-size: clamp(1.375rem, 2.4vw, 1.875rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.025em;
    }

    .gx-welcome-title span {
        background: linear-gradient(90deg, #fde68a, #fdba74);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .gx-welcome-text {
        margin-top: 0.375rem;
        font-size: 0.9375rem;
        color: rgb(255 255 255 / 0.8);
    }

    .gx-welcome-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .gx-welcome-action {
        display: inline-flex;
        align-items: center;
        gap: 0.4375rem;
        padding: 0.5625rem 0.875rem;
        border-radius: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #fff;
        background: rgb(255 255 255 / 0.08);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.12);
        backdrop-filter: blur(6px);
        transition: background-color 0.15s, transform 0.15s, box-shadow 0.15s;
    }

    .gx-welcome-action:hover {
        background: rgb(255 255 255 / 0.15);
        transform: translateY(-1px);
    }

    .gx-welcome-action .fi-icon {
        width: 1rem;
        height: 1rem;
        opacity: 0.85;
    }

    .gx-welcome-action-primary {
        color: #9f0012;
        background: #fff;
        box-shadow: 0 8px 20px -8px rgb(0 0 0 / 0.45);
    }

    .gx-welcome-action-primary:hover {
        background: #fff7ed;
    }

    .gx-welcome-action-primary .fi-icon {
        opacity: 1;
    }

    .gx-welcome-action:focus-visible,
    .gx-cache-btn:focus-visible {
        outline: 2px solid var(--primary-400);
        outline-offset: 2px;
    }

    @media (min-width: 1024px) {
        .gx-welcome {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 2.25rem;
        }

        .gx-welcome-actions {
            justify-content: flex-end;
            max-width: 32rem;
        }
    }

    /* ---------- Cache manager page ---------- */
    .gx-tone-info { --gx-tone: var(--info-500); }
    .gx-tone-success { --gx-tone: var(--success-500); }
    .gx-tone-warning { --gx-tone: var(--warning-500); }
    .gx-tone-primary { --gx-tone: var(--primary-500); }
    .gx-tone-gray { --gx-tone: var(--gray-500); }

    .gx-cache {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .gx-cache-stats,
    .gx-cache-targets {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr));
        gap: 1rem;
    }

    .gx-cache-targets {
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));
    }

    .gx-cache-stat,
    .gx-cache-target {
        border-radius: var(--gx-radius-lg);
        background-color: var(--gx-card-bg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow);
    }

    .gx-cache-stat {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.125rem 1.25rem;
    }

    .gx-cache-stat-icon,
    .gx-cache-target-icon {
        display: grid;
        flex-shrink: 0;
        place-items: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.625rem;
        color: var(--gx-tone);
        background: color-mix(in oklab, var(--gx-tone) 12%, transparent);
    }

    .gx-cache-stat-icon .fi-icon,
    .gx-cache-target-icon .fi-icon {
        width: 1.25rem;
        height: 1.25rem;
    }

    .gx-cache-stat-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--gray-500);
    }

    .gx-cache-stat-value {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--gray-950);
    }

    .gx-cache-stat-value-sm {
        font-size: 0.875rem;
        font-weight: 600;
    }

    .gx-cache-stat-value-ok {
        color: var(--success-600);
    }

    .dark .gx-cache-stat-value {
        color: #fff;
    }

    .dark .gx-cache-stat-value-ok {
        color: var(--success-400);
    }

    .gx-cache-hero {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: var(--gx-radius-xl);
        background: linear-gradient(90deg, color-mix(in oklab, var(--primary-500) 10%, var(--gx-card-bg)), var(--gx-card-bg) 75%);
        box-shadow: 0 0 0 1px color-mix(in oklab, var(--primary-500) 22%, transparent), var(--gx-card-shadow);
    }

    .gx-cache-hero-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-950);
    }

    .gx-cache-hero-text {
        margin-top: 0.25rem;
        max-width: 42rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .dark .gx-cache-hero-title {
        color: #fff;
    }

    .dark .gx-cache-hero-text {
        color: var(--gray-400);
    }

    .gx-cache-hero-button {
        flex-shrink: 0;
    }

    .gx-cache-target {
        display: flex;
        flex-direction: column;
        padding: 1.25rem;
    }

    .gx-cache-target-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .gx-cache-target-title {
        flex: 1;
        min-width: 0;
        font-weight: 600;
        color: var(--gray-950);
    }

    .dark .gx-cache-target-title {
        color: #fff;
    }

    .gx-cache-target-tag {
        flex-shrink: 0;
        padding: 0.125rem 0.5rem;
        border-radius: 0.375rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--gx-tone);
        background: color-mix(in oklab, var(--gx-tone) 12%, transparent);
    }

    .gx-cache-target-text {
        flex: 1;
        margin-top: 0.75rem;
        font-size: 0.875rem;
        color: var(--gray-500);
    }

    .dark .gx-cache-target-text {
        color: var(--gray-400);
    }

    .gx-cache-target-footer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gx-card-ring);
    }

    .gx-cache-target-button {
        width: 100%;
    }

    @media (min-width: 640px) {
        .gx-cache-hero {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 1.75rem;
        }
    }

    /* ---------- Settings page ---------- */
    .gx-settings-tabs.fi-sc-tabs {
        flex-direction: column;
        gap: 1rem;
    }

    .gx-settings-tabs > .fi-tabs {
        flex-direction: row;
        gap: 0.25rem;
        padding: 0.375rem;
        overflow-x: auto;
        scrollbar-width: none;
        border-radius: var(--gx-radius-xl);
        background-color: var(--gx-card-bg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow);
    }

    .gx-settings-tabs > .fi-sc-tabs-tab.fi-active {
        margin: 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .gx-settings-tabs .fi-tabs-item {
        position: relative;
        flex-shrink: 0;
        padding: 0.625rem 0.875rem;
        border-radius: 0.625rem;
        color: var(--gray-600);
        transition: background-color 0.15s, color 0.15s;
    }

    .dark .gx-settings-tabs .fi-tabs-item {
        color: var(--gray-400);
    }

    .gx-settings-tabs .fi-tabs-item .fi-icon {
        color: var(--gray-400);
        transition: color 0.15s;
    }

    .gx-settings-tabs .fi-tabs-item:hover .fi-icon {
        color: var(--primary-500);
    }

    .gx-settings-tabs .fi-tabs-item.fi-active,
    .dark .gx-settings-tabs .fi-tabs-item.fi-active {
        background-color: color-mix(in oklab, var(--primary-500) 9%, transparent);
    }

    .gx-settings-tabs .fi-tabs-item.fi-active .fi-tabs-item-label,
    .gx-settings-tabs .fi-tabs-item.fi-active .fi-icon {
        color: var(--primary-600);
        font-weight: 600;
    }

    .dark .gx-settings-tabs .fi-tabs-item.fi-active .fi-tabs-item-label,
    .dark .gx-settings-tabs .fi-tabs-item.fi-active .fi-icon {
        color: var(--primary-400);
    }

    @media (min-width: 1024px) {
        .gx-settings-tabs.fi-sc-tabs {
            flex-direction: row;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .gx-settings-tabs > .fi-tabs {
            position: sticky;
            top: 5.5rem;
            flex-direction: column;
            flex-shrink: 0;
            width: 15rem;
            padding: 0.5rem;
            overflow: visible;
        }

        .gx-settings-tabs .fi-tabs-item {
            justify-content: flex-start;
        }

        .gx-settings-tabs .fi-tabs-item.fi-active::before {
            content: '';
            position: absolute;
            inset: 0.5rem auto 0.5rem -0.5rem;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: var(--primary-500);
        }

        .gx-settings-tabs > .fi-sc-tabs-tab.fi-active {
            flex: 1;
        }
    }

    .gx-settings .fi-section-header > .fi-icon {
        width: 2.5rem;
        height: 2.5rem;
        margin-top: 0;
        padding: 0.5625rem;
        border-radius: 0.75rem;
        color: var(--primary-600);
        background: color-mix(in oklab, var(--primary-500) 10%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 16%, transparent);
    }

    .dark .gx-settings .fi-section-header > .fi-icon {
        color: var(--primary-400);
    }

    .gx-settings .fi-section-header-description {
        margin-top: 0.125rem;
    }

    /* Logo uploads: dashed drop zone, checkerboard behind transparent images. */
    .gx-upload .filepond--root {
        min-height: 9.5rem;
        background-color: color-mix(in oklab, var(--gray-50) 70%, transparent);
        box-shadow: none;
        outline: 1.5px dashed color-mix(in oklab, var(--gray-400) 55%, transparent);
        outline-offset: -1.5px;
        transition: outline-color 0.15s, background-color 0.15s;
    }

    .dark .gx-upload .filepond--root {
        background-color: rgb(255 255 255 / 0.03);
        outline-color: rgb(255 255 255 / 0.18);
    }

    .gx-upload .filepond--root:hover,
    .gx-upload .filepond--root[data-hopper-state='drag-over'] {
        outline-color: var(--primary-500);
        background-color: color-mix(in oklab, var(--primary-500) 5%, transparent);
    }

    .gx-upload .filepond--drop-label {
        min-height: 9.5rem;
    }

    .gx-upload .filepond--drop-label label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.625rem;
        font-size: 0.8125rem;
        color: var(--gray-500);
        cursor: pointer;
    }

    .gx-upload .filepond--drop-label label::before {
        content: '';
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        background:
            no-repeat center / 1.25rem
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.6' stroke='%23df0100'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5'/%3E%3C/svg%3E"),
            color-mix(in oklab, var(--primary-500) 10%, transparent);
    }

    .gx-upload .filepond--label-action {
        font-weight: 600;
        color: var(--primary-600);
        text-decoration: none;
    }

    .gx-upload .filepond--item-panel {
        background-color: var(--gray-700);
    }

    .gx-upload .filepond--image-preview {
        background-color: #fff;
        background-image:
            linear-gradient(45deg, var(--gray-100) 25%, transparent 25%),
            linear-gradient(-45deg, var(--gray-100) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, var(--gray-100) 75%),
            linear-gradient(-45deg, transparent 75%, var(--gray-100) 75%);
        background-size: 16px 16px;
        background-position: 0 0, 0 8px, 8px -8px, -8px 0;
    }

    .gx-upload-dark .filepond--image-preview {
        background-color: #0f172a;
        background-image: none;
    }

    .gx-upload .filepond--image-preview-overlay-idle {
        color: rgb(15 23 42 / 0.55);
    }

    /* Sticky save bar */
    .gx-settings-savebar {
        position: sticky;
        bottom: 1rem;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding: 0.75rem;
        border-radius: var(--gx-radius-xl);
        background-color: var(--gx-chrome-bg);
        backdrop-filter: saturate(1.6) blur(14px);
        -webkit-backdrop-filter: saturate(1.6) blur(14px);
        box-shadow: 0 0 0 1px var(--gx-card-ring), 0 12px 32px -12px rgb(15 23 42 / 0.25);
    }

    .gx-settings-savebar-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        color: var(--gray-500);
    }

    .dark .gx-settings-savebar-text {
        color: var(--gray-400);
    }

    .gx-settings-savebar-icon {
        flex-shrink: 0;
        width: 1.125rem;
        height: 1.125rem;
        color: var(--gray-400);
    }

    @media (min-width: 640px) {
        .gx-settings-savebar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0.75rem 0.75rem 1.25rem;
        }
    }

    @media (min-width: 1024px) {
        .gx-settings-savebar {
            margin-inline-start: 16.5rem;
        }
    }

    /* ---------- Product editor ---------- */
    .gx-product-form .gx-upload .filepond--root,
    .gx-product-form .gx-upload .filepond--drop-label {
        min-height: 11rem;
    }

    /* ---------- Product editor: combo ---------- */
    .gx-combo .fi-section-header > .fi-icon {
        width: 2.5rem;
        height: 2.5rem;
        margin-top: 0;
        padding: 0.5625rem;
        border-radius: 0.75rem;
        color: var(--primary-600);
        background: color-mix(in oklab, var(--primary-500) 10%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--primary-500) 16%, transparent);
    }

    .dark .gx-combo .fi-section-header > .fi-icon {
        color: var(--primary-400);
    }

    .gx-combo-empty {
        display: grid;
        gap: 0.25rem;
        padding: 1rem 1.125rem;
        border-radius: var(--gx-radius-lg);
        background-color: color-mix(in oklab, var(--gray-50) 70%, transparent);
        outline: 1.5px dashed color-mix(in oklab, var(--gray-400) 50%, transparent);
        outline-offset: -1.5px;
    }

    .dark .gx-combo-empty {
        background-color: rgb(255 255 255 / 0.03);
    }

    .gx-combo-empty-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-950);
    }

    .dark .gx-combo-empty-title {
        color: var(--gray-50);
    }

    .gx-combo-empty-text {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: var(--gray-500);
    }

    .dark .gx-combo-empty-text {
        color: var(--gray-400);
    }

    .gx-combo-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .gx-combo-stat {
        display: grid;
        gap: 0.125rem;
        padding: 0.75rem 1rem;
        border-radius: var(--gx-radius-lg);
        background-color: color-mix(in oklab, var(--gray-50) 80%, transparent);
        box-shadow: inset 0 0 0 1px var(--gx-card-ring);
    }

    .dark .gx-combo-stat {
        background-color: rgb(255 255 255 / 0.03);
    }

    .gx-combo-stat-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--gray-500);
    }

    .dark .gx-combo-stat-label {
        color: var(--gray-400);
    }

    .gx-combo-stat-value {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        font-variant-numeric: tabular-nums;
        color: var(--gray-950);
    }

    .dark .gx-combo-stat-value {
        color: var(--gray-50);
    }

    .gx-combo-stat-muted .gx-combo-stat-value {
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--gray-400);
    }

    .gx-combo-stat-good {
        background-color: color-mix(in oklab, var(--success-500) 10%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--success-600) 30%, transparent);
    }

    .gx-combo-stat-good .gx-combo-stat-label,
    .gx-combo-stat-good .gx-combo-stat-value {
        color: var(--success-700);
    }

    .dark .gx-combo-stat-good .gx-combo-stat-label,
    .dark .gx-combo-stat-good .gx-combo-stat-value {
        color: var(--success-400);
    }

    .gx-combo-stat-bad {
        background-color: color-mix(in oklab, var(--danger-500) 8%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--danger-600) 30%, transparent);
    }

    .gx-combo-stat-bad .gx-combo-stat-value {
        color: var(--danger-600);
    }

    .dark .gx-combo-stat-bad .gx-combo-stat-value {
        color: var(--danger-400);
    }

    .gx-combo-note {
        grid-column: 1 / -1;
        font-size: 0.8125rem;
        color: var(--gray-500);
    }

    .gx-combo-note-bad {
        color: var(--danger-600);
    }

    .dark .gx-combo-note-bad {
        color: var(--danger-400);
    }

    @media (max-width: 40rem) {
        .gx-combo-summary {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    /* ---------- Banner editor ---------- */
    .gx-banner-form .gx-upload .filepond--root,
    .gx-banner-form .gx-upload .filepond--drop-label {
        min-height: 13rem;
    }

    /* Banner artwork has no transparency to show, so preview it on a plain tile. */
    .gx-banner-form .gx-upload .filepond--image-preview {
        background-image: none;
        background-color: var(--gray-100);
    }

    .dark .gx-banner-form .gx-upload .filepond--image-preview {
        background-color: var(--gray-800);
    }

    /* Page editor: a large title and a writing area tall enough for a full policy. */
    .gx-page-form .gx-page-title {
        font-size: 1.125rem;
        font-weight: 600;
    }

    .gx-page-form .fi-fo-rich-editor .tiptap {
        font-size: 0.9375rem;
        line-height: 1.7;
    }

    .gx-page-form .fi-fo-rich-editor-toolbar {
        background-color: var(--gray-50);
    }

    .dark .gx-page-form .fi-fo-rich-editor-toolbar {
        background-color: var(--gray-900);
    }

    /* Choice cards: radio options (home block type, product source, panel colour) as tappable cards. */
    .fi-fo-radio.gx-choice-cards {
        gap: 0.75rem;
    }

    .gx-choice-cards > .fi-fo-radio-label {
        align-self: stretch;
        padding: 0.875rem 1rem;
        border-radius: var(--gx-radius-lg);
        background-color: var(--gx-card-bg);
        box-shadow: 0 0 0 1px var(--gx-card-ring), 0 1px 2px rgb(15 23 42 / 0.04);
        cursor: pointer;
        transition: box-shadow 0.15s, background-color 0.15s;
    }

    .gx-choice-cards > .fi-fo-radio-label:hover {
        box-shadow: 0 0 0 1px color-mix(in oklab, var(--primary-500) 45%, transparent), 0 6px 16px -10px rgb(15 23 42 / 0.25);
    }

    .gx-choice-cards > .fi-fo-radio-label:has(.fi-radio-input:checked) {
        background-color: color-mix(in oklab, var(--primary-500) 6%, var(--gx-card-bg));
        box-shadow: 0 0 0 2px var(--primary-500);
    }

    .gx-choice-cards > .fi-fo-radio-label > .fi-fo-radio-label-text {
        align-content: start;
        font-weight: 600;
    }

    .gx-choice-cards .fi-fo-radio-label-description {
        margin-top: 0.125rem;
        font-size: 0.8125rem;
        line-height: 1.35;
    }

    /* Panel colour: a swatch of the storefront gradient on each card. */
    .gx-theme-cards > .fi-fo-radio-label::after {
        content: '';
        flex-shrink: 0;
        width: 3.5rem;
        height: 2.5rem;
        margin-left: auto;
        border-radius: 0.5rem;
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.12);
    }

    .gx-theme-cards > .fi-fo-radio-label:has(.fi-radio-input[value="aurora"])::after {
        background: linear-gradient(135deg, #5c0000, #a30000 55%, #df0100);
    }

    .gx-theme-cards > .fi-fo-radio-label:has(.fi-radio-input[value="midnight"])::after {
        background: radial-gradient(120% 120% at 0% 100%, rgb(234 88 12 / 0.85), rgb(67 20 7 / 0.4) 50%, transparent 75%), #000;
    }

    /* Product picker: the product photo beside its name, in the dropdown and on the picked row. */
    .gx-product-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .gx-product-option > img {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        padding: 0.125rem;
        object-fit: contain;
        border-radius: 0.5rem;
        background-color: #fff;
        box-shadow: 0 0 0 1px var(--gx-card-ring);
    }

    .gx-product-option > span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fi-select-input-value-label .gx-product-option > img {
        width: 1.75rem;
        height: 1.75rem;
    }

    /* Sticky Save / Cancel bar on create and edit pages, matching the settings save bar. */
    .fi-sticky .fi-ac {
        background-color: var(--gx-chrome-bg);
        backdrop-filter: saturate(1.6) blur(14px);
        -webkit-backdrop-filter: saturate(1.6) blur(14px);
        border-radius: var(--gx-radius-xl);
        box-shadow: 0 0 0 1px var(--gx-card-ring), 0 12px 32px -12px rgb(15 23 42 / 0.25);
    }

    /* ---------- Order detail ---------- */
    .gx-order-totals {
        margin-top: 1rem;
        margin-inline-start: auto;
        max-width: 22rem;
        padding: 1rem 1.125rem;
        border-radius: var(--gx-radius-lg);
        background-color: color-mix(in oklab, var(--gray-50) 80%, transparent);
        box-shadow: inset 0 0 0 1px var(--gx-card-ring);
    }

    .dark .gx-order-totals {
        background-color: rgb(255 255 255 / 0.03);
    }

    .gx-order-totals .fi-in-text {
        justify-content: flex-end;
        text-align: end;
    }

    .gx-timeline {
        position: relative;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .gx-timeline-item {
        --gx-dot: var(--gray-400);
        position: relative;
        padding: 0 0 1.25rem 1.75rem;
    }

    .gx-timeline-item::before {
        content: '';
        position: absolute;
        top: 1.25rem;
        bottom: 0;
        left: 0.4375rem;
        width: 2px;
        background: var(--gx-card-ring);
    }

    .gx-timeline-item:last-child {
        padding-bottom: 0;
    }

    .gx-timeline-item:last-child::before {
        display: none;
    }

    .gx-timeline-item::after {
        content: '';
        position: absolute;
        top: 0.25rem;
        left: 0;
        width: 1rem;
        height: 1rem;
        border-radius: 9999px;
        background: var(--gx-card-bg);
        box-shadow: inset 0 0 0 4px var(--gx-dot);
    }

    .gx-timeline-item:first-child::after {
        box-shadow: inset 0 0 0 4px var(--gx-dot), 0 0 0 4px color-mix(in oklab, var(--gx-dot) 18%, transparent);
    }

    .gx-timeline-item[data-color='warning'] { --gx-dot: var(--warning-500); }
    .gx-timeline-item[data-color='info'] { --gx-dot: var(--info-500); }
    .gx-timeline-item[data-color='primary'] { --gx-dot: var(--primary-500); }
    .gx-timeline-item[data-color='success'] { --gx-dot: var(--success-500); }
    .gx-timeline-item[data-color='danger'] { --gx-dot: var(--danger-500); }

    .gx-timeline-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .gx-timeline-kind {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--gray-500);
    }

    .gx-timeline-arrow {
        width: 1rem;
        height: 1rem;
        color: var(--gray-400);
    }

    .gx-timeline-note {
        margin-top: 0.375rem;
        font-size: 0.875rem;
        color: var(--gray-700);
    }

    .dark .gx-timeline-note {
        color: var(--gray-300);
    }

    .gx-timeline-meta {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    /* ---------- Finance report: profit & loss statement ---------- */
    .gx-pl {
        display: flex;
        flex-direction: column;
        font-size: 0.875rem;
    }

    .gx-pl-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0;
        color: var(--gray-700);
    }

    .dark .gx-pl-row {
        color: var(--gray-300);
    }

    .gx-pl-amount {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .gx-pl-line {
        padding-inline-start: 0.75rem;
    }

    .gx-pl-negative .gx-pl-amount {
        color: var(--danger-600);
    }

    .gx-pl-positive .gx-pl-amount {
        color: var(--success-600);
    }

    .dark .gx-pl-negative .gx-pl-amount {
        color: var(--danger-400);
    }

    .dark .gx-pl-positive .gx-pl-amount {
        color: var(--success-400);
    }

    .gx-pl-subtotal {
        border-top: 1px dashed var(--gx-card-ring);
        font-weight: 600;
        color: var(--gray-950);
    }

    .dark .gx-pl-subtotal {
        color: #fff;
    }

    .gx-pl-total {
        margin-top: 0.5rem;
        padding: 0.75rem;
        border-radius: var(--gx-radius-lg);
        background: color-mix(in oklab, var(--gray-100) 70%, transparent);
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-950);
    }

    .dark .gx-pl-total {
        background: color-mix(in oklab, var(--gray-800) 70%, transparent);
        color: #fff;
    }

    .gx-pl-heading {
        margin-top: 0.875rem;
        padding-bottom: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--gray-500);
    }

    .gx-pl > .gx-pl-heading:first-child {
        margin-top: 0;
    }

    .gx-pl-empty,
    .gx-pl-note {
        padding: 0.25rem 0 0.25rem 0.75rem;
        font-size: 0.8125rem;
        color: var(--gray-500);
    }

    .gx-pl-note {
        padding-inline-start: 0;
    }

    .gx-pl-warning {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.625rem 0.75rem;
        border-radius: var(--gx-radius-lg);
        background: color-mix(in oklab, var(--warning-500) 12%, transparent);
        font-size: 0.8125rem;
        color: var(--warning-700);
    }

    .dark .gx-pl-warning {
        color: var(--warning-300);
    }

    .gx-pl-warning .fi-icon {
        flex-shrink: 0;
        width: 1.125rem;
        height: 1.125rem;
    }

    .gx-pl-aside {
        margin-top: 1.25rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--gx-card-ring);
    }

    /* ---------- Motion ---------- */
    .fi-page .fi-wi-widget {
        animation: gx-rise 0.45s cubic-bezier(0.22, 1, 0.36, 1) backwards;
    }

    @keyframes gx-rise {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .fi-page .fi-wi-widget,
        .gx-cache-btn .fi-icon,
        .gx-courier-send .fi-badge,
        .gx-courier-send .fi-badge .fi-icon {
            animation: none;
            transition: none;
        }

        .fi-ta-col:hover .gx-courier-send .fi-badge .fi-icon,
        .fi-ta-col:active .gx-courier-send .fi-badge {
            transform: none;
        }

        .fi-wi-stats-overview-stat:hover,
        .gx-welcome-action:hover {
            transform: none;
        }
    }
</style>
