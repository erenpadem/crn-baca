<x-filament-panels::page>
    @php($d = $this->getDealer())
    @if ($d)
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ünvan</dt>
                    <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $d->unvan }}</dd>
                </div>
                @if ($d->firma_no)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Firma no</dt>
                        <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $d->firma_no }}</dd>
                    </div>
                @endif
                @if ($d->il_ilce)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">İl / ilçe</dt>
                        <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $d->il_ilce }}</dd>
                    </div>
                @endif
                @if ($d->tel)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefon</dt>
                        <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $d->tel }}</dd>
                    </div>
                @endif
                @if ($d->mail)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">E-posta</dt>
                        <dd class="mt-1 text-base text-gray-950 dark:text-white">{{ $d->mail }}</dd>
                    </div>
                @endif
            </dl>
            @if ($d->adres)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Adres</dt>
                    <dd class="mt-1 text-base text-gray-950 dark:text-white whitespace-pre-line">{{ $d->adres }}</dd>
                </div>
            @endif
            @if ($d->sevk_adresi)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sevk adresi</dt>
                    <dd class="mt-1 text-base text-gray-950 dark:text-white whitespace-pre-line">{{ $d->sevk_adresi }}</dd>
                </div>
            @endif
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400">Bayi bilgisi bulunamadı.</p>
    @endif
</x-filament-panels::page>
