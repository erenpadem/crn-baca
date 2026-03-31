@php
    use Filament\Support\Icons\Heroicon;

    $d = $this->getDealer();

    $firmaAciklama = 'Bu kayıt yönetim panelindeki firma kartınızla eşleşir.';
    if ($d && filled($d->ilgili_kisi)) {
        $firmaAciklama .= ' İlgili kişi: '.$d->ilgili_kisi.'.';
    }

    $hasIletisim = $d && ($d->tel || $d->tel_2 || $d->mail || $d->il_ilce);
    $hasTelefon = $d && ($d->tel || $d->tel_2);
    $hasEpostaKonum = $d && ($d->mail || $d->il_ilce);
@endphp

<x-filament-panels::page>
    @if ($d)
        {{--
            Filament paneli varsayılan tema CSS'i uygulama views'ındaki Tailwind sınıflarını içermez.
            İki sütun düzeni bu sayfaya özel scoped CSS ile verilir (sm+ yan yana).
        --}}
        <style>
            .cari-hesap-wrap {
                width: 100%;
                max-width: 72rem;
                margin-inline: auto;
            }
            .cari-hesap-row {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                width: 100%;
            }
            @media (min-width: 640px) {
                .cari-hesap-row {
                    flex-direction: row;
                    align-items: stretch;
                }
                .cari-hesap-row > .fi-section {
                    flex: 1 1 0%;
                    min-width: 0;
                }
                .cari-hesap-row > .fi-section.cari-span-full {
                    flex: 0 0 100%;
                    max-width: 100%;
                }
            }
            .cari-hesap-inner-row {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }
            @media (min-width: 640px) {
                .cari-hesap-inner-row {
                    flex-direction: row;
                    align-items: stretch;
                }
                .cari-hesap-inner-row > * {
                    flex: 1 1 0%;
                    min-width: 0;
                }
            }
            .cari-hesap-stack {
                margin-top: 1.5rem;
            }
        </style>

        <div class="cari-hesap-wrap self-start">
            <div class="cari-hesap-row">
                <x-filament::section
                    :heading="$d->unvan"
                    :description="$firmaAciklama"
                    :icon="Heroicon::OutlinedBuildingOffice2"
                    icon-color="primary"
                    :class="'min-w-0 '.(! filled($d->firma_no) ? 'cari-span-full' : '')"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="gray" size="sm">Kayıtlı firma</x-filament::badge>
                    </div>
                </x-filament::section>

                @if (filled($d->firma_no))
                    <x-filament::section
                        heading="Firma kodu"
                        description="Sipariş ve teklif belgelerinde geçen numara"
                        :icon="Heroicon::OutlinedHashtag"
                        icon-color="gray"
                        compact
                        class="min-w-0"
                    >
                        <p
                            class="font-mono text-2xl font-semibold tracking-tight text-primary-600 dark:text-primary-400"
                        >
                            {{ $d->firma_no }}
                        </p>
                    </x-filament::section>
                @endif
            </div>

            @if ($hasIletisim)
                <div class="cari-hesap-row cari-hesap-stack">
                    @if ($hasTelefon)
                        <x-filament::section
                            heading="Telefon"
                            description="Arama için kayıtlı numaralar"
                            :icon="Heroicon::OutlinedPhone"
                            icon-color="primary"
                            class="min-w-0 {{ ! $hasEpostaKonum ? 'cari-span-full' : '' }}"
                        >
                            <div class="cari-hesap-inner-row">
                                @if ($d->tel)
                                    <div
                                        class="rounded-xl border border-gray-200/90 bg-gray-50/80 p-4 ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10"
                                    >
                                        <p
                                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                        >
                                            Telefon
                                        </p>
                                        <a
                                            href="tel:{{ preg_replace('/\s+/', '', $d->tel) }}"
                                            class="mt-1 block text-lg font-semibold text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            {{ $d->tel }}
                                        </a>
                                    </div>
                                @endif
                                @if ($d->tel_2)
                                    <div
                                        class="rounded-xl border border-gray-200/90 bg-gray-50/80 p-4 ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10"
                                    >
                                        <p
                                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                        >
                                            Telefon (2)
                                        </p>
                                        <a
                                            href="tel:{{ preg_replace('/\s+/', '', $d->tel_2) }}"
                                            class="mt-1 block text-lg font-semibold text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            {{ $d->tel_2 }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </x-filament::section>
                    @endif

                    @if ($hasEpostaKonum)
                        <x-filament::section
                            heading="E-posta ve konum"
                            description="Yazışma ve bölge bilgisi"
                            :icon="Heroicon::OutlinedEnvelope"
                            icon-color="primary"
                            class="min-w-0 {{ ! $hasTelefon ? 'cari-span-full' : '' }}"
                        >
                            <div class="flex flex-col gap-3">
                                @if ($d->mail)
                                    <div
                                        class="rounded-xl border border-gray-200/90 bg-gray-50/80 p-4 ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10"
                                    >
                                        <p
                                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                        >
                                            E-posta
                                        </p>
                                        <a
                                            href="mailto:{{ $d->mail }}"
                                            class="mt-1 block break-all text-lg font-semibold text-primary-600 hover:underline dark:text-primary-400"
                                        >
                                            {{ $d->mail }}
                                        </a>
                                    </div>
                                @endif
                                @if ($d->il_ilce)
                                    <div
                                        class="rounded-xl border border-gray-200/90 bg-gray-50/80 p-4 ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10"
                                    >
                                        <p
                                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                        >
                                            İl / ilçe
                                        </p>
                                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $d->il_ilce }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </x-filament::section>
                    @endif
                </div>
            @else
                <x-filament::section
                    heading="İletişim"
                    description="Henüz bilgi yok"
                    :icon="Heroicon::OutlinedPhone"
                    icon-color="gray"
                    class="cari-hesap-stack"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Henüz iletişim bilgisi eklenmemiş.
                    </p>
                </x-filament::section>
            @endif

            @if ($d->adres || $d->sevk_adresi)
                <div class="cari-hesap-row cari-hesap-stack">
                    @if ($d->adres)
                        <x-filament::section
                            heading="Fatura / merkez adresi"
                            description="Kayıtlı fatura veya merkez adresi"
                            :icon="Heroicon::OutlinedHomeModern"
                            icon-color="gray"
                            class="min-w-0 {{ ! $d->sevk_adresi ? 'cari-span-full' : '' }}"
                        >
                            <p class="whitespace-pre-line text-sm leading-relaxed text-gray-800 dark:text-gray-200">
                                {{ $d->adres }}
                            </p>
                        </x-filament::section>
                    @endif
                    @if ($d->sevk_adresi)
                        <x-filament::section
                            heading="Sevk adresi"
                            description="Teslimat için kullanılan adres"
                            :icon="Heroicon::OutlinedTruck"
                            icon-color="gray"
                            class="min-w-0 {{ ! $d->adres ? 'cari-span-full' : '' }}"
                        >
                            <p class="whitespace-pre-line text-sm leading-relaxed text-gray-800 dark:text-gray-200">
                                {{ $d->sevk_adresi }}
                            </p>
                        </x-filament::section>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="mx-auto max-w-lg self-start">
            <x-filament::empty-state
                heading="Firma bilgisi bulunamadı"
                description="Oturumunuz bir firmaya bağlı değil veya kayıt yüklenemedi. Sorun devam ederse yöneticinize başvurun."
                :icon="Heroicon::OutlinedExclamationTriangle"
                icon-color="warning"
            />
        </div>
    @endif
</x-filament-panels::page>
