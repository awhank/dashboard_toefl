<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { useForm } from '@inertiajs/svelte';

    const form = useForm({
        username: '',
        password: '',
    });

    function submit(event: SubmitEvent) {
        event.preventDefault();
        form.post('/login', {
            onFinish: () => form.reset('password'),
        });
    }
</script>

<AppHead title="Masuk" />

<AuthLayout>
    <h1 class="font-heading mb-1 text-xl text-foreground">Masuk</h1>
    <p class="mb-6 text-sm text-muted-foreground">Gunakan NIP/nomor pegawai dan password Anda.</p>

    <form onsubmit={submit} class="space-y-4">
        <div>
            <label for="username" class="mb-1 block text-sm font-medium text-foreground">
                NIP / Nomor Pegawai
            </label>
            <input
                id="username"
                type="text"
                autocomplete="username"
                bind:value={form.username}
                class="w-full rounded-md border border-border px-3 py-2 text-sm focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
            {#if form.errors.username}
                <p class="mt-1 text-sm text-status-danger">{form.errors.username}</p>
            {/if}
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-foreground">Password</label>
            <input
                id="password"
                type="password"
                autocomplete="current-password"
                bind:value={form.password}
                class="w-full rounded-md border border-border px-3 py-2 text-sm focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
            {#if form.errors.password}
                <p class="mt-1 text-sm text-status-danger">{form.errors.password}</p>
            {/if}
        </div>

        <button
            type="submit"
            disabled={form.processing}
            class="w-full rounded-md bg-primary py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-60"
        >
            Masuk
        </button>
    </form>
</AuthLayout>
