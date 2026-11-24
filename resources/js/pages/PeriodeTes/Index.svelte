<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import Icon from '@/components/Icon.svelte';
    import { page, router } from '@inertiajs/svelte';

    interface JenisTesItem {
        id: number;
        kode: string;
        nama: string;
        is_resmi: boolean;
    }

    interface PeriodeItem {
        id: number;
        jenis_tes_id: number;
        tahun: number;
        gelombang: number;
        nama: string;
        tanggal_pelaksanaan: string;
        penyelenggara: string | null;
        keterangan: string | null;
        hasil_tes_count: number;
        jenis_tes: JenisTesItem;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface Props {
        periode: {
            data: PeriodeItem[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
            links: PaginationLink[];
        };
        jenisTes: JenisTesItem[];
    }

    let { periode, jenisTes }: Props = $props();

    const authUser = $derived(page.props.auth.user);
    const isAdmin = $derived(authUser?.role === 'admin');
    const flash = $derived(page.props.flash);

    let showFormModal = $state(false);
    let isEditing = $state(false);
    let editingId = $state<number | null>(null);

    let formJenisTesId = $state<number | null>(null);
    let formTahun = $state<number | null>(new Date().getFullYear());
    let formGelombang = $state(1);
    let formNama = $state('');
    let formTanggal = $state(todayLocal());
    let formPenyelenggara = $state('');
    let formKeterangan = $state('');
    let formProcessing = $state(false);
    let formErrors = $state<Record<string, string>>({});

    function pad(value: number): string {
        return String(value).padStart(2, '0');
    }

    function todayLocal(): string {
        const now = new Date();

        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    }

    function openCreateModal() {
        isEditing = false;
        editingId = null;
        formJenisTesId = jenisTes[0]?.id ?? null;
        formTahun = new Date().getFullYear();
        formGelombang = 1;
        formNama = '';
        formTanggal = todayLocal();
        formPenyelenggara = '';
        formKeterangan = '';
        formErrors = {};
        showFormModal = true;
    }

    function openEditModal(item: PeriodeItem) {
        isEditing = true;
        editingId = item.id;
        formJenisTesId = item.jenis_tes_id;
        formTahun = item.tahun;
        formGelombang = item.gelombang;
        formNama = item.nama;
        formTanggal = item.tanggal_pelaksanaan.slice(0, 10);
        formPenyelenggara = item.penyelenggara ?? '';
        formKeterangan = item.keterangan ?? '';
        formErrors = {};
        showFormModal = true;
    }

    function submitForm(event: Event) {
        event.preventDefault();
        formProcessing = true;
        formErrors = {};

        const payload = {
            jenis_tes_id: formJenisTesId,
            tahun: formTahun,
            gelombang: formGelombang,
            nama: formNama,
            tanggal_pelaksanaan: formTanggal,
            penyelenggara: formPenyelenggara || null,
            keterangan: formKeterangan || null,
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
            router.put(`/periode-tes/${editingId}`, payload, options);
        } else {
            router.post('/periode-tes', payload, options);
        }
    }

    function destroy(item: PeriodeItem) {
        if (confirm(`Hapus periode "${item.nama}"?`)) {
            router.delete(`/periode-tes/${item.id}`);
        }
    }
</script>

<AppHead title="Periode Tes" />

<AppLayout active="periode">
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
                <h2 class="font-heading text-xl text-foreground">Periode Tes</h2>
                <p class="text-sm text-muted-foreground">
                    Kelola jadwal pelaksanaan tes TOEFL dan jumlah peserta per periode
                    ({periode.total} periode).
                </p>
            </div>
            {#if isAdmin}
                <button
                    onclick={openCreateModal}
                    class="flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition hover:opacity-90"
                >
                    <Icon name="plus" class="h-4 w-4" />
                    <span>Tambah Periode</span>
                </button>
            {/if}
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30 text-xs uppercase text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-semibold">Nama Periode</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Jenis Tes</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tahun / Gelombang</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tanggal Pelaksanaan</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Penyelenggara</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Peserta</th>
                            {#if isAdmin}
                                <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                            {/if}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {#if periode.data.length === 0}
                            <tr>
                                <td colspan={isAdmin ? 7 : 6} class="py-12 text-center text-muted-foreground">
                                    Belum ada periode tes. Tambahkan periode terlebih dahulu sebelum menginput hasil.
                                </td>
                            </tr>
                        {:else}
                            {#each periode.data as item (item.id)}
                                <tr class="transition-colors hover:bg-muted/20">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-foreground">{item.nama}</div>
                                        {#if item.keterangan}
                                            <div class="text-xs text-muted-foreground">{item.keterangan}</div>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge tone={item.jenis_tes.is_resmi ? 'primary' : 'muted'}>
                                            {item.jenis_tes.kode}
                                        </Badge>
                                        <div class="mt-1 text-xs text-muted-foreground">{item.jenis_tes.nama}</div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {item.tahun} / Gelombang {item.gelombang}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {item.tanggal_pelaksanaan.slice(0, 10)}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{item.penyelenggara ?? '-'}</td>
                                    <td class="px-4 py-3">
                                        <Badge tone={item.hasil_tes_count > 0 ? 'ok' : 'muted'}>
                                            {item.hasil_tes_count} hasil
                                        </Badge>
                                    </td>
                                    {#if isAdmin}
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    onclick={() => openEditModal(item)}
                                                    title="Ubah Periode"
                                                    aria-label="Ubah Periode"
                                                    class="rounded p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                                >
                                                    <Icon name="pencil" class="h-4 w-4" />
                                                </button>
                                                <button
                                                    onclick={() => destroy(item)}
                                                    title="Hapus Periode"
                                                    aria-label="Hapus Periode"
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

            {#if periode.total > 0}
                <div class="flex flex-col gap-3 border-t border-border px-4 py-3 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        Menampilkan <span class="font-medium text-foreground">{periode.from ?? 0}</span> sampai
                        <span class="font-medium text-foreground">{periode.to ?? 0}</span> dari
                        <span class="font-medium text-foreground">{periode.total}</span> periode
                    </div>
                    <div class="flex items-center gap-1">
                        {#each periode.links as link}
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
                class="relative w-full max-w-lg rounded-xl border border-border bg-white p-6 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="periode-modal-title"
            >
                <div class="mb-4 flex items-center justify-between border-b border-border pb-3">
                    <h3 id="periode-modal-title" class="font-heading text-lg text-foreground">
                        {isEditing ? 'Ubah Periode Tes' : 'Tambah Periode Tes'}
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
                    <div>
                        <label for="form-jenis-tes" class="mb-1 block text-xs font-medium text-muted-foreground">
                            Jenis Tes *
                        </label>
                        <select
                            id="form-jenis-tes"
                            bind:value={formJenisTesId}
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        >
                            {#each jenisTes as jenis (jenis.id)}
                                <option value={jenis.id}>{jenis.kode} - {jenis.nama}</option>
                            {/each}
                        </select>
                        {#if formErrors.jenis_tes_id}
                            <p class="mt-1 text-xs text-red-600">{formErrors.jenis_tes_id}</p>
                        {/if}
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-tahun" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Tahun *
                            </label>
                            <input
                                id="form-tahun"
                                type="number"
                                min="2000"
                                max="2100"
                                bind:value={formTahun}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.tahun}
                                <p class="mt-1 text-xs text-red-600">{formErrors.tahun}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-gelombang" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Gelombang *
                            </label>
                            <select
                                id="form-gelombang"
                                bind:value={formGelombang}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            >
                                <option value={1}>Gelombang 1</option>
                                <option value={2}>Gelombang 2</option>
                            </select>
                            {#if formErrors.gelombang}
                                <p class="mt-1 text-xs text-red-600">{formErrors.gelombang}</p>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <label for="form-nama-periode" class="mb-1 block text-xs font-medium text-muted-foreground">
                            Nama Periode *
                        </label>
                        <input
                            id="form-nama-periode"
                            type="text"
                            bind:value={formNama}
                            placeholder="Contoh: TOEFL ITP 2026 Gelombang 1"
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                        {#if formErrors.nama}
                            <p class="mt-1 text-xs text-red-600">{formErrors.nama}</p>
                        {/if}
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-tanggal" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Tanggal Pelaksanaan *
                            </label>
                            <input
                                id="form-tanggal"
                                type="date"
                                bind:value={formTanggal}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.tanggal_pelaksanaan}
                                <p class="mt-1 text-xs text-red-600">{formErrors.tanggal_pelaksanaan}</p>
                            {/if}
                        </div>

                        <div>
                            <label for="form-penyelenggara" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Penyelenggara
                            </label>
                            <input
                                id="form-penyelenggara"
                                type="text"
                                bind:value={formPenyelenggara}
                                placeholder="Contoh: Pusat Bahasa"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.penyelenggara}
                                <p class="mt-1 text-xs text-red-600">{formErrors.penyelenggara}</p>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <label for="form-keterangan" class="mb-1 block text-xs font-medium text-muted-foreground">
                            Keterangan
                        </label>
                        <textarea
                            id="form-keterangan"
                            bind:value={formKeterangan}
                            rows="2"
                            placeholder="Catatan tambahan (opsional)"
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        ></textarea>
                        {#if formErrors.keterangan}
                            <p class="mt-1 text-xs text-red-600">{formErrors.keterangan}</p>
                        {/if}
                    </div>

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
                            {formProcessing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Periode'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</AppLayout>
