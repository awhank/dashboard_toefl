<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import { page } from '@inertiajs/svelte';

    const user = $derived(page.props.auth.user);

    const roleLabel: Record<string, string> = {
        admin: 'Administrator',
        pimpinan: 'Pimpinan',
        pegawai: 'Pegawai',
    };
</script>

<AppHead title="Profil" />

<AppLayout active="profil">
    <div class="mx-auto max-w-2xl space-y-4">
        <Card>
            <h2 class="font-heading mb-4 text-base text-foreground">Profil Saya</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Nama</dt>
                    <dd class="font-medium text-foreground">{user.name}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">NIP / Nomor Pegawai</dt>
                    <dd class="font-medium text-foreground">{user.username}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Peran</dt>
                    <dd class="font-medium text-foreground">{roleLabel[user.role] ?? user.role}</dd>
                </div>
            </dl>
            <a href="/ganti-password" class="mt-4 inline-block text-sm font-medium text-primary hover:underline">
                Ganti Password →
            </a>
        </Card>

        <p class="text-sm text-muted-foreground">
            Riwayat skor TOEFL Anda akan tampil di sini setelah halaman ini terhubung ke data hasil tes.
        </p>
    </div>
</AppLayout>
