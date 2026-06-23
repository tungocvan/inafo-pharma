<div class="space-y-8">
    @if ($errors->has('system'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg animate-pulse">
            <p class="text-sm text-red-700 font-bold">{{ $errors->first('system') }}</p>
        </div>
    @endif

    @guest
        <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl flex items-start gap-3">
            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Already have an account?</h3>
                <p class="text-sm text-gray-500 mt-1">
                    <a href="{{ route('website-v2.login') }}?redirect=checkout" class="text-blue-600 font-bold hover:underline">Login now</a>
                    to track orders more easily.
                </p>
            </div>
        </div>
    @endguest

    <form wire:submit="placeOrder" class="space-y-8">
        <section class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-black text-white font-bold text-sm">1</span>
                <h2 class="text-xl font-bold text-gray-900">Shipping Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Full name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.blur="customer_name" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors @error('customer_name') border-red-500 bg-red-50 @enderror">
                    @error('customer_name') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Phone <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.blur="customer_phone" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors @error('customer_phone') border-red-500 bg-red-50 @enderror" maxlength="20">
                    @error('customer_phone') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-gray-700">Email</label>
                    <input type="email" wire:model.blur="customer_email" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors @error('customer_email') border-red-500 bg-red-50 @enderror">
                    @error('customer_email') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-gray-700">Address <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.blur="customer_address" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors @error('customer_address') border-red-500 bg-red-50 @enderror">
                    @error('customer_address') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-bold text-gray-700">Delivery note</label>
                    <textarea wire:model="note" rows="3" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors"></textarea>
                </div>
            </div>
        </section>

        <section class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-black text-white font-bold text-sm">2</span>
                <h2 class="text-xl font-bold text-gray-900">Payment</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-all duration-200 group {{ $payment_method === 'cod' ? 'border-blue-600 bg-blue-50/30 ring-1 ring-blue-600' : 'border-gray-200' }}">
                    <input wire:model.live="payment_method" value="cod" type="radio" class="sr-only">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">COD</span>
                            <span class="block text-xs text-gray-500">Pay when receiving goods</span>
                        </div>
                    </div>
                </label>

                <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-all duration-200 group {{ $payment_method === 'momo' ? 'border-pink-600 bg-pink-50/30 ring-1 ring-pink-600' : 'border-gray-200' }}">
                    <input wire:model.live="payment_method" value="momo" type="radio" class="sr-only">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100">
                            <img src="https://developers.momo.vn/v3/img/logo.svg" alt="Momo" class="w-full h-full object-cover p-1">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">MoMo</span>
                            <span class="block text-xs text-gray-500">Fast QR payment</span>
                        </div>
                    </div>
                </label>
            </div>
            @error('payment_method') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
        </section>

        <button type="submit" wire:loading.attr="disabled" class="w-full bg-black text-white font-bold py-5 rounded-xl hover:bg-gray-800 transition-all shadow-xl hover:shadow-2xl text-lg uppercase tracking-wide relative overflow-hidden group">
            <div wire:loading.remove wire:target="placeOrder" class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Confirm order
            </div>
            <div wire:loading wire:target="placeOrder" class="absolute inset-0 flex items-center justify-center bg-gray-800">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </button>
    </form>
</div>
