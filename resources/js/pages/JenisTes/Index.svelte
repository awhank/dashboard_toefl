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
        skor_bagian_min: number;
        skor_bagian_max: number;
        skor_total_min: number;
        skor_total_max: number;
        masa_berlaku_bulan: number | null;
        periode_tes_count: number;
    }

    interface Props {
        jenisTes: JenisTesItem[];
    }

    let { jenisTes }: Props = $props();

    const flash = $derived(page.props.flash);

    let showFormModal = $state(false);
    let isEditing = $state(false);
    let editingId = $state<number | null>(null);

    let formKode = $state('');
    let formNama = $state('');
    let formIsResmi = $state(true);
    let formSkorBagianMin = $state<number | null>(31);
    let formSkorBagianMax = $state<number | null>(68);
    let formSkorTotalMin = $state<number | null>(310);
    let formSkorTotalMax = $state<number | null>(677);
    let formMasaBerlaku = $state<number | null>(24);
    let formProcessing = $state(false);
    let formErrors = $state<Record<string, string>>({});

    function openCreateModal() {
        isEditing = false;
        editingId = null;
        formKode = '';
        formNama = '';
        formIsResmi = true;
        formSkorBagianMin = 31;
        formSkorBagianMax = 68;
        formSkorTotalMin = 310;
        formSkorTotalMax = 677;
        formMasaBerlaku = 24;
        formErrors = {};
        showFormModal = true;
    }

    function openEditModal(item: JenisTesItem) {
        isEditing = true;
        editingId = item.id;
        formKode = item.kode;
        formNama = item.nama;
        formIsResmi = item.is_resmi;
        formSkorBagianMin = item.skor_bagian_min;
        formSkorBagianMax = item.skor_bagian_max;
        formSkorTotalMin = item.skor_total_min;
        formSkorTotalMax = item.skor_total_max;
        formMasaBerlaku = item.masa_berlaku_bulan;
        formErrors = {};
        showFormModal = true;
    }

    function submitForm(event: Event) {
        event.preventDefault();
        formProcessing = true;
        formErrors = {};

        const payload = {
            kode: formKode,
            nama: formNama,
            is_resmi: formIsResmi,
            skor_bagian_min: formSkorBagianMin,
            skor_bagian_max: formSkorBagianMax,
            skor_total_min: formSkorTotalMin,
            skor_total_max: formSkorTotalMax,
            masa_berlaku_bulan: formMasaBerlaku || null,
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
            router.put(`/jenis-tes/${editingId}`, payload, options);
        } else {
            router.post('/jenis-tes', payload, options);
        }
    }

    function destroy(item: JenisTesItem) {
        if (confirm(`Hapus jenis tes "${item.nama}"?`)) {
            router.delete(`/jenis-tes/${item.id}`);
        }
    }
</script>

<AppHead title="Jenis Tes" />

