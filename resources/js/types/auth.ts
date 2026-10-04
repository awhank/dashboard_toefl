export type Role = 'admin' | 'pimpinan' | 'pegawai';

export type User = {
    id: number;
    pegawai_id: number | null;
    username: string;
    name: string;
    role: Role;
    is_active: boolean;
    password_changed_at: string | null;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
