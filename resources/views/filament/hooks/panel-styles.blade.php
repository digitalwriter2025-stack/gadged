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
            radial-gradient(70rem 26rem at 100% -8rem, color-mix(in oklab, var(--primary-500) 7%, transparent), transparent 70%),
            var(--gray-50);
        background-attachment: fixed;
    }

    .dark .fi-body:has(.fi-layout) {
        background:
            radial-gradient(70rem 26rem at 100% -8rem, color-mix(in oklab, var(--primary-500) 9%, transparent), transparent 70%),
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

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        box-shadow: 0 6px 16px -6px color-mix(in oklab, var(--primary-600) 70%, transparent);
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

    /* ---------- Stat cards ---------- */
    .fi-wi-stats-overview-stat {
        --gx-accent: var(--primary-500);
        overflow: hidden;
        padding: 1.25rem 1.375rem 1.5rem;
        background:
            radial-gradient(14rem 9rem at 100% 0%, color-mix(in oklab, var(--gx-accent) 10%, transparent), transparent 70%),
            var(--gx-card-bg);
        transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s;
    }

    .fi-wi-stats-overview-stat::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 3px;
        background: linear-gradient(180deg, var(--gx-accent), color-mix(in oklab, var(--gx-accent) 20%, transparent));
        opacity: 0.85;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 0 1px var(--gx-card-ring), var(--gx-card-shadow-hover);
    }

    .fi-wi-stats-overview-stat[data-accent='orange'] { --gx-accent: #f97316; }
    .fi-wi-stats-overview-stat[data-accent='blue'] { --gx-accent: #3b82f6; }
    .fi-wi-stats-overview-stat[data-accent='amber'] { --gx-accent: #f59e0b; }
    .fi-wi-stats-overview-stat[data-accent='emerald'] { --gx-accent: #10b981; }
    .fi-wi-stats-overview-stat[data-accent='violet'] { --gx-accent: #8b5cf6; }
    .fi-wi-stats-overview-stat[data-accent='teal'] { --gx-accent: #14b8a6; }
    .fi-wi-stats-overview-stat[data-accent='rose'] { --gx-accent: #f43f5e; }

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
        color: var(--gx-accent);
        background: color-mix(in oklab, var(--gx-accent) 13%, transparent);
        box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--gx-accent) 18%, transparent);
    }

    .fi-wi-stats-overview-stat-label {
        padding-top: 0.25rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--gray-500);
    }

    .dark .fi-wi-stats-overview-stat-label {
        color: var(--gray-400);
    }

    .fi-wi-stats-overview-stat-value {
        margin-top: -0.5rem;
        font-size: 1.875rem;
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    .fi-wi-stats-overview-stat-description {
        font-size: 0.8125rem;
        font-weight: 500;
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
            radial-gradient(32rem 18rem at 100% 0%, color-mix(in oklab, var(--primary-500) 45%, transparent), transparent 65%),
            radial-gradient(24rem 14rem at 0% 120%, color-mix(in oklab, var(--primary-400) 22%, transparent), transparent 60%),
            linear-gradient(135deg, #0f172a, #1e293b);
        box-shadow: 0 0 0 1px rgb(255 255 255 / 0.06), 0 18px 40px -18px rgb(15 23 42 / 0.6);
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
        color: rgb(255 255 255 / 0.75);
        background: rgb(255 255 255 / 0.08);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.1);
    }

    .gx-welcome-date .fi-icon {
        width: 0.875rem;
        height: 0.875rem;
        color: var(--primary-300);
    }

    .gx-welcome-title {
        margin-top: 0.875rem;
        font-size: clamp(1.375rem, 2.4vw, 1.875rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.025em;
    }

    .gx-welcome-title span {
        background: linear-gradient(90deg, var(--primary-300), var(--primary-500));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .gx-welcome-text {
        margin-top: 0.375rem;
        font-size: 0.9375rem;
        color: rgb(255 255 255 / 0.65);
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
        background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        box-shadow: 0 8px 20px -8px color-mix(in oklab, var(--primary-500) 80%, transparent);
    }

    .gx-welcome-action-primary:hover {
        background: linear-gradient(135deg, var(--primary-400), var(--primary-500));
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
        .gx-cache-btn .fi-icon {
            animation: none;
            transition: none;
        }

        .fi-wi-stats-overview-stat:hover,
        .gx-welcome-action:hover {
            transform: none;
        }
    }
</style>
