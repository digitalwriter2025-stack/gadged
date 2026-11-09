@props(['id', 'title'])

<dialog id="{{ $id }}" class="modal m-auto w-[min(28rem,calc(100vw-2rem))] rounded-lg bg-white p-0 text-gray-900 shadow-2xl backdrop:bg-black/50" aria-labelledby="{{ $id }}-title" data-modal>
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
        <h2 id="{{ $id }}-title" class="text-sm font-semibold">{{ $title }}</h2>
        <button type="button" data-modal-close class="grid size-8 cursor-pointer place-items-center rounded-md text-gray-500 hover:bg-surface hover:text-gray-900" aria-label="{{ __('Close') }}">
            <x-store.icon name="x" class="size-5" />
        </button>
    </div>
    <div class="px-4 py-4 text-[13px] leading-6 text-gray-600">
        {{ $slot }}
    </div>
</dialog>
