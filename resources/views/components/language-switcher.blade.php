@props(['theme' => 'light'])

<div class="flex items-center gap-1 shrink-0">
    <form method="POST" action="{{ route('locale.switch') }}" class="m-0">
        @csrf
        <input type="hidden" name="locale" value="en">
        <button type="submit" class="text-[10px] font-medium px-1.5 py-0.5 rounded leading-none transition-colors
            {{ $theme === 'dark'
                ? (app()->getLocale() === 'en' ? 'bg-[#3f3f46] text-white' : 'text-[#52525b] hover:text-[#a1a1aa]')
                : (app()->getLocale() === 'en' ? 'bg-[#eaeaea] text-[#353636]' : 'text-[#7c7c86] hover:text-[#353636]') }}">
            EN
        </button>
    </form>
    <span class="text-[10px] leading-none {{ $theme === 'dark' ? 'text-[#3f3f46]' : 'text-[#d4d4d8]' }}">|</span>
    <form method="POST" action="{{ route('locale.switch') }}" class="m-0">
        @csrf
        <input type="hidden" name="locale" value="es">
        <button type="submit" class="text-[10px] font-medium px-1.5 py-0.5 rounded leading-none transition-colors
            {{ $theme === 'dark'
                ? (app()->getLocale() === 'es' ? 'bg-[#3f3f46] text-white' : 'text-[#52525b] hover:text-[#a1a1aa]')
                : (app()->getLocale() === 'es' ? 'bg-[#eaeaea] text-[#353636]' : 'text-[#7c7c86] hover:text-[#353636]') }}">
            ES
        </button>
    </form>
</div>
