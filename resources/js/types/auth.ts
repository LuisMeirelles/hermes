export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    github_username?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
