<form wire:submit.prevent="login" class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 space-y-5">
    <div>
        <p class="text-sm font-bold uppercase tracking-wider text-blue-600">WebsiteV2</p>
        <h1 class="mt-2 text-2xl font-black text-gray-900">Login</h1>
    </div>

    <div>
        <label for="website-v2-login-email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
        <input id="website-v2-login-email" type="email" wire:model.defer="email" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="website-v2-login-password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
        <input id="website-v2-login-password" type="password" wire:model.defer="password" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" wire:model.defer="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        Remember me
    </label>

    <button type="submit" class="w-full rounded-full bg-gray-900 px-5 py-3 text-sm font-bold text-white hover:bg-blue-600 transition">
        Login
    </button>

    <p class="text-center text-sm text-gray-500">
        No account yet?
        <a href="{{ route('website-v2.register') }}" class="font-bold text-blue-600 hover:text-blue-800">Register</a>
    </p>
</form>
