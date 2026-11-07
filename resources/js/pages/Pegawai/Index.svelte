<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import Icon from '@/components/Icon.svelte';
    import { page, router } from '@inertiajs/svelte';

    interface PegawaiItem {
        id: number;
        nomor_pegawai: string;
        jenis_nomor: 'nip' | 'nipppk' | 'internal';
        nama: string;
        jenis_pegawai: 'dosen' | 'tendik';
        jenis_kelamin: 'L' | 'P' | null;
        tanggal_lahir: string | null;
        jabatan: string | null;
        status_aktif: boolean;
        tanggal_nonaktif: string | null;
        keterangan_nonaktif: string | null;
        user?: {
            id: number;
            username: string;
            role: string;
            is_active: boolean;
            password_changed_at: string | null;
        } | null;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface Props {
        pegawai: {
            data: PegawaiItem[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
            links: PaginationLink[];
        };
        filters: {
            search: string | null;
            jenis: string | null;
            status: string | null;
        };
    }

    let { pegawai, filters }: Props = $props();

    const authUser = $derived(page.props.auth.user);
    const isAdmin = $derived(authUser?.role === 'admin');
    const flash = $derived(page.props.flash);

    // Filter states
    let search = $state('');
    let jenis = $state('');
    let status = $state('');

    $effect(() => {
        search = filters.search ?? '';
        jenis = filters.jenis ?? '';
        status = filters.status ?? '';
    });

    function applyFilters() {
        router.get(
            '/pegawai',
            {
                search: search.trim() || undefined,
                jenis: jenis || undefined,
                status: status || undefined,
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
        jenis = '';
        status = '';
        router.get('/pegawai', {}, { preserveState: true, replace: true });
    }

    // Modal Form Tambah / Edit
    let showFormModal = $state(false);
    let isEditing = $state(false);
    let editingId = $state<number | null>(null);

    let formNomorPegawai = $state('');
    let formJenisNomor = $state<'nip' | 'nipppk' | 'internal'>('nip');
    let formNama = $state('');
    let formJenisPegawai = $state<'dosen' | 'tendik'>('dosen');
    let formJenisKelamin = $state<'L' | 'P' | ''>('');
    let formTanggalLahir = $state('');
    let formJabatan = $state('');
    let formProcessing = $state(false);
    let formErrors = $state<Record<string, string>>({});

    function openCreateModal() {
        isEditing = false;
        editingId = null;
        formNomorPegawai = '';
        formJenisNomor = 'nip';
        formNama = '';
        formJenisPegawai = 'dosen';
        formJenisKelamin = '';
        formTanggalLahir = '';
        formJabatan = '';
        formErrors = {};
        showFormModal = true;
    }

    function openEditModal(item: PegawaiItem) {
        isEditing = true;
        editingId = item.id;
        formNomorPegawai = item.nomor_pegawai;
        formJenisNomor = item.jenis_nomor;
        formNama = item.nama;
        formJenisPegawai = item.jenis_pegawai;
        formJenisKelamin = item.jenis_kelamin ?? '';
        formTanggalLahir = item.tanggal_lahir ? item.tanggal_lahir.slice(0, 10) : '';
        formJabatan = item.jabatan ?? '';
        formErrors = {};
        showFormModal = true;
    }

    function submitForm(e: Event) {
        e.preventDefault();
        formProcessing = true;
        formErrors = {};

        const payload = {
            nomor_pegawai: formNomorPegawai,
            jenis_nomor: formJenisNomor,
            nama: formNama,
            jenis_pegawai: formJenisPegawai,
            jenis_kelamin: formJenisKelamin || null,
            tanggal_lahir: formTanggalLahir || null,
            jabatan: formJabatan || null,
        };

        if (isEditing && editingId) {
            router.put(`/pegawai/${editingId}`, payload, {
                onSuccess: () => {
                    showFormModal = false;
                },
                onError: (errors) => {
                    formErrors = errors;
                },
                onFinish: () => {
                    formProcessing = false;
                },
            });
        } else {
            router.post('/pegawai', payload, {
                onSuccess: () => {
                    showFormModal = false;
                },
                onError: (errors) => {
                    formErrors = errors;
                },
                onFinish: () => {
                    formProcessing = false;
                },
            });
        }
    }

    // Modal Nonaktifkan Pegawai
    let showNonaktifModal = $state(false);
    let nonaktifTarget = $state<PegawaiItem | null>(null);
    let formTanggalNonaktif = $state(new Date().toISOString().slice(0, 10));
    let formKeteranganNonaktif = $state('');
    let nonaktifProcessing = $state(false);
    let nonaktifErrors = $state<Record<string, string>>({});

    function openNonaktifModal(item: PegawaiItem) {
        nonaktifTarget = item;
        formTanggalNonaktif = new Date().toISOString().slice(0, 10);
        formKeteranganNonaktif = '';
        nonaktifErrors = {};
        showNonaktifModal = true;
    }

    function submitNonaktif(e: Event) {
        e.preventDefault();
        if (!nonaktifTarget) return;

        nonaktifProcessing = true;
        nonaktifErrors = {};

        router.post(
            `/pegawai/${nonaktifTarget.id}/nonaktifkan`,
            {
                tanggal_nonaktif: formTanggalNonaktif,
                keterangan_nonaktif: formKeteranganNonaktif,
            },
            {
                onSuccess: () => {
                    showNonaktifModal = false;
                },
                onError: (errors) => {
                    nonaktifErrors = errors;
                },
                onFinish: () => {
                    nonaktifProcessing = false;
                },
            },
        );
    }

    function toggleAktifkan(item: PegawaiItem) {
        if (confirm(`Aktifkan kembali pegawai ${item.nama}? Akun login juga akan diaktifkan kembali.`)) {
            router.post(`/pegawai/${item.id}/aktifkan`);
        }
    }

    function triggerResetPassword(item: PegawaiItem) {
        if (
            confirm(
                `Reset password akun pegawai ${item.nama}?\n\nPassword akan dikembalikan ke nomor pegawai: ${item.nomor_pegawai}`,
            )
        ) {
            router.post(`/pegawai/${item.id}/reset-password`);
        }
    }

    // Modal Import Pegawai Excel
    let showImportModal = $state(false);
    let importFile = $state<File | null>(null);
    let updateExisting = $state(true);
    let importProcessing = $state(false);
    let importErrors = $state<Record<string, string>>({});
    let fileInputRef = $state<HTMLInputElement | null>(null);

    function openImportModal() {
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

    function submitImport(e: Event) {
        e.preventDefault();
        if (!importFile) {
            importErrors = { file: 'Silakan pilih berkas Excel (.xlsx / .xls / .csv) terlebih dahulu.' };
            return;
        }

        importProcessing = true;
        importErrors = {};

        const formData = new FormData();
        formData.append('file', importFile);
        formData.append('update_existing', updateExisting ? '1' : '0');

        router.post('/pegawai/import', formData, {
            forceFormData: true,
            onSuccess: () => {
                showImportModal = false;
                importFile = null;
            },
            onError: (errors) => {
                importErrors = errors;
            },
            onFinish: () => {
                importProcessing = false;
            },
        });
    }

    const jenisNomorLabels: Record<string, string> = {
        nip: 'NIP',
        nipppk: 'NIPPPK',
        internal: 'Internal',
    };
</script>

<AppHead title="Data Pegawai" />

<AppLayout active="pegawai">
    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Flash Alert -->
        {#if flash?.success}
            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                <Icon name="check-circle" class="h-5 w-5 text-emerald-600 shrink-0" />
                <span>{flash.success}</span>
            </div>
        {/if}
        {#if flash?.error}
            <div class="flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                <Icon name="alert-triangle" class="h-5 w-5 text-red-600 shrink-0" />
                <span>{flash.error}</span>
            </div>
        {/if}

        <!-- Top Bar: Judul & Tombol Aksi -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-xl text-foreground">Data Pegawai</h2>
                <p class="text-sm text-muted-foreground">
                    Kelola data Dosen dan Tenaga Kependidikan Poltekkes Banjarmasin ({pegawai.total} pegawai terdaftar).
                </p>
            </div>
            {#if isAdmin}
                <div class="flex items-center gap-2">
                    <button
                        onclick={openCreateModal}
                        title="Tambah Pegawai"
                        aria-label="Tambah Pegawai"
                        class="flex h-9 w-9 items-center justify-center rounded-md bg-primary text-primary-foreground shadow-sm transition hover:opacity-90"
                    >
                        <Icon name="plus" class="h-5 w-5" />
                    </button>
                    <button
                        onclick={openImportModal}
                        title="Import Pegawai dari Excel"
                        aria-label="Import Pegawai dari Excel"
                        class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-700"
                    >
                        <Icon name="upload" class="h-5 w-5" />
                    </button>
                </div>
            {/if}
        </div>

        <!-- Filter Bar -->
        <Card class="p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                <div class="sm:col-span-2">
                    <label for="search-input" class="block text-xs font-medium text-muted-foreground mb-1">Cari Pegawai</label>
                    <div class="relative">
                        <Icon name="search" class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <input
                            id="search-input"
                            type="text"
                            bind:value={search}
                            onkeydown={(e) => e.key === 'Enter' && applyFilters()}
                            placeholder="Cari nama, NIP, atau jabatan..."
                            class="w-full rounded-md border border-border pl-9 pr-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label for="filter-jenis" class="block text-xs font-medium text-muted-foreground mb-1">Kategori</label>
                    <select
                        id="filter-jenis"
                        bind:value={jenis}
                        onchange={applyFilters}
                        class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                    >
                        <option value="">Semua Kategori</option>
                        <option value="dosen">Dosen</option>
                        <option value="tendik">Tendik</option>
                    </select>
                </div>

                <div>
                    <label for="filter-status" class="block text-xs font-medium text-muted-foreground mb-1">Status Kepegawaian</label>
                    <select
                        id="filter-status"
                        bind:value={status}
                        onchange={applyFilters}
                        class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        onclick={resetFilters}
                        class="w-full rounded-md border border-border bg-muted/40 px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
        </Card>

        <!-- Tabel Pegawai -->
        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30 text-xs text-muted-foreground uppercase">
                            <th class="py-3 px-4 font-semibold">Nomor Identitas</th>
                            <th class="py-3 px-4 font-semibold">Nama Lengkap</th>
                            <th class="py-3 px-4 font-semibold">Kategori</th>
                            <th class="py-3 px-4 font-semibold">L/P & Tgl Lahir</th>
                            <th class="py-3 px-4 font-semibold">Jabatan</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            {#if isAdmin}
                                <th class="py-3 px-4 text-right font-semibold">Aksi</th>
                            {/if}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {#if pegawai.data.length === 0}
                            <tr>
                                <td colspan={isAdmin ? 7 : 6} class="py-12 text-center text-muted-foreground">
                                    Tidak ada data pegawai yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        {:else}
                            {#each pegawai.data as item (item.id)}
                                <tr class="hover:bg-muted/20 transition-colors">
                                    <td class="py-3 px-4 font-mono text-xs text-foreground">
                                        <div class="font-medium text-foreground">{item.nomor_pegawai}</div>
                                        <span class="text-[10px] text-muted-foreground uppercase">{jenisNomorLabels[item.jenis_nomor] ?? item.jenis_nomor}</span>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-foreground">
                                        {item.nama}
                                    </td>
                                    <td class="py-3 px-4">
                                        {#if item.jenis_pegawai === 'dosen'}
                                            <Badge tone="primary">Dosen</Badge>
                                        {:else}
                                            <Badge tone="muted">Tendik</Badge>
                                        {/if}
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">
                                        <div>{item.jenis_kelamin ? (item.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-'}</div>
                                        {#if item.tanggal_lahir}
                                            <div class="text-xs text-muted-foreground/80">{item.tanggal_lahir.slice(0, 10)}</div>
                                        {/if}
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">
                                        {item.jabatan ?? '-'}
                                    </td>
                                    <td class="py-3 px-4">
                                        {#if item.status_aktif}
                                            <Badge tone="ok">Aktif</Badge>
                                        {:else}
                                            <div class="space-y-0.5">
                                                <Badge tone="danger">Nonaktif</Badge>
                                                {#if item.keterangan_nonaktif}
                                                    <p class="text-[11px] text-muted-foreground">{item.keterangan_nonaktif}</p>
                                                {/if}
                                            </div>
                                        {/if}
                                    </td>
                                    {#if isAdmin}
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <!-- Edit -->
                                                <button
                                                    onclick={() => openEditModal(item)}
                                                    title="Ubah Data"
                                                    class="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition"
                                                >
                                                    <Icon name="pencil" class="h-4 w-4" />
                                                </button>

                                                <!-- Reset Password -->
                                                <button
                                                    onclick={() => triggerResetPassword(item)}
                                                    title="Reset Password Akun ke Default"
                                                    class="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-amber-600 transition"
                                                >
                                                    <Icon name="key" class="h-4 w-4" />
                                                </button>

                                                <!-- Toggle Aktif/Nonaktif -->
                                                {#if item.status_aktif}
                                                    <button
                                                        onclick={() => openNonaktifModal(item)}
                                                        title="Nonaktifkan Pegawai"
                                                        class="rounded p-1.5 text-muted-foreground hover:bg-red-50 hover:text-red-600 transition"
                                                    >
                                                        <Icon name="power" class="h-4 w-4" />
                                                    </button>
                                                {:else}
                                                    <button
                                                        onclick={() => toggleAktifkan(item)}
                                                        title="Aktifkan Kembali Pegawai"
                                                        class="rounded p-1.5 text-muted-foreground hover:bg-emerald-50 hover:text-emerald-600 transition"
                                                    >
                                                        <Icon name="rotate-ccw" class="h-4 w-4" />
                                                    </button>
                                                {/if}
                                            </div>
                                        </td>
                                    {/if}
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            {#if pegawai.total > 0}
                <div class="flex flex-col gap-3 border-t border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between text-xs text-muted-foreground">
                    <div>
                        Menampilkan <span class="font-medium text-foreground">{pegawai.from ?? 0}</span> sampai{' '}
                        <span class="font-medium text-foreground">{pegawai.to ?? 0}</span> dari{' '}
                        <span class="font-medium text-foreground">{pegawai.total}</span> data pegawai
                    </div>
                    <div class="flex items-center gap-1">
                        {#each pegawai.links as link}
                            {#if link.url}
                                <a
                                    href={link.url}
                                    class="rounded px-2.5 py-1 text-xs font-medium transition
                                    {link.active
                                        ? 'bg-primary text-primary-foreground font-semibold'
                                        : 'text-foreground/80 hover:bg-muted'}"
                                >
                                    <!-- Render label HTML entity like &laquo; &raquo; secara aman -->
                                    {#if link.label.includes('Previous')}
                                        <span class="flex items-center gap-1"><Icon name="chevron-left" class="h-3 w-3" /> Prev</span>
                                    {:else if link.label.includes('Next')}
                                        <span class="flex items-center gap-1">Next <Icon name="chevron-right" class="h-3 w-3" /></span>
                                    {:else}
                                        {link.label}
                                    {/if}
                                </a>
                            {:else}
                                <span class="px-2 py-1 text-muted-foreground/50 opacity-60">
                                    {#if link.label.includes('Previous')}
                                        <span class="flex items-center gap-1"><Icon name="chevron-left" class="h-3 w-3" /> Prev</span>
                                    {:else if link.label.includes('Next')}
                                        <span class="flex items-center gap-1">Next <Icon name="chevron-right" class="h-3 w-3" /></span>
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

    <!-- Modal Form Tambah / Edit Pegawai -->
    {#if showFormModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl border border-border">
                <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                    <h3 class="font-heading text-lg text-foreground">
                        {isEditing ? 'Ubah Data Pegawai' : 'Tambah Pegawai Baru'}
                    </h3>
                    <button
                        onclick={() => (showFormModal = false)}
                        class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form onsubmit={submitForm} class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label for="form-jenis-nomor" class="block text-xs font-medium text-muted-foreground mb-1">Jenis Nomor *</label>
                            <select
                                id="form-jenis-nomor"
                                bind:value={formJenisNomor}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value="nip">NIP (PNS)</option>
                                <option value="nipppk">NIPPPK</option>
                                <option value="internal">Internal / Kontrak</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="form-nomor-pegawai" class="block text-xs font-medium text-muted-foreground mb-1">Nomor Pegawai *</label>
                            <input
                                id="form-nomor-pegawai"
                                type="text"
                                bind:value={formNomorPegawai}
                                placeholder="Contoh: 198001012005011001"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.nomor_pegawai}
                                <p class="mt-1 text-xs text-red-600">{formErrors.nomor_pegawai}</p>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <label for="form-nama" class="block text-xs font-medium text-muted-foreground mb-1">Nama Lengkap (beserta gelar) *</label>
                        <input
                            id="form-nama"
                            type="text"
                            bind:value={formNama}
                            placeholder="Contoh: Dr. Supriadi, S.Kep., Ns., M.Kep."
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                        {#if formErrors.nama}
                            <p class="mt-1 text-xs text-red-600">{formErrors.nama}</p>
                        {/if}
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-jenis-pegawai" class="block text-xs font-medium text-muted-foreground mb-1">Kategori Pegawai *</label>
                            <select
                                id="form-jenis-pegawai"
                                bind:value={formJenisPegawai}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value="dosen">Dosen</option>
                                <option value="tendik">Tenaga Kependidikan (Tendik)</option>
                            </select>
                            {#if formErrors.jenis_pegawai}
                                <p class="mt-1 text-xs text-red-600">{formErrors.jenis_pegawai}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-jenis-kelamin" class="block text-xs font-medium text-muted-foreground mb-1">Jenis Kelamin</label>
                            <select
                                id="form-jenis-kelamin"
                                bind:value={formJenisKelamin}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value="">- Pilih Jenis Kelamin -</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                            {#if formErrors.jenis_kelamin}
                                <p class="mt-1 text-xs text-red-600">{formErrors.jenis_kelamin}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-tanggal-lahir" class="block text-xs font-medium text-muted-foreground mb-1">Tanggal Lahir</label>
                            <input
                                id="form-tanggal-lahir"
                                type="date"
                                bind:value={formTanggalLahir}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.tanggal_lahir}
                                <p class="mt-1 text-xs text-red-600">{formErrors.tanggal_lahir}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-jabatan" class="block text-xs font-medium text-muted-foreground mb-1">Jabatan</label>
                            <input
                                id="form-jabatan"
                                type="text"
                                bind:value={formJabatan}
                                placeholder="Contoh: Lektor / Staf Kepegawaian"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.jabatan}
                                <p class="mt-1 text-xs text-red-600">{formErrors.jabatan}</p>
                            {/if}
                        </div>
                    </div>

                    {#if !isEditing}
                        <div class="rounded-md bg-brand-teal/5 border border-brand-teal/20 p-3 text-xs text-muted-foreground">
                            <p class="font-medium text-foreground">💡 Informasi Akun Login:</p>
                            <p>Akun login pegawai akan otomatis dibuat dengan <strong>username</strong> dan <strong>password default</strong> sama dengan nomor pegawai.</p>
                        </div>
                    {/if}

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
                            {formProcessing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Pegawai')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}

    <!-- Modal Konfirmasi Nonaktifkan Pegawai -->
    {#if showNonaktifModal && nonaktifTarget}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl border border-border">
                <div class="flex items-center gap-3 border-b border-border pb-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 shrink-0">
                        <Icon name="power" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-heading text-base text-foreground">Nonaktifkan Pegawai</h3>
                        <p class="text-xs text-muted-foreground">{nonaktifTarget.nama} ({nonaktifTarget.nomor_pegawai})</p>
                    </div>
                </div>

                <form onsubmit={submitNonaktif} class="space-y-4">
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Pegawai yang dinonaktifkan tidak akan dihitung di Dashboard pegawai aktif, dan akun login-nya akan dinonaktifkan. Seluruh riwayat tes TOEFL sebelumnya tetap tersimpan.
                    </p>

                    <div>
                        <label for="nonaktif-tanggal" class="block text-xs font-medium text-muted-foreground mb-1">Tanggal Nonaktif *</label>
                        <input
                            id="nonaktif-tanggal"
                            type="date"
                            bind:value={formTanggalNonaktif}
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                        {#if nonaktifErrors.tanggal_nonaktif}
                            <p class="mt-1 text-xs text-red-600">{nonaktifErrors.tanggal_nonaktif}</p>
                        {/if}
                    </div>

                    <div>
                        <label for="nonaktif-keterangan" class="block text-xs font-medium text-muted-foreground mb-1">Alasan / Keterangan *</label>
                        <textarea
                            id="nonaktif-keterangan"
                            bind:value={formKeteranganNonaktif}
                            rows="2"
                            placeholder="Contoh: Pensiun / Pindah Satuan Kerja / Tugas Belajar Luar Kota"
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        ></textarea>
                        {#if nonaktifErrors.keterangan_nonaktif}
                            <p class="mt-1 text-xs text-red-600">{nonaktifErrors.keterangan_nonaktif}</p>
                        {/if}
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                        <button
                            type="button"
                            onclick={() => (showNonaktifModal = false)}
                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-muted"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={nonaktifProcessing}
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            {nonaktifProcessing ? 'Memproses...' : 'Nonaktifkan Pegawai'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}

    <!-- Modal Import Pegawai dari Excel -->
    {#if showImportModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl border border-border">
                <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <Icon name="upload" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="font-heading text-lg text-foreground">Import Data Pegawai</h3>
                            <p class="text-xs text-muted-foreground">Upload berkas Excel daftar pegawai Poltekkes</p>
                        </div>
                    </div>
                    <button
                        onclick={() => (showImportModal = false)}
                        class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form onsubmit={submitImport} class="space-y-4">
                    <!-- Template Download Info Card -->
                    <div class="flex items-start justify-between gap-3 rounded-lg border border-border bg-muted/30 p-3.5 text-xs">
                        <div class="space-y-1">
                            <p class="font-medium text-foreground">Format yang Didukung:</p>
                            <p class="text-muted-foreground leading-relaxed">
                                Format <strong>Laporan DUK Kemenkes</strong> (contoh: <code>daftar_pegawai.xlsx</code>) atau format <strong>Template Standar</strong>.
                            </p>
                        </div>
                        <a
                            href="/pegawai/template-import"
                            download
                            class="flex shrink-0 items-center gap-1.5 rounded-md border border-border bg-white px-3 py-1.5 text-xs font-medium text-primary hover:bg-muted transition"
                        >
                            <Icon name="download" class="h-3.5 w-3.5" />
                            Template
                        </a>
                    </div>

                    <!-- File Dropzone -->
                    <div>
                        <span class="block text-xs font-medium text-muted-foreground mb-1.5">Berkas Excel (.xlsx / .xls / .csv) *</span>
                        {#if !importFile}
                            <label
                                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border bg-muted/10 p-6 text-center cursor-pointer hover:border-brand-teal hover:bg-brand-teal/5 transition"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-2">
                                    <Icon name="file-spreadsheet" class="h-5 w-5" />
                                </div>
                                <span class="text-xs font-medium text-foreground">
                                    Klik untuk memilih berkas atau seret file ke sini
                                </span>
                                <span class="text-[11px] text-muted-foreground mt-1">
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
                                        <p class="text-[11px] text-muted-foreground">{(importFile.size / 1024).toFixed(1)} KB</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onclick={removeSelectedFile}
                                    class="rounded p-1 text-muted-foreground hover:bg-red-50 hover:text-red-600 transition"
                                    title="Hapus berkas"
                                >
                                    <Icon name="x" class="h-4 w-4" />
                                </button>
                            </div>
                        {/if}

                        {#if importErrors.file}
                            <p class="mt-1.5 text-xs text-red-600">{importErrors.file}</p>
                        {/if}
                    </div>

                    <!-- Opsi Update Existing -->
                    <label class="flex items-start gap-2.5 rounded-lg border border-border p-3 text-xs cursor-pointer hover:bg-muted/20">
                        <input
                            type="checkbox"
                            bind:checked={updateExisting}
                            class="mt-0.5 rounded border-border text-emerald-600 focus:ring-emerald-500"
                        />
                        <div class="space-y-0.5">
                            <span class="font-medium text-foreground">Perbarui data jika nomor pegawai (NIP) sudah ada</span>
                            <p class="text-muted-foreground leading-relaxed">
                                Jika dicentang, baris data dengan NIP yang sudah terdaftar akan diperbarui informasi nama/jabatannya. Jika tidak, baris tersebut akan dilewati.
                            </p>
                        </div>
                    </label>

                    <!-- Info Otomasi Akun -->
                    <div class="rounded-md bg-brand-teal/5 border border-brand-teal/20 p-3 text-xs text-muted-foreground">
                        <p class="font-medium text-foreground">💡 Akun Login Otomatis:</p>
                        <p>Setiap pegawai baru yang berhasil diimpor otomatis dibuatkan akun login dengan <strong>username</strong> &amp; <strong>password default</strong> sama dengan nomor pegawai.</p>
                    </div>

                    <!-- Footer Action Buttons -->
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
                            class="flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50 shadow-sm transition"
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
