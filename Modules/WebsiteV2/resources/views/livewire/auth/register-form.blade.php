<form wire:submit.prevent="register" class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 space-y-5">
    <div>
        <p class="text-sm font-bold uppercase tracking-wider text-blue-600">WebsiteV2</p>
        <h1 class="mt-2 text-2xl font-black text-gray-900">Register</h1>
    </div>

    <div>
        <label for="website-v2-register-name" class="block text-sm font-bold text-gray-700 mb-2">Name</label>
        <input id="website-v2-register-name" type="text" wire:model.defer="name" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="website-v2-register-email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
        <input id="website-v2-register-email" type="email" wire:model.defer="email" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="website-v2-register-password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
        <input id="website-v2-register-password" type="password" wire:model.defer="password" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
        @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="website-v2-register-password-confirmation" class="block text-sm font-bold text-gray-700 mb-2">Confirm password</label>
        <input id="website-v2-register-password-confirmation" type="password" wire:model.defer="password_confirmation" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <button type="submit" class="w-full rounded-full bg-gray-900 px-5 py-3 text-sm font-bold text-white hover:bg-blue-600 transition">
        Register
    </button>

    <p class="text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('website-v2.login') }}" class="font-bold text-blue-600 hover:text-blue-800">Login</a>
    </p>
</form>
