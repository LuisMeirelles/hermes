import { Head, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import CasoDeTesteController from '@/actions/App/Http/Controllers/CasoDeTesteController';
import GherkinStepEditor from '@/components/gherkin-step-editor';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    buildPassosPayload,
    groupPassosByFase,
    hasStepInEveryFase,
} from '@/lib/gherkin';
import type { GherkinFormState } from '@/lib/gherkin';
import { index } from '@/routes/casos-de-teste';
import type { CasoDeTeste } from '@/types';

type FormData = {
    titulo: string;
    descricao: string;
    passos: GherkinFormState;
};

export default function CasosDeTesteEdit({
    casoDeTeste,
}: {
    casoDeTeste: CasoDeTeste;
}) {
    const [excluindo, setExcluindo] = useState(false);

    const { data, setData, transform, patch, processing, errors } =
        useForm<FormData>({
            titulo: casoDeTeste.titulo,
            descricao: casoDeTeste.descricao ?? '',
            passos: groupPassosByFase(casoDeTeste.passos),
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
        patch(CasoDeTesteController.update(casoDeTeste.id).url);
    }

    function confirmarExclusao() {
        router.delete(CasoDeTesteController.destroy(casoDeTeste.id).url);
    }

    return (
        <>
            <Head title={casoDeTeste.titulo} />

            <div className="space-y-6">
                <Heading
                    title="Editar Caso de Teste"
                    description="Atualize o título e os passos em Gherkin"
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

                    <div className="flex items-center gap-4">
                        <Button
                            type="submit"
                            disabled={processing || !formValido}
                        >
                            Salvar
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={() => setExcluindo(true)}
                        >
                            Excluir
                        </Button>
                    </div>
                </form>
            </div>

            <Dialog open={excluindo} onOpenChange={setExcluindo}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Excluir Caso de Teste</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        Tem certeza que deseja excluir &quot;
                        {casoDeTeste.titulo}&quot;? Esta ação não pode ser
                        desfeita.
                    </p>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setExcluindo(false)}
                        >
                            Cancelar
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={confirmarExclusao}
                        >
                            Excluir
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

CasosDeTesteEdit.layout = {
    breadcrumbs: [{ title: 'Casos de Teste', href: index() }],
};
