<div class="flex items-center space-x-2">
    <form method="POST" action="{{ route('language.switch') }}" class="inline">
        @csrf
        <select name="locale" onchange="this.form.submit()" 
                class="text-sm border-0 bg-transparent focus:ring-0 focus:outline-none text-gray-700 dark:text-gray-300">
            <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>
                ID
            </option>
            <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>
                EN
            </option>
        </select>
    </form>
</div>