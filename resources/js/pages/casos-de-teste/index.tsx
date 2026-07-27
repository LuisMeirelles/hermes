import { Head, Link, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import CasoDeTesteController from '@/actions/App/Http/Controllers/CasoDeTesteController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, edit, index } from '@/routes/casos-de-teste';
import type { CasoDeTeste } from '@/types';

export default function CasosDeTesteIndex({
    casosDeTeste,
}: {
    casosDeTeste: CasoDeTeste[];
}) {
    const [excluindo, setExcluindo] = useState<CasoDeTeste | null>(null);

    function confirmarExclusao() {
        if (!excluindo) {
            return;
        }

        router.delete(CasoDeTesteController.destroy(excluindo.id).url, {
            onSuccess: () => setExcluindo(null),
        });
    }

    return (
        <>
            <Head title="Casos de Teste" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Casos de Teste"
                        description="Biblioteca reutilizável de casos de teste"
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus /> Novo Caso de Teste
                        </Link>
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Título</TableHead>
                            <TableHead>Passos</TableHead>
                            <TableHead>Criado em</TableHead>
                            <TableHead className="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {casosDeTeste.map((casoDeTeste) => (
                            <TableRow
                                key={casoDeTeste.id}
                                className="cursor-pointer"
                                onClick={() =>
                                    router.visit(edit(casoDeTeste.id))
                                }
                            >
                                <TableCell>{casoDeTeste.titulo}</TableCell>
                                <TableCell>
                                    {casoDeTeste.passos_count}
                                </TableCell>
                                <TableCell>
                                    {new Date(
                                        casoDeTeste.created_at,
                                    ).toLocaleDateString('pt-BR')}
                                </TableCell>
                                <TableCell className="text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={(event) => {
                                            event.stopPropagation();
                                            setExcluindo(casoDeTeste);
                                        }}
                                    >
                                        <Trash2 />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            <Dialog
                open={excluindo !== null}
                onOpenChange={(open) => !open && setExcluindo(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Excluir Caso de Teste</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        Tem certeza que deseja excluir &quot;{excluindo?.titulo}
                        &quot;? Esta ação não pode ser desfeita.
                    </p>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setExcluindo(null)}
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

CasosDeTesteIndex.layout = {
    breadcrumbs: [{ title: 'Casos de Teste', href: index() }],
};
