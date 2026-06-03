import type { RoleKey, RoleTokens } from '@/lib/types';

export function authRouteWithEncryptedRole(
    routeName: 'login' | 'register',
    roleTokens: RoleTokens,
    role: RoleKey,
): string {
    return route(routeName, { role: roleTokens[role] });
}
