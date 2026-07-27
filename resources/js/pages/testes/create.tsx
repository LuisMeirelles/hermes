import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import TesteController from '@/actions/App/Http/Controllers/TesteController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, issueLookup } from '@/routes/testes';
import type { GithubIssuePreview, GithubRepositorio } from '@/types';

type FormData = {
    repo_name: string;
    issue_number: string;
    titulo: string;
};

export default function TestesCreate({
    repositorios,
}: {
    repositorios: GithubRepositorio[];
}) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        repo_name: '',
        issue_number: '',
        titulo: '',
    });

    const [issue, setIssue] = useState<GithubIssuePreview | null>(null);
    const [buscando, setBuscando] = useState(false);
    const [erroLookup, setErroLookup] = useState<string | null>(null);

    function limparIssue() {
        setIssue(null);
        setErroLookup(null);
    }

    async function buscarIssue() {
        setBuscando(true);
        setErroLookup(null);
        setIssue(null);

        try {
            const response = await fetch(
                issueLookup.url({
                    query: {
                        repo_name: data.repo_name,
                        issue_number: data.issue_number,
                    },
                }),
            );

            if (!response.ok) {
                setErroLookup('Issue não encontrada.');

                return;
            }

            const preview = (await response.json()) as GithubIssuePreview;
            setIssue(preview);
            setData('titulo', preview.title);
        } catch {
            setErroLookup('Não foi possível buscar a issue.');
        } finally {
            setBuscando(false);
        }
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        post(TesteController.store().url);
    }

    return (
        <>
            <Head title="Novo Teste" />

            <div className="space-y-6">
                <Heading
                    title="Novo Teste"
                    description="Vincule um novo ciclo de testes a uma issue do GitHub"
                />

                <form onSubmit={submit} className="max-w-xl space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="repo_name">Repositório</Label>
                        <Select
                            value={data.repo_name}
                            onValueChange={(value) => {
                                setData('repo_name', value);
                                limparIssue();
                            }}
                        >
                            <SelectTrigger id="repo_name" className="w-full">
                                <SelectValue placeholder="Selecione um repositório" />
                            </SelectTrigger>
                            <SelectContent>
                                {repositorios.map((repo) => (
                                    <SelectItem
                                        key={repo.full_name}
                                        value={repo.name}
                                    >
                                        {repo.full_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.repo_name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="issue_number">Número da Issue</Label>
                        <div className="flex gap-2">
                            <Input
                                id="issue_number"
                                type="number"
                                min={1}
                                value={data.issue_number}
                                onChange={(event) => {
                                    setData('issue_number', event.target.value);
                                    limparIssue();
                                }}
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={
                                    !data.repo_name ||
                                    !data.issue_number ||
                                    buscando
                                }
                                onClick={buscarIssue}
                            >
                                Buscar
                            </Button>
                        </div>
                        <InputError message={errors.issue_number} />
                        {erroLookup && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {erroLookup}
                            </p>
                        )}
                    </div>

                    {issue && (
                        <div className="rounded-md border p-4 text-sm">
                            <p className="font-medium">{issue.title}</p>
                            <p className="text-muted-foreground">
                                {issue.state}
                            </p>
                            <a
                                href={issue.html_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-primary hover:underline"
                            >
                                Ver no GitHub
                            </a>
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="titulo">Título do Teste</Label>
                        <Input
                            id="titulo"
                            value={data.titulo}
                            onChange={(event) =>
                                setData('titulo', event.target.value)
                            }
                        />
                        <InputError message={errors.titulo} />
                    </div>

                    <Button type="submit" disabled={processing || !issue}>
                        Criar Teste
                    </Button>
                </form>
            </div>
        </>
    );
}

TestesCreate.layout = {
    breadcrumbs: [
        { title: 'Testes', href: index() },
        { title: 'Novo', href: '' },
    ],
};
