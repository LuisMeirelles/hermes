import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/settings/github';

type Installation = {
    account_login: string;
    account_type: string;
} | null;

export default function Github({
    installation,
    appSlug,
}: {
    installation: Installation;
    appSlug: string | null;
}) {
    return (
        <>
            <Head title="GitHub connection" />

            <h1 className="sr-only">GitHub connection</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="GitHub"
                    description="Connect Hermes to your GitHub App installation"
                />

                {installation ? (
                    <p>
                        Connected as{' '}
                        <strong>{installation.account_login}</strong> (
                        {installation.account_type})
                    </p>
                ) : (
                    <div className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            Not connected yet.
                        </p>
                        <Button asChild>
                            <a
                                href={`https://github.com/apps/${appSlug}/installations/new`}
                            >
                                Install on GitHub
                            </a>
                        </Button>
                    </div>
                )}
            </div>
        </>
    );
}

Github.layout = {
    breadcrumbs: [
        {
            title: 'GitHub',
            href: edit(),
        },
    ],
};
