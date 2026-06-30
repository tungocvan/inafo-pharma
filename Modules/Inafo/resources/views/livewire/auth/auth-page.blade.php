<main class="relative min-h-screen overflow-hidden bg-[#F8F9FA] px-3 py-10 text-[#222222] sm:px-4">
    <div class="absolute inset-x-0 top-0 h-[240px] bg-[#00533F]"></div>

    <div class="relative mx-auto flex w-full max-w-[1120px] items-center justify-between gap-4 pb-8">
        <a href="{{ route(config('inafo.inafo.route_name', 'inafo') . '.home') }}" class="flex items-center gap-2 text-white">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-[15px] font-bold text-[#00533F]">IN</span>
            <span class="text-[28px] font-bold leading-[42px] tracking-normal md:text-[32px] md:leading-[49px]">{{ config('inafo.inafo.brand_name', 'INAFO Pharma') }}</span>
        </a>

        <div class="hidden items-center gap-3 sm:flex">
            <button wire:click="showLogin" type="button" class="h-[37px] rounded-[1440px] px-4 py-2 text-[14px] font-bold leading-[21px] transition {{ $mode === 'login' ? 'bg-white text-[#00533F]' : 'text-white hover:bg-white/10' }}">
                Dang nhap
            </button>
            <button wire:click="showRegister" type="button" class="h-[37px] rounded-[1440px] px-4 py-2 text-[14px] font-bold leading-[21px] transition {{ $mode === 'register' ? 'bg-white text-[#00533F]' : 'text-white hover:bg-white/10' }}">
                Dang ky
            </button>
        </div>
    </div>

    <section class="relative mx-auto grid w-full max-w-[1120px] gap-6 lg:grid-cols-[1fr_500px]">
        <div class="hidden min-h-[560px] overflow-hidden rounded-2xl bg-[#00533F] lg:block">
            <div class="flex h-full flex-col justify-end bg-[#00533F] p-10 text-white">
                <p class="text-[14px] font-bold uppercase leading-[21px] text-[#FFC107]">Pharma OTC</p>
                <h1 class="mt-3 max-w-lg text-[32px] font-bold leading-[49px]">San OTC hang tuyen chon, dong hanh cung nha thuoc Viet.</h1>
                <p class="mt-4 max-w-md text-[15px] font-normal leading-[27px] text-white/90">Dang nhap de xem gia rieng, quan ly gio hang va dat hang nhanh cho ho so kinh doanh da xac minh.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-[rgba(34,34,34,0.15)_0px_4px_16px_-2px]">
            <div class="grid grid-cols-2 border-b border-[#E9ECEF] bg-white">
                <button wire:click="showLogin" type="button" class="h-14 text-[14px] font-bold leading-[21px] transition {{ $mode === 'login' ? 'bg-[#EAF5F2] text-[#00533F] shadow-[inset_0_-3px_0_#00533F]' : 'text-[#222222] hover:bg-[#F8F9FA]' }}">
                    Dang nhap
                </button>
                <button wire:click="showRegister" type="button" class="h-14 text-[14px] font-bold leading-[21px] transition {{ $mode === 'register' ? 'bg-[#EAF5F2] text-[#00533F] shadow-[inset_0_-3px_0_#00533F]' : 'text-[#222222] hover:bg-[#F8F9FA]' }}">
                    Dang ky
                </button>
            </div>

            <div class="bg-[#00533F] px-6 py-6 text-center text-white sm:px-8">
                <h2 class="text-[22px] font-bold leading-8 md:text-[26px] md:leading-9">Pharmalink OTC - SAN OTC HANG TUYEN CHON</h2>
                <p class="mt-1 text-[19px] font-bold leading-[23px]">Dong hanh cung nha thuoc Viet!</p>
            </div>

            <div class="px-4 py-7 sm:px-6">
                <p class="mb-6 text-center text-[14px] font-normal leading-[21px] text-[#6C757D]">Nhan ngay muc chiet khau cao nhat thi truong</p>

                @if ($mode === 'login')
                    <form wire:submit="login" class="space-y-4">
                        <x-inafo::auth.input
                            name="phone"
                            type="tel"
                            placeholder="Nhap so dien thoai"
                            autocomplete="tel"
                        />

                        <x-inafo::auth.input
                            name="password"
                            type="password"
                            placeholder="Nhap mat khau"
                            autocomplete="current-password"
                            trailing="eye"
                        />

                        <label class="flex items-center gap-3 text-[14px] font-normal leading-[21px] text-[#4A5568]">
                            <input wire:model.live="remember" type="checkbox" class="h-[18px] w-[18px] rounded border-2 border-[#CED4DA] text-[#00533F] focus:ring-[3px] focus:ring-[#00533F]/15">
                            Ghi nho dang nhap
                        </label>

                        <button type="submit" wire:loading.attr="disabled" wire:target="login" class="flex h-[40px] w-full items-center justify-center rounded-md bg-[#00533F] px-4 py-2 text-[14px] font-bold leading-[21px] text-white transition hover:bg-[#003D2E] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/25 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="login">Dang Nhap</span>
                            <span wire:loading wire:target="login">Dang dang nhap...</span>
                        </button>

                        <a href="#" class="block text-center text-[14px] font-bold leading-[21px] text-[#00533F] hover:underline">Quen mat khau?</a>
                    </form>
                @else
                    <form wire:submit="register" class="space-y-4">
                        <x-inafo::auth.input
                            name="phone"
                            type="tel"
                            placeholder="Nhap so dien thoai *"
                            autocomplete="tel"
                        />

                        <x-inafo::auth.input
                            name="password"
                            type="password"
                            placeholder="Nhap mat khau *"
                            autocomplete="new-password"
                            trailing="eye"
                        />

                        <x-inafo::auth.input
                            name="password_confirmation"
                            type="password"
                            placeholder="Nhap lai mat khau *"
                            autocomplete="new-password"
                            trailing="eye"
                        />

                        <x-inafo::auth.select
                            name="province"
                            placeholder="Tinh/Thanh pho *"
                            :options="$provinces"
                        />

                        <label class="flex items-start gap-3 text-[14px] font-normal leading-[21px] text-[#4A5568]">
                            <input wire:model.live="agree_terms" type="checkbox" class="mt-0.5 h-[18px] w-[18px] rounded border-2 border-[#CED4DA] text-[#00533F] focus:ring-[3px] focus:ring-[#00533F]/15">
                            <span>Toi da doc va dong y voi <a href="#" class="font-medium text-[#00533F] hover:underline">Dieu khoan su dung</a> va <a href="#" class="font-medium text-[#00533F] hover:underline">Chinh sach bao mat</a></span>
                        </label>
                        @error('agree_terms')
                            <p class="-mt-2 text-[14px] leading-[21px] text-[#DC3545]">{{ $message }}</p>
                        @enderror

                        <button type="submit" wire:loading.attr="disabled" wire:target="register" class="flex h-[40px] w-full items-center justify-center rounded-md bg-[#00533F] px-4 py-2 text-[14px] font-bold leading-[21px] text-white transition hover:bg-[#003D2E] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/25 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="register">Dang ky</span>
                            <span wire:loading wire:target="register">Dang tao tai khoan...</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</main>