<AppLayout active="jenis-tes">
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
                <h2 class="font-heading text-xl text-foreground">Jenis Tes</h2>
                <p class="text-sm text-muted-foreground">
                    Kelola jenis tes, rentang skor, dan masa berlaku sertifikat.
                </p>
            </div>
            <button
                onclick={openCreateModal}
                class="flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition hover:opacity-90"
            >
                <Icon name="plus" class="h-4 w-4" />
                <span>Tambah Jenis Tes</span>
            </button>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/30 text-xs uppercase text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-semibold">Kode</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Nama</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Skor Bagian</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Skor Total</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Masa Berlaku</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Dipakai</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {#if jenisTes.length === 0}
                            <tr>
                                <td colspan="8" class="py-12 text-center text-muted-foreground">
                                    Belum ada jenis tes. Tambahkan jenis tes terlebih dahulu.
                                </td>
                            </tr>
                        {:else}
                            {#each jenisTes as item (item.id)}
                                <tr class="transition-colors hover:bg-muted/20">
                                    <td class="px-4 py-3 font-mono text-xs font-medium text-foreground">
                                        {item.kode}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-foreground">{item.nama}</td>
                                    <td class="px-4 py-3">
                                        <Badge tone={item.is_resmi ? 'primary' : 'muted'}>
                                            {item.is_resmi ? 'Resmi' : 'Prediction'}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {item.skor_bagian_min} – {item.skor_bagian_max}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {item.skor_total_min} – {item.skor_total_max}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {item.masa_berlaku_bulan ? `${item.masa_berlaku_bulan} bulan` : '-'}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge tone={item.periode_tes_count > 0 ? 'ok' : 'muted'}>
                                            {item.periode_tes_count} periode
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button
                                                onclick={() => openEditModal(item)}
                                                title="Ubah Jenis Tes"
                                                aria-label="Ubah Jenis Tes"
                                                class="rounded p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                            >
                                                <Icon name="pencil" class="h-4 w-4" />
                                            </button>
                                            <button
                                                onclick={() => destroy(item)}
                                                title="Hapus Jenis Tes"
                                                aria-label="Hapus Jenis Tes"
                                                class="rounded p-1.5 text-muted-foreground transition hover:bg-red-50 hover:text-red-600"
                                            >
                                                <Icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    {#if showFormModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs">
            <div
                class="relative w-full max-w-lg rounded-xl border border-border bg-white p-6 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="jenis-tes-modal-title"
            >
                <div class="mb-4 flex items-center justify-between border-b border-border pb-3">
                    <h3 id="jenis-tes-modal-title" class="font-heading text-lg text-foreground">
                        {isEditing ? 'Ubah Jenis Tes' : 'Tambah Jenis Tes'}
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
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label for="form-kode" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Kode *
                            </label>
                            <input
                                id="form-kode"
                                type="text"
                                bind:value={formKode}
                                maxlength="10"
                                placeholder="ITP"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm uppercase text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.kode}
                                <p class="mt-1 text-xs text-red-600">{formErrors.kode}</p>
                            {/if}
                        </div>
                        <div class="sm:col-span-2">
                            <label for="form-nama" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Nama Jenis Tes *
                            </label>
                            <input
                                id="form-nama"
                                type="text"
                                bind:value={formNama}
                                placeholder="TOEFL ITP"
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.nama}
                                <p class="mt-1 text-xs text-red-600">{formErrors.nama}</p>
                            {/if}
                        </div>
                    </div>

                    <label class="flex items-start gap-2.5 rounded-lg border border-border p-3 text-xs hover:bg-muted/20">
                        <input
                            type="checkbox"
                            bind:checked={formIsResmi}
                            class="mt-0.5 rounded border-border text-primary focus:ring-brand-teal"
                        />
                        <span>
                            <span class="block font-medium text-foreground">Tes resmi</span>
                            <span class="text-muted-foreground">
                                Hasil tes resmi dihitung sebagai sertifikat dan mengikuti masa berlaku.
                            </span>
                        </span>
                    </label>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="form-skor-bagian-min" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Skor Bagian Minimal *
                            </label>
                            <input
                                id="form-skor-bagian-min"
                                type="number"
                                min="0"
                                bind:value={formSkorBagianMin}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_bagian_min}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_bagian_min}</p>
                            {/if}
                        </div>
                        <div>
                            <label for="form-skor-bagian-max" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Skor Bagian Maksimal *
                            </label>
                            <input
                                id="form-skor-bagian-max"
                                type="number"
                                min="0"
                                bind:value={formSkorBagianMax}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_bagian_max}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_bagian_max}</p>
                            {/if}
                        </div>
                        <div>
                            <label for="form-skor-total-min" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Skor Total Minimal *
                            </label>
                            <input
                                id="form-skor-total-min"
                                type="number"
                                min="0"
                                bind:value={formSkorTotalMin}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_total_min}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_total_min}</p>
                            {/if}
                        </div>
                        <div>
                            <label for="form-skor-total-max" class="mb-1 block text-xs font-medium text-muted-foreground">
                                Skor Total Maksimal *
                            </label>
                            <input
                                id="form-skor-total-max"
                                type="number"
                                min="0"
                                bind:value={formSkorTotalMax}
                                class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                            />
                            {#if formErrors.skor_total_max}
                                <p class="mt-1 text-xs text-red-600">{formErrors.skor_total_max}</p>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <label for="form-masa-berlaku" class="mb-1 block text-xs font-medium text-muted-foreground">
                            Masa Berlaku (bulan)
                        </label>
                        <input
                            id="form-masa-berlaku"
                            type="number"
                            min="1"
                            max="255"
                            bind:value={formMasaBerlaku}
                            placeholder="Contoh: 24"
                            class="w-full rounded-md border border-border px-3 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Kosongkan untuk jenis tes yang tidak memiliki masa berlaku sertifikat.
                        </p>
                        {#if formErrors.masa_berlaku_bulan}
                            <p class="mt-1 text-xs text-red-600">{formErrors.masa_berlaku_bulan}</p>
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
                            {formProcessing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Jenis Tes'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</AppLayout>
