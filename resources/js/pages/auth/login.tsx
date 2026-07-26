import { Head } from '@inertiajs/react';
import { Github } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { redirect } from '@/routes/github';

export default function Login() {
    return (
        <>
            <Head title="Log in" />

            <Button asChild className="w-full">
                <a href={redirect.url()}>
                    <Github className="mr-2" />
                    Continue with GitHub
                </a>
            </Button>
        </>
    );
}

Login.layout = {
    title: 'Log in to Hermes',
    description: 'Sign in with your GitHub account to continue',
};
