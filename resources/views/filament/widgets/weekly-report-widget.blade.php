<x-filament-widgets::widget>
    <x-filament::section>
        <div>
    @if($this->getUnreadReportsCount() > 0)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Trigger modal custom
                window.dispatchEvent(new CustomEvent('open-weekly-report-modal', {
                    detail: { count: {{ $this->getUnreadReportsCount() }} }
                }));
            });
        </script>
    @endif
</div>

    </x-filament::section>
</x-filament-widgets::widget>
