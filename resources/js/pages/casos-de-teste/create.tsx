import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import CasoDeTesteController from '@/actions/App/Http/Controllers/CasoDeTesteController';
import GherkinStepEditor from '@/components/gherkin-step-editor';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    buildPassosPayload,
    createEmptyGherkinFormState,
    hasStepInEveryFase,
} from '@/lib/gherkin';
import type { GherkinFormState } from '@/lib/gherkin';
import { index } from '@/routes/casos-de-teste';

type FormData = {
    titulo: string;
    descricao: string;
    passos: GherkinFormState;
};

export default function CasosDeTesteCreate() {
    const { data, setData, transform, post, processing, errors } =
        useForm<FormData>({
            titulo: '',
            descricao: '',
            passos: createEmptyGherkinFormState(),
        });

    transform((formData) => ({
        titulo: formData.titulo,
        descricao: formData.descricao,
        passos: buildPassosPayload(formData.passos),
    }));

    const formValido =
        data.titulo.trim() !== '' && hasStepInEveryFase(data.passos);

    function submit(event: FormEvent) {
        event.preventDefault();
        post(CasoDeTesteController.store().url);
    }

    return (
        <>
            <Head title="Novo Caso de Teste" />

            <div className="space-y-6">
                <Heading
                    title="Novo Caso de Teste"
                    description="Descreva o caso de teste em Gherkin"
                />

                <form onSubmit={submit} className="max-w-2xl space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="titulo">Título</Label>
                        <Input
                            id="titulo"
                            value={data.titulo}
                            onChange={(event) =>
                                setData('titulo', event.target.value)
                            }
                        />
                        <InputError message={errors.titulo} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="descricao">Descrição (opcional)</Label>
                        <textarea
                            id="descricao"
                            className="flex min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                            value={data.descricao}
                            onChange={(event) =>
                                setData('descricao', event.target.value)
                            }
                        />
                        <InputError message={errors.descricao} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Passos</Label>
                        <GherkinStepEditor
                            titulo={data.titulo || undefined}
                            value={data.passos}
                            onChange={(passos) => setData('passos', passos)}
                            disabled={processing}
                        />
                        <InputError message={errors.passos} />
                    </div>

                    <Button type="submit" disabled={processing || !formValido}>
                        Criar Caso de Teste
                    </Button>
                </form>
            </div>
        </>
    );
}

CasosDeTesteCreate.layout = {
    breadcrumbs: [
        { title: 'Casos de Teste', href: index() },
        { title: 'Novo', href: '' },
    ],
};
