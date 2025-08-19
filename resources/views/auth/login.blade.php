<x-guest-layout>
    @php
        $image1 = asset('images/rv-glass-outside.jpg'); 
        $image2 = asset('images/j-glass-outside.jpg'); 
        $logoUrl = asset('images/rvJ-glass-logo.png'); 
    @endphp

    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg flex overflow-hidden w-full max-w-3xl">
            
            <!-- Left: Image Slideshow -->
            <div id="slideshow" class="w-1/2 relative aspect-[4/3] overflow-hidden bg-white">
                <div class="slides flex w-full h-full transition-transform duration-1000 ease-in-out">
                    <img src="{{ $image1 }}" class="w-full h-full object-contain flex-shrink-0" alt="RV Glass and Aluminum Supply">
                    <img src="{{ $image2 }}" class="w-full h-full object-contain flex-shrink-0" alt="J Glass and General Merchandise">
                    <!-- Duplicate first image for seamless loop -->
                    <img src="{{ $image1 }}" class="w-full h-full object-contain flex-shrink-0" alt="RV Glass and Aluminum Supply">
                </div>
            </div>

            <!-- Right: Login Form -->
            <div class="w-1/2 bg-[#E31C23] flex flex-col items-center justify-center p-12">
                <img src="{{ $logoUrl }}" alt="RV Glass and Aluminum Supply Logo" class="mt-1.5 mb-12 w-56">
                <form method="POST" action="{{ route('login') }}" class="w-full max-w-lg">
                    @csrf
                    <!-- Email Address -->
                    <div class="mb-4">
                        <input id="email" name="email" type="email" placeholder="Email Address" required autofocus autocomplete="username"
                            class="w-full px-4 py-3 rounded bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-yellow-200" />
                    </div>

                    <!-- Password -->
                    <div class="mb-4 relative">
                        <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password"
                            class="w-full px-4 py-3 rounded bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-yellow-200" />
                    </div>

                    <button type="submit"
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 rounded mb-3 transition">
                        Log In
                    </button>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="block text-center text-yellow-200 hover:text-yellow-100 text-sm">Forgot password?</a>
                    @endif
                </form>
            </div>
        </div>
    </div>


    <!-- Slideshow Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const slidesContainer = document.querySelector("#slideshow .slides");
            const totalSlides = slidesContainer.children.length;
            let current = 0;

            function showNextSlide() {
                current++;
                slidesContainer.style.transition = "transform 1s ease-in-out";
                slidesContainer.style.transform = `translateX(-${current * 100}%)`;

                // If we're at the last duplicate slide, reset instantly after animation
                if (current === totalSlides - 1) {
                    setTimeout(() => {
                        slidesContainer.style.transition = "none";
                        slidesContainer.style.transform = "translateX(0)";
                        current = 0;
                    }, 1000); // match transition duration
                }
            }

            setInterval(showNextSlide, 4000); // 4s pause (3s still + 1s slide)
        });
    </script>
</x-guest-layout>
