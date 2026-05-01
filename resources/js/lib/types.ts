import type { Component } from 'vue';

export type RoleKey = 'patient' | 'doctor' | 'family_member';

export type RoleOption = {
    key: RoleKey;
    label: string;
    icon: Component;
    ringClass: string;
};

export type User = {
    public_id: string;
    name: string;
    email: string | null;
    role: RoleKey;
    email_verified_at: string | null;
};

export type Auth = {
    user: User | null;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: Auth;
    flash: {
        error: string | null;
        rateLimitSeconds: number | null;
    };
};
