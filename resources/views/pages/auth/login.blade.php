<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6" x-data="qrLogin()">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- QR Login Section --}}
        <template x-if="qrMode">
            <div class="flex flex-col items-center gap-4">
                {{-- Status: Loading --}}
                <template x-if="qrStatus === 'loading'">
                    <div class="flex flex-col items-center gap-2 py-4">
                        <svg class="size-6 animate-spin text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm text-zinc-400">QR-code laden...</span>
                    </div>
                </template>

                {{-- Status: Pending (show QR) --}}
                <template x-if="qrStatus === 'pending'">
                    <div class="flex flex-col items-center gap-3">
                        <div id="qr-code-container" class="rounded-lg bg-white p-3"></div>

                        <div class="flex items-center gap-2 text-sm text-zinc-400">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Verloopt over <strong x-text="countdown" class="text-zinc-200"></strong>s</span>
                        </div>
                        <p class="text-center text-xs text-zinc-500">
                            Scan deze QR-code met je telefoon waarop je al bent ingelogd.
                        </p>
                    </div>
                </template>

                {{-- Status: Approved (consuming) --}}
                <template x-if="qrStatus === 'approved'">
                    <div class="flex flex-col items-center gap-2 py-4">
                        <svg class="size-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-sm font-medium text-green-400">Login goedgekeurd! Je wordt doorgestuurd...</span>
                    </div>
                </template>

                {{-- Status: Expired --}}
                <template x-if="qrStatus === 'expired'">
                    <div class="flex flex-col items-center gap-3 py-4">
                        <svg class="size-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-sm text-amber-400">QR-code is verlopen.</span>
                        <button @click="startQrSession()" class="text-sm text-zinc-400 underline hover:text-white">
                            Opnieuw genereren
                        </button>
                    </div>
                </template>

                {{-- Status: Denied --}}
                <template x-if="qrStatus === 'denied'">
                    <div class="flex flex-col items-center gap-3 py-4">
                        <svg class="size-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-sm text-red-400">Login is geweigerd.</span>
                        <button @click="startQrSession()" class="text-sm text-zinc-400 underline hover:text-white">
                            Opnieuw proberen
                        </button>
                    </div>
                </template>

                {{-- Back to normal login --}}
                <button @click="qrMode = false; cleanup()" class="text-sm text-zinc-400 underline hover:text-white">
                    &larr; Terug naar e-mail login
                </button>
            </div>
        </template>

        {{-- Normal Login Form --}}
        <template x-if="!qrMode">
            <div class="flex flex-col gap-6">
                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <!-- Email Address -->
                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <!-- Password -->
                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('Password')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <!-- Remember Me -->
                    <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                            {{ __('Log in') }}
                        </flux:button>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-zinc-700"></span>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-zinc-900 px-2 text-zinc-500">of ga verder met</span>
                    </div>
                </div>

                {{-- Social Buttons --}}
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('social.redirect', 'google') }}"
                       class="flex items-center justify-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-2.5 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </a>
                    <a href="{{ route('social.redirect', 'github') }}"
                       class="flex items-center justify-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-2.5 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        GitHub
                    </a>
                </div>

                {{-- QR Login Button --}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-zinc-700"></span>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-zinc-900 px-2 text-zinc-500">of</span>
                    </div>
                </div>

                <button
                    @click="qrMode = true; startQrSession()"
                    class="flex items-center justify-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-2.5 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white w-full"
                    data-test="qr-login-button"
                >
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                    </svg>
                    Login met QR-code
                </button>

                @if (Route::has('register'))
                    <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-400">
                        <span>{{ __('Don\'t have an account?') }}</span>
                        <flux:link :href="route('register')" wire:navigate class="text-white hover:text-zinc-200">{{ __('Sign up') }}</flux:link>
                    </div>
                @endif
            </div>
        </template>
    </div>

    {{-- QR code JS library (loaded only when needed) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
            integrity="sha256-xUHvBjJ4hahBW8qN9gceFBibSFUzbe9PNttUvehITzY="
            crossorigin="anonymous"
            defer></script>

    <script>
        /**
         * Alpine.js component for the QR login flow.
         *
         * @security All token handling happens server-side. The client only holds the
         *           plain token temporarily for polling and QR display.
         */
        function qrLogin() {
            return {
                qrMode: false,
                qrStatus: 'loading',       // loading | pending | approved | expired | denied | invalid
                token: null,
                countdown: 120,
                pollInterval: null,
                countdownInterval: null,
                qrInstance: null,

                /**
                 * Start a new QR login session by calling the backend.
                 */
                async startQrSession() {
                    this.cleanup();
                    this.qrStatus = 'loading';
                    this.countdown = 120;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const response = await fetch('{{ route("qr-login.start") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            this.qrStatus = 'invalid';
                            return;
                        }

                        const data = await response.json();
                        this.token = data.token;
                        this.qrStatus = 'pending';

                        // Wait for DOM to render QR container, then generate QR
                        this.$nextTick(() => {
                            const container = document.getElementById('qr-code-container');
                            if (container) {
                                container.innerHTML = '';
                                this.qrInstance = new QRCode(container, {
                                    text: data.scan_url,
                                    width: 200,
                                    height: 200,
                                    colorDark: '#000000',
                                    colorLight: '#ffffff',
                                    correctLevel: QRCode.CorrectLevel.M,
                                });
                            }
                        });

                        this.startPolling();
                        this.startCountdown();
                    } catch (error) {
                        console.error('QR session start failed:', error);
                        this.qrStatus = 'invalid';
                    }
                },

                /**
                 * Poll the status endpoint every 3 seconds.
                 */
                startPolling() {
                    this.pollInterval = setInterval(async () => {
                        if (!this.token) return;

                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            const response = await fetch('{{ route("qr-login.status") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ token: this.token }),
                            });

                            if (!response.ok) {
                                this.qrStatus = 'invalid';
                                this.cleanup();
                                return;
                            }

                            const data = await response.json();
                            this.qrStatus = data.status;

                            if (data.status === 'approved') {
                                this.cleanup();
                                await this.consumeToken();
                            } else if (data.status === 'expired' || data.status === 'denied' || data.status === 'consumed') {
                                this.cleanup();
                            }
                        } catch (error) {
                            console.error('QR status poll failed:', error);
                        }
                    }, 3000);
                },

                /**
                 * Countdown timer for token expiry.
                 */
                startCountdown() {
                    this.countdownInterval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.qrStatus = 'expired';
                            this.cleanup();
                        }
                    }, 1000);
                },

                /**
                 * Consume the approved token and redirect the user.
                 *
                 * @security Token is sent to the server for final authentication.
                 */
                async consumeToken() {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const response = await fetch('{{ route("qr-login.consume") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ token: this.token }),
                        });

                        const data = await response.json();

                        if (response.ok && data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            this.qrStatus = 'invalid';
                        }
                    } catch (error) {
                        console.error('QR consume failed:', error);
                        this.qrStatus = 'invalid';
                    }
                },

                /**
                 * Clean up intervals when leaving QR mode or on status change.
                 */
                cleanup() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                },
            };
        }
    </script>
</x-layouts::auth>
