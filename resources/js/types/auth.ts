export interface Permission {
    id: number;
    name: string;
    display_name: string;
    group: string;
}

export interface Role {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    permissions: Permission[];
}

export interface RoleUser {
    id: number;
    user_id: number;
    role_id: number;
    parent_id: number | null;
    role: Role;
}

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_admin?: boolean;
    roles?: Role[];
    permissions?: string[];
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

