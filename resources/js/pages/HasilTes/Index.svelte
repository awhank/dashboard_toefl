<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import Icon from '@/components/Icon.svelte';
    import { page, router } from '@inertiajs/svelte';

    type SumberInput = 'manual' | 'impor';

    interface JenisTesRingkas {
        id: number;
        kode: string;
        nama: string;
        is_resmi: boolean;
        skor_bagian_min: number;
        skor_bagian_max: number;
        skor_total_min: number;
        skor_total_max: number;
        masa_berlaku_bulan: number | null;
    }

    interface PeriodeOption {
        id: number;
        nama: string;
        tahun: number;
        gelombang: number;
        tanggal_pelaksanaan: string;
        jenis_tes: JenisTesRingkas;
    }

    interface PegawaiOption {
        id: number;
        nomor_pegawai: string;
        nama: string;
        status_aktif: boolean;
    }

    interface HasilTesItem {
        id: number;
        periode_tes_id: number;
        pegawai_id: number;
        skor_listening: number;
        skor_structure: number;
        skor_reading: number;
        skor_total: number;
        tanggal_tes: string;
        berlaku_sampai: string | null;
        no_sertifikat: string | null;
        sumber_input: SumberInput;
        periode_tes: PeriodeOption;
        pegawai: PegawaiOption;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface Props {
        hasilTes: {
            data: HasilTesItem[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
            links: PaginationLink[];
        };
        periodeTes: PeriodeOption[];
        pegawai: PegawaiOption[];
        filters: {
            search: string | null;
            periode_tes_id: string | null;
            sumber_input: string | null;
        };
    }

    let { hasilTes, periodeTes, pegawai, filters }: Props = $props();

    const authUser = $derived(page.props.auth.user);
    const isAdmin = $derived(authUser?.role === 'admin');
    const flash = $derived(page.props.flash);

    let search = $state('');
    let filterPeriodeId = $state('');
    let filterSumber = $state('');

    $effect(() => {
        search = filters.search ?? '';
        filterPeriodeId = filters.periode_tes_id ?? '';
        filterSumber = filters.sumber_input ?? '';
    });

    function applyFilters() {
        router.get(
            '/hasil-tes',
            {
                search: search.trim() || undefined,
                periode_tes_id: filterPeriodeId || undefined,
                sumber_input: filterSumber || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function resetFilters() {
        search = '';
        filterPeriodeId = '';
        filterSumber = '';
        router.get('/hasil-tes', {}, { preserveState: true, replace: true });
    }

    function pad(value: number): string {
        return String(value).padStart(2, '0');
    }

    function todayLocal(): string {
        const now = new Date();

        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    }

    let showFormModal = $state(false);
    let isEditing = $state(false);
    let editingId = $state<number | null>(null);

    let formPeriodeId = $state<number | null>(null);
    let formPegawaiId = $state<number | null>(null);
    let formListening = $state<number | null>(null);
    let formStructure = $state<number | null>(null);
    let formReading = $state<number | null>(null);
    let formTanggal = $state(todayLocal());
    let formNoSertifikat = $state('');
    let formProcessing = $state(false);
    let formErrors = $state<Record<string, string>>({});

    const selectedPeriode = $derived(periodeTes.find((item) => item.id === formPeriodeId) ?? null);
    const skorBagianMin = $derived(selectedPeriode?.jenis_tes.skor_bagian_min ?? 0);
    const skorBagianMax = $derived(selectedPeriode?.jenis_tes.skor_bagian_max ?? 0);
    const skorTotalMin = $derived(selectedPeriode?.jenis_tes.skor_total_min ?? 0);
    const skorTotalMax = $derived(selectedPeriode?.jenis_tes.skor_total_max ?? 0);
    const skorTotal = $derived(
        Math.round((((formListening ?? 0) + (formStructure ?? 0) + (formReading ?? 0)) * 10) / 3),
    );

    function openCreateModal() {
        isEditing = false;
        editingId = null;
        formPeriodeId = periodeTes[0]?.id ?? null;
        formPegawaiId = null;
        formListening = null;
        formStructure = null;
        formReading = null;
        formTanggal = periodeTes[0]?.tanggal_pelaksanaan.slice(0, 10) ?? todayLocal();
        formNoSertifikat = '';
        formErrors = {};
        showFormModal = true;
    }

    function openEditModal(item: HasilTesItem) {
        isEditing = true;
        editingId = item.id;
        formPeriodeId = item.periode_tes_id;
        formPegawaiId = item.pegawai_id;
        formListening = item.skor_listening;
        formStructure = item.skor_structure;
        formReading = item.skor_reading;
        formTanggal = item.tanggal_tes.slice(0, 10);
        formNoSertifikat = item.no_sertifikat ?? '';
        formErrors = {};
        showFormModal = true;
    }

    function handlePeriodeChange() {
        const selected = periodeTes.find((item) => item.id === formPeriodeId);

        if (selected) {
            formTanggal = selected.tanggal_pelaksanaan.slice(0, 10);
        }
    }

    function submitForm(event: Event) {
        event.preventDefault();
        formProcessing = true;
        formErrors = {};

        const payload = {
            periode_tes_id: formPeriodeId,
            pegawai_id: formPegawaiId,
            skor_listening: formListening,
            skor_structure: formStructure,
            skor_reading: formReading,
            tanggal_tes: formTanggal,
            no_sertifikat: formNoSertifikat || null,
        };

        const options = {
            onSuccess: () => {
                showFormModal = false;
            },
            onError: (errors: Record<string, string>) => {
                formErrors = errors;
            },
            onFinish: () => {
                formProcessing = false;
            },
        };

        if (isEditing && editingId) {
            router.put(`/hasil-tes/${editingId}`, payload, options);
        } else {
            router.post('/hasil-tes', payload, options);
        }
    }

    function destroy(item: HasilTesItem) {
        if (confirm(`Hapus hasil tes ${item.pegawai.nama} pada ${item.periode_tes.nama}?`)) {
            router.delete(`/hasil-tes/${item.id}`);
        }
    }

    let showImportModal = $state(false);
    let importPeriodeId = $state<number | null>(null);
    let importFile = $state<File | null>(null);
    let updateExisting = $state(true);
    let importProcessing = $state(false);
    let importErrors = $state<Record<string, string>>({});
    let fileInputRef = $state<HTMLInputElement | null>(null);

    function openImportModal() {
        importPeriodeId = periodeTes[0]?.id ?? null;
        importFile = null;
        updateExisting = true;
        importErrors = {};
        importProcessing = false;
        showImportModal = true;
    }

    function handleFileChange(event: Event) {
        const target = event.target as HTMLInputElement;

        if (target.files && target.files.length > 0) {
            importFile = target.files[0];
            importErrors = {};
        }
    }

    function removeSelectedFile() {
        importFile = null;

        if (fileInputRef) {
            fileInputRef.value = '';
        }
    }

    function submitImport(event: Event) {
        event.preventDefault();
        importErrors = {};

        if (!importPeriodeId) {
            importErrors = { periode_tes_id: 'Pilih periode tes terlebih dahulu.' };

            return;
        }

        if (!importFile) {
            importErrors = { file: 'Silakan pilih berkas Excel (.xlsx / .xls / .csv).' };

            return;
        }

        importProcessing = true;

        const formData = new FormData();
        formData.append('periode_tes_id', String(importPeriodeId));
        formData.append('file', importFile);
        formData.append('update_existing', updateExisting ? '1' : '0');

        router.post('/hasil-tes/import', formData, {
            forceFormData: true,
            onSuccess: () => {
                showImportModal = false;
                importFile = null;
            },
            onError: (errors: Record<string, string>) => {
                importErrors = errors;
            },
            onFinish: () => {
                importProcessing = false;
            },
        });
    }

    const sumberLabels: Record<SumberInput, string> = {
        manual: 'Manual',
        impor: 'Impor',
    };
</script>

<AppHead title="Hasil Tes" />

<AppLayout active="hasil-tes">
    <div class="mx-auto max-w-7xl space-y-6">
        {#if flash?.success}
            <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                <Icon name="check-circle" class="h-5 w-5 shrink-0 text-emerald-600" />
                <span>{flash.success}</span>
            </div>
        {/if}
        {#if flash?.error}
            <div class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <Icon name="alert-triangle" class="h-5 w-5 shrink-0 text-red-600" />
                <span>{flash.error}</span>
            </div>
        {/if}

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-xl text-foreground">Hasil Tes</h2>
                <p class="text-sm text-muted-foreground">
                    Kelola skor TOEFL per pegawai dan periode ({hasilTes.total} hasil tes).
                </p>
            </div>
            {#if isAdmin}
                <div class="flex items-center gap-2">
                    <button
                        onclick={openImportModal}
                        title="Impor Hasil Tes dari Excel"
                        aria-label="Impor Hasil Tes dari Excel"
                        class="flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700"
                    >
                        <Icon name="upload" class="h-4 w-4" />
                        <span class="hidden sm:inline">Impor Excel</span>
                    </button>
                    <button
                        onclick={openCreateModal}
                        class="flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition hover:opacity-90"
                    >
                        <Icon name="plus" class="h-4 w-4" />
                        <span>Tambah Manual</span>
                    </button>
                </div>
            {/if}
        </div>

        <Card class="p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label for="hasil-search" class="mb-1 block text-xs font-medium text-muted-foreground">
                        Cari Pegawai
                    </label>
                    <div class="relative">
                        <Icon name="search" class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground" />
                        <input
                            id="hasil-search"
                            type="text"
                            bind:value={search}
                            onkeydown={(event) => event.key === 'Enter' && applyFilters()}
                            placeholder="Cari nama atau nomor pegawai..."
                            class="w-full rounded-md border border-border py-1.5 pr-3 pl-9 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label for="hasil-periode" class="mb-1 block text-xs font-medium text-muted-foreground">
                        Periode Tes
                    </label>
                    <select
                        id="hasil-periode"
                        bind:value={filterPeriodeId}
                        onchange={applyFilters}
                        class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                    >
                        <option value="">Semua Periode</option>
                        {#each periodeTes as periode (periode.id)}
                            <option value={String(periode.id)}>{periode.nama}</option>
                        {/each}
                    </select>
                </div>

                <div>
                    <label for="hasil-sumber" class="mb-1 block text-xs font-medium text-muted-foreground">
                        Sumber Input
                    </label>
                    <select
                        id="hasil-sumber"
                        bind:value={filterSumber}
                        onchange={applyFilters}
                        class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                    >
                        <option value="">Semua Sumber</option>
                        <option value="manual">Manual</option>
                        <option value="impor">Impor Excel</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        onclick={resetFilters}
                        class="w-full rounded-md border border-border bg-muted/40 px-3 py-1.5 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
        </Card>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30 text-xs uppercase text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-semibold">Pegawai</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Periode Tes</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tanggal Tes</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Listening</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Structure</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Reading</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Total</th>
                            <th scope="col" class="px-4 py-3 font-semibold">No. Sertifikat</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Berlaku s.d.</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Sumber</th>
                            {#if isAdmin}
                                <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                            {/if}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {#if hasilTes.data.length === 0}
                            <tr>
                                <td colspan={isAdmin ? 11 : 10} class="py-12 text-center text-muted-foreground">
                                    Tidak ada hasil tes yang sesuai dengan filter.
                                </td>
                            </tr>
                        {:else}
                            {#each hasilTes.data as item (item.id)}
                                <tr class="transition-colors hover:bg-muted/20">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-foreground">{item.pegawai.nama}</div>
                                        <div class="font-mono text-xs text-muted-foreground">
                                            {item.pegawai.nomor_pegawai}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-foreground">{item.periode_tes.nama}</div>
                                        <div class="mt-1">
                                            <Badge tone={item.periode_tes.jenis_tes.is_resmi ? 'primary' : 'muted'}>
                                                {item.periode_tes.jenis_tes.kode}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                        {item.tanggal_tes.slice(0, 10)}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-foreground">
                                        {item.skor_listening}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-foreground">
                                        {item.skor_structure}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-foreground">
                                        {item.skor_reading}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold text-foreground">
                                        {item.skor_total}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        {item.no_sertifikat ?? '-'}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                        {item.berlaku_sampai ? item.berlaku_sampai.slice(0, 10) : '-'}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge tone={item.sumber_input === 'impor' ? 'primary' : 'muted'}>
                                            {sumberLabels[item.sumber_input]}
                                        </Badge>
                                    </td>
                                    {#if isAdmin}
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    onclick={() => openEditModal(item)}
                                                    title="Ubah Hasil Tes"
                                                    aria-label="Ubah Hasil Tes"
                                                    class="rounded p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                                >
                                                    <Icon name="pencil" class="h-4 w-4" />
                                                </button>
                                                <button
                                                    onclick={() => destroy(item)}
                                                    title="Hapus Hasil Tes"
                                                    aria-label="Hapus Hasil Tes"
                                                    class="rounded p-1.5 text-muted-foreground transition hover:bg-red-50 hover:text-red-600"
                                                >
                                                    <Icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    {/if}
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>

            {#if hasilTes.total > 0}
                <div class="flex flex-col gap-3 border-t border-border px-4 py-3 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        Menampilkan <span class="font-medium text-foreground">{hasilTes.from ?? 0}</span> sampai
                        <span class="font-medium text-foreground">{hasilTes.to ?? 0}</span> dari
                        <span class="font-medium text-foreground">{hasilTes.total}</span> hasil tes
                    </div>
                    <div class="flex items-center gap-1">
                        {#each hasilTes.links as link}
                            {#if link.url}
                                <a
                                    href={link.url}
                                    class="rounded px-2.5 py-1 text-xs font-medium transition
                                    {link.active
                                        ? 'bg-primary font-semibold text-primary-foreground'
                                        : 'text-foreground/80 hover:bg-muted'}"
                                >
                                    {#if link.label.includes('Previous')}
                                        <span class="flex items-center gap-1">
                                            <Icon name="chevron-left" class="h-3 w-3" /> Prev
                                        </span>
                                    {:else if link.label.includes('Next')}
                                        <span class="flex items-center gap-1">
                                            Next <Icon name="chevron-right" class="h-3 w-3" />
                                        </span>
                                    {:else}
                                        {link.label}
                                    {/if}
                                </a>
                            {:else}
                                <span class="px-2 py-1 text-muted-foreground/50 opacity-60">
                                    {#if link.label.includes('Previous')}
                                        <span class="flex items-center gap-1">
                                            <Icon name="chevron-left" class="h-3 w-3" /> Prev
                                        </span>
                                    {:else if link.label.includes('Next')}
                                        <span class="flex items-center gap-1">
                                            Next <Icon name="chevron-right" class="h-3 w-3" />
                                        </span>
                                    {:else}
                                        {link.label}
                                    {/if}
                                </span>
                            {/if}
                        {/each}
                    </div>
                </div>
            {/if}
        </Card>
    </div>

    {#if showFormModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs">
            <div
                class="relative w-full max-w-2xl rounded-xl border border-border bg-white p-6 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="hasil-tes-modal-title"
            >
                <div class="mb-4 flex items-center justify-between border-b border-border pb-3">
                    <h3 id="hasil-tes-modal-title" class="font-heading text-lg text-foreground">
                        {isEditing ? 'Ubah Hasil Tes' : 'Tambah Hasil Tes Manual'}
                    </h3>
                    <button
                        onclick={() => (showFormModal = false)}
                        class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                        aria-label="Tutup"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form onsubmit={submitForm} class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-periode-tes" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Periode Tes *
                            </label>
                            <select
                                id="form-periode-tes"
                                bind:value={formPeriodeId}
                                onchange={handlePeriodeChange}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value={null} disabled>Pilih periode tes</option>
                                {#each periodeTes as periode (periode.id)}
                                    <option value={periode.id}>
                                        {periode.nama} ({periode.jenis_tes.kode})
                                    </option>
                                {/each}
                            </select>
                            {#if formErrors.periode_tes_id}
                                <p class="mt-1 text-xs text-red-600">{formErrors.periode_tes_id}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-pegawai" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Pegawai *
                            </label>
                            <select
                                id="form-pegawai"
                                bind:value={formPegawaiId}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value={null} disabled>Pilih pegawai</option>
                                {#each pegawai as item (item.id)}
                                    <option value={item.id}>
                                        {item.nama} - {item.nomor_pegawai}{item.status_aktif ? '' : ' (nonaktif)'}
                                    </option>
                                {/each}
                            </select>
                            {#if formErrors.pegawai_id}
                                <p class="mt-1 text-xs text-red-600">{formErrors.pegawai_id}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label for="form-listening" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Listening *
                            </label>
                            <input
                                id="form-listening"
                                type="number"
                                min={skorBagianMin}
                                max={skorBagianMax}
                                bind:value={formListening}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_listening}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_listening}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-structure" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Structure *
                            </label>
                            <input
                                id="form-structure"
                                type="number"
                                min={skorBagianMin}
                                max={skorBagianMax}
                                bind:value={formStructure}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_structure}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_structure}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-reading" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Reading *
                            </label>
                            <input
                                id="form-reading"
                                type="number"
                                min={skorBagianMin}
                                max={skorBagianMax}
                                bind:value={formReading}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_reading}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_reading}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-brand-teal/20 bg-brand-teal/5 p-3">
                        <div>
                            <p class="text-xs font-medium text-foreground">Skor Total (otomatis)</p>
                            <p class="text-xs text-muted-foreground">
                                Rentang {selectedPeriode ? `${skorTotalMin}–${skorTotalMax}` : '-'}
                            </p>
                        </div>
                        <span class="font-heading text-2xl text-primary">{skorTotal}</span>
                    </div>

                    {#if selectedPeriode && !selectedPeriode.jenis_tes.is_resmi}
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                            Jenis tes ini non-resmi. Hasilnya tidak dihitung sebagai sertifikat TOEFL ITP.
                        </div>
                    {/if}

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-tanggal-tes" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Tanggal Tes *
                            </label>
                            <input
                                id="form-tanggal-tes"
                                type="date"
                                bind:value={formTanggal}
                                required
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.tanggal_tes}
                                <p class="mt-1 text-xs text-red-600">{formErrors.tanggal_tes}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-no-sertifikat" class="mb-1 block text-xs font-medium text-muted-foreground">
                                No. Sertifikat
                            </label>
                            <input
                                id="form-no-sertifikat"
                                type="text"
                                bind:value={formNoSertifikat}
                                maxlength="50"
                                placeholder="Opsional"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.no_sertifikat}
                                <p class="mt-1 text-xs text-red-600">{formErrors.no_sertifikat}</p>
                            {/if}
                        </div>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Masa berlaku sertifikat dihitung otomatis oleh sistem berdasarkan jenis tes dan tanggal tes.
                    </p>

                    <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                        <button
                            type="button"
                            onclick={() => (showFormModal = false)}
                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-muted"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={formProcessing}
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50"
                        >
                            {formProcessing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Hasil Tes'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}

    {#if showImportModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs">
            <div
                class="relative w-full max-w-lg rounded-xl border border-border bg-white p-6 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="hasil-import-modal-title"
            >
                <div class="mb-4 flex items-center justify-between border-b border-border pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <Icon name="upload" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 id="hasil-import-modal-title" class="font-heading text-lg text-foreground">
                                Impor Hasil Tes
                            </h3>
                            <p class="text-xs text-muted-foreground">Upload berkas Excel hasil tes per periode</p>
                        </div>
                    </div>
                    <button
                        onclick={() => (showImportModal = false)}
                        class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                        aria-label="Tutup"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form onsubmit={submitImport} class="space-y-4">
                    <div>
                        <label for="import-periode" class="mb-1 block text-xs font-medium text-muted-foreground">
                            Periode Tes *
                        </label>
                        <select
                            id="import-periode"
                            bind:value={importPeriodeId}
                            required
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        >
                            <option value={null} disabled>Pilih periode tes</option>
                            {#each periodeTes as periode (periode.id)}
                                <option value={periode.id}>{periode.nama}</option>
                            {/each}
                        </select>
                        {#if importErrors.periode_tes_id}
                            <p class="mt-1 text-xs text-red-600">{importErrors.periode_tes_id}</p>
                        {/if}
                    </div>

                    <div>
                        <span class="mb-1.5 block text-xs font-medium text-muted-foreground">
                            Berkas Excel (.xlsx / .xls / .csv) *
                        </span>
                        {#if !importFile}
                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-border bg-muted/10 p-6 text-center transition hover:border-brand-teal hover:bg-brand-teal/5"
                            >
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <Icon name="file-spreadsheet" class="h-5 w-5" />
                                </div>
                                <span class="text-xs font-medium text-foreground">
                                    Klik untuk memilih berkas hasil tes
                                </span>
                                <span class="mt-1 text-[11px] text-muted-foreground">
                                    Format: Microsoft Excel (.xlsx, .xls) atau .csv (Maksimal 10MB)
                                </span>
                                <input
                                    bind:this={fileInputRef}
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    onchange={handleFileChange}
                                    class="hidden"
                                />
                            </label>
                        {:else}
                            <div class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50/50 p-3">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <Icon name="file-spreadsheet" class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-medium text-foreground">{importFile.name}</p>
                                        <p class="text-[11px] text-muted-foreground">
                                            {(importFile.size / 1024).toFixed(1)} KB
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onclick={removeSelectedFile}
                                    class="rounded p-1 text-muted-foreground transition hover:bg-red-50 hover:text-red-600"
                                    title="Hapus berkas"
                                    aria-label="Hapus berkas"
                                >
                                    <Icon name="x" class="h-4 w-4" />
                                </button>
                            </div>
                        {/if}
                        {#if importErrors.file}
                            <p class="mt-1.5 text-xs text-red-600">{importErrors.file}</p>
                        {/if}
                    </div>

                    <label class="flex items-start gap-2.5 rounded-lg border border-border p-3 text-xs hover:bg-muted/20">
                        <input
                            type="checkbox"
                            bind:checked={updateExisting}
                            class="mt-0.5 rounded border-border text-emerald-600 focus:ring-emerald-500"
                        />
                        <span>
                            <span class="block font-medium text-foreground">Perbarui hasil yang sudah ada</span>
                            <span class="text-muted-foreground">
                                Jika dicentang, hasil pegawai yang sudah ada pada periode ini akan diperbarui.
                            </span>
                        </span>
                    </label>

                    <div class="rounded-md border border-brand-teal/20 bg-brand-teal/5 p-3 text-xs text-muted-foreground">
                        Hasil yang diimpor akan ditandai dengan sumber <strong>Impor Excel</strong>. Skor total dan
                        masa berlaku dihitung ulang oleh sistem.
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                        <button
                            type="button"
                            onclick={() => (showImportModal = false)}
                            disabled={importProcessing}
                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-muted disabled:opacity-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={importProcessing || !importFile}
                            class="flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {#if importProcessing}
                                <Icon name="rotate-ccw" class="h-4 w-4 animate-spin" />
                                <span>Memproses...</span>
                            {:else}
                                <Icon name="upload" class="h-4 w-4" />
                                <span>Mulai Import</span>
                            {/if}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</AppLayout>
