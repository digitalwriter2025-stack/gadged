<style>
    /* Admin auth pages (sign in / password reset): mobile-first card, split-screen from 1024px. */
    .fi-simple-layout:has(> .gx-auth-brand) {
        position: relative;
        isolation: isolate;
        background:
            radial-gradient(60rem 30rem at 110% -10%, color-mix(in oklab, var(--primary-500) 16%, transparent), transparent 60%),
            radial-gradient(45rem 25rem at -10% 110%, color-mix(in oklab, var(--primary-400) 12%, transparent), transparent 60%),
            var(--gray-50);
    }

    .dark .fi-simple-layout:has(> .gx-auth-brand) {
        background:
            radial-gradient(60rem 30rem at 110% -10%, color-mix(in oklab, var(--primary-500) 14%, transparent), transparent 60%),
            radial-gradient(45rem 25rem at -10% 110%, color-mix(in oklab, var(--primary-600) 10%, transparent), transparent 60%),
            var(--gray-950);
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main-ctn {
        padding: 1rem;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main {
        position: relative;
        overflow: hidden;
        max-width: 28rem;
        margin-block: 1.5rem;
        padding: 2.25rem 1.375rem 1.75rem;
        border-radius: 1.25rem;
        box-shadow:
            0 0 0 1px rgb(15 23 42 / 0.05),
            0 1px 2px rgb(15 23 42 / 0.04),
            0 12px 32px -8px rgb(15 23 42 / 0.12);
        animation: gx-auth-rise 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main::before {
        content: '';
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-400), var(--primary-600));
    }

    .dark .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main {
        box-shadow:
            0 0 0 1px rgb(255 255 255 / 0.08),
            0 12px 32px -8px rgb(0 0 0 / 0.5);
    }

    @media (min-width: 640px) {
        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main {
            margin-block: 3rem;
            padding: 2.75rem 2.5rem 2.25rem;
        }
    }

    /* Header */
    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header .fi-logo {
        margin-bottom: 1.25rem;
        font-size: 1.25rem;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header-heading {
        font-size: 1.625rem;
        line-height: 2rem;
        letter-spacing: -0.02em;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header-subheading {
        margin-top: 0.375rem;
        font-size: 0.9375rem;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-page-content {
        row-gap: 1.75rem;
    }

    /* Fields */
    .fi-simple-layout:has(> .gx-auth-brand) .fi-input-wrp {
        min-height: 2.875rem;
        border-radius: 0.75rem;
        outline: 0 solid transparent;
        transition: outline-width 0.15s ease, outline-color 0.15s ease;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-input-wrp:focus-within {
        outline: 4px solid color-mix(in oklab, var(--primary-500) 18%, transparent);
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-input {
        padding-block: 0.75rem;
        font-size: 1rem; /* 16px stops iOS from zooming into the field */
    }

    @media (min-width: 640px) {
        .fi-simple-layout:has(> .gx-auth-brand) .fi-input {
            font-size: 0.9375rem;
        }
    }

    /* Keep Chrome's autofill from painting the fields light blue */
    .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill,
    .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill:hover,
    .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill:focus {
        -webkit-text-fill-color: var(--gray-950);
        -webkit-box-shadow: 0 0 0 1000px #fff inset;
        caret-color: var(--gray-950);
        transition: background-color 9999s ease-out;
    }

    .dark .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill,
    .dark .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill:hover,
    .dark .fi-simple-layout:has(> .gx-auth-brand) input:-webkit-autofill:focus {
        -webkit-text-fill-color: #fff;
        -webkit-box-shadow: 0 0 0 1000px var(--gray-900) inset;
        caret-color: #fff;
    }

    .fi-simple-layout:has(> .gx-auth-brand) .fi-checkbox-input {
        width: 1.125rem;
        height: 1.125rem;
        border-radius: 0.375rem;
    }

    /* Primary submit button */
    .fi-simple-layout:has(> .gx-auth-brand) button.fi-btn[type='submit'] {
        min-height: 3rem;
        border-radius: 0.75rem;
        font-size: 0.9375rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        background-image: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        box-shadow: 0 8px 20px -8px color-mix(in oklab, var(--primary-600) 70%, transparent);
        transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
    }

    .fi-simple-layout:has(> .gx-auth-brand) button.fi-btn[type='submit']:not([disabled]):hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px -10px color-mix(in oklab, var(--primary-600) 80%, transparent);
    }

    .fi-simple-layout:has(> .gx-auth-brand) button.fi-btn[type='submit']:focus-visible {
        outline: 3px solid color-mix(in oklab, var(--primary-500) 45%, transparent);
        outline-offset: 2px;
    }

    .fi-simple-layout:has(> .gx-auth-brand) button.fi-btn[type='submit']:not([disabled]):active {
        transform: translateY(0);
    }

    /* Brand panel: only shown from 1024px */
    .gx-auth-brand {
        display: none;
    }

    @media (min-width: 1024px) {
        .fi-simple-layout:has(> .gx-auth-brand) {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            align-items: stretch;
            background: #fff;
        }

        .dark .fi-simple-layout:has(> .gx-auth-brand) {
            background: var(--gray-900);
        }

        .fi-simple-layout:has(> .gx-auth-brand) > :not(.gx-auth-brand) {
            grid-column: 2;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main-ctn {
            min-height: 100dvh;
            padding: 2rem 3rem;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main,
        .dark .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main {
            max-width: 26rem;
            margin: 0;
            padding: 0;
            overflow: visible;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main::before {
            display: none;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header {
            align-items: flex-start;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header .fi-logo {
            display: none;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header-heading,
        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header-subheading {
            text-align: start;
        }

        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-header-heading {
            font-size: 2rem;
            line-height: 2.5rem;
        }

        .gx-auth-brand {
            position: sticky;
            top: 0;
            grid-column: 1;
            grid-row: 1 / span 20;
            display: flex;
            height: 100dvh;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(155deg, #0f172a 0%, #111827 45%, color-mix(in oklab, var(--primary-700) 70%, #111827) 130%);
            border-inline-end: 1px solid rgb(255 255 255 / 0.06);
        }
    }

    .gx-auth-brand-glow {
        position: absolute;
        width: 36rem;
        height: 36rem;
        right: -12rem;
        bottom: -14rem;
        border-radius: 9999px;
        background: radial-gradient(circle, color-mix(in oklab, var(--primary-500) 55%, transparent), transparent 65%);
        filter: blur(10px);
        animation: gx-auth-float 9s ease-in-out infinite alternate;
    }

    .gx-auth-brand-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgb(255 255 255 / 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgb(255 255 255 / 0.05) 1px, transparent 1px);
        background-size: 44px 44px;
        mask-image: radial-gradient(ellipse at 30% 30%, #000 20%, transparent 75%);
    }

    .gx-auth-brand-inner {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 2.5rem;
        width: 100%;
        max-width: 36rem;
        margin-inline: auto;
        padding: 3rem clamp(2.5rem, 5vw, 4.5rem);
    }

    .gx-auth-brand-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .gx-auth-brand-logo img {
        height: 2.5rem;
        width: auto;
        max-width: 11rem;
        object-fit: contain;
    }

    .gx-auth-brand-logo img + .gx-auth-brand-name {
        display: none;
    }

    .gx-auth-brand-monogram {
        display: grid;
        place-items: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        font-size: 1.125rem;
        background: linear-gradient(135deg, var(--primary-400), var(--primary-600));
        box-shadow: 0 8px 20px -6px color-mix(in oklab, var(--primary-500) 70%, transparent);
    }

    .gx-auth-brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--primary-300);
        background: rgb(255 255 255 / 0.06);
        border: 1px solid rgb(255 255 255 / 0.1);
    }

    .gx-auth-brand-badge-icon {
        width: 1rem;
        height: 1rem;
    }

    .gx-auth-brand-copy h2 {
        margin-top: 1.25rem;
        font-size: clamp(2rem, 3vw, 2.75rem);
        line-height: 1.12;
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    .gx-auth-brand-copy > p {
        margin-top: 1rem;
        max-width: 28rem;
        font-size: 1rem;
        line-height: 1.6;
        color: rgb(255 255 255 / 0.68);
    }

    .gx-auth-brand-features {
        display: grid;
        gap: 1rem;
        margin-top: 2.25rem;
    }

    .gx-auth-brand-features li {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        padding: 0.875rem 1rem;
        border-radius: 1rem;
        background: rgb(255 255 255 / 0.04);
        border: 1px solid rgb(255 255 255 / 0.07);
        backdrop-filter: blur(6px);
    }

    .gx-auth-brand-features strong {
        display: block;
        font-size: 0.9375rem;
        font-weight: 600;
    }

    .gx-auth-brand-features small {
        display: block;
        margin-top: 0.125rem;
        font-size: 0.8125rem;
        color: rgb(255 255 255 / 0.6);
    }

    .gx-auth-brand-feature-icon {
        display: grid;
        flex-shrink: 0;
        place-items: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.625rem;
        color: var(--primary-300);
        background: color-mix(in oklab, var(--primary-500) 18%, transparent);
    }

    .gx-auth-brand-feature-icon .fi-icon,
    .gx-auth-brand-feature-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .gx-auth-brand-footer {
        font-size: 0.8125rem;
        color: rgb(255 255 255 / 0.45);
    }

    @media (min-width: 1024px) and (max-height: 760px) {
        .gx-auth-brand-features {
            display: none;
        }
    }

    @keyframes gx-auth-rise {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes gx-auth-float {
        from {
            transform: translate(0, 0) scale(1);
        }

        to {
            transform: translate(-3rem, -2rem) scale(1.1);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .fi-simple-layout:has(> .gx-auth-brand) .fi-simple-main,
        .gx-auth-brand-glow {
            animation: none;
        }
    }
</style>
