<div x-data="{ open: false, url: '', name: '' }"
     x-on:delete-product.window="url = $event.detail.delete_url; name = $event.detail.name; open = true"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 px-4"
     x-cloak
     x-show="open"
     x-transition.opacity>
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.outside="open = false" x-transition.scale>
        <h2 class="text-xl font-bold text-gray-900">Delete Product</h2>
        <p class="mt-2 text-sm text-gray-600">Are you sure you want to delete <span class="font-semibold text-gray-900" x-text="name"></span>? This action cannot be undone.</p>
        <form :action="url" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50" @click="open = false">Cancel</button>
            <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-rose-700">Delete</button>
        </form>
    </div>
</div>
