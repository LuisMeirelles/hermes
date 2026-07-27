import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, index, show } from '@/routes/testes';
import type { Teste } from '@/types';

export default function TestesIndex({ testes }: { testes: Teste[] }) {
    return (
        <>
            <Head title="Testes" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Testes"
                        description="Ciclos de execução vinculados a issues do GitHub"
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus /> Novo Teste
                        </Link>
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Título</TableHead>
                            <TableHead>Issue</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Progresso</TableHead>
                            <TableHead>Criado em</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {testes.map((teste) => (
                            <TableRow
                                key={teste.id}
                                className="cursor-pointer"
                                onClick={() => router.visit(show(teste.id))}
                            >
                                <TableCell>
                                    {teste.titulo ??
                                        `${teste.repo_name}#${teste.issue_number}`}
                                </TableCell>
                                <TableCell>
                                    {teste.repo_name}#{teste.issue_number}
                                </TableCell>
                                <TableCell>
                                    <StatusBadge status={teste.status} />
                                </TableCell>
                                <TableCell>
                                    {Number(teste.percent_complete)}%
                                </TableCell>
                                <TableCell>
                                    {new Date(
                                        teste.created_at,
                                    ).toLocaleDateString('pt-BR')}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}

TestesIndex.layout = {
    breadcrumbs: [{ title: 'Testes', href: index() }],
};
