<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { useForm } from '@inertiajs/svelte';

    let { wajibDiganti = false }: { wajibDiganti?: boolean } = $props();

    const form = useForm({
        password_lama: '',
        password_baru: '',
        password_baru_confirmation: '',
    });

    function submit(event: SubmitEvent) {
        event.preventDefault();
        form.put('/ganti-password', {
            onSuccess: () => form.reset(),
        });
    }
</script>

<AppHead title="Ganti Password" />

<AuthLayout>
    <h1 class="font-heading mb-1 text-xl text-foreground">Ganti Password</h1>
    {#if wajibDiganti}
        <p class="mb-6 text-sm text-status-warning">
            Ini login pertama Anda — password masih sama dengan NIP/nomor pegawai. Silakan ganti dulu sebelum
            melanjutkan.
        </p>
    {:else}
        <p class="mb-6 text-sm text-muted-foreground">Masukkan password lama dan password baru Anda.</p>
    {/if}

    <form onsubmit={submit} class="space-y-4">
        <div>
            <label for="password_lama" class="mb-1 block text-sm font-medium text-foreground">Password Lama</label>
            <input
                id="password_lama"
                type="password"
                autocomplete="current-password"
                bind:value={form.password_lama}
                class="w-full rounded-md border border-border px-3 py-2 text-sm focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
            {#if form.errors.password_lama}
                <p class="mt-1 text-sm text-status-danger">{form.errors.password_lama}</p>
            {/if}
        </div>

        <div>
            <label for="password_baru" class="mb-1 block text-sm font-medium text-foreground">Password Baru</label>
            <input
                id="password_baru"
                type="password"
                autocomplete="new-password"
                bind:value={form.password_baru}
                class="w-full rounded-md border border-border px-3 py-2 text-sm focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
            {#if form.errors.password_baru}
                <p class="mt-1 text-sm text-status-danger">{form.errors.password_baru}</p>
            {/if}
        </div>

        <div>
            <label for="password_baru_confirmation" class="mb-1 block text-sm font-medium text-foreground">
                Ulangi Password Baru
            </label>
            <input
                id="password_baru_confirmation"
                type="password"
                autocomplete="new-password"
                bind:value={form.password_baru_confirmation}
                class="w-full rounded-md border border-border px-3 py-2 text-sm focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
        </div>

        <button
            type="submit"
            disabled={form.processing}
            class="w-full rounded-md bg-primary py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-60"
        >
            Simpan Password Baru
        </button>
    </form>
</AuthLayout>
