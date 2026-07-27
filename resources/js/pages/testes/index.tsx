import { Head, Link, router } from '@inertiajs/react';
import { Plus, X } from 'lucide-react';
import Heading from '@/components/heading';
import StatusBadge from '@/components/status-badge';
import { Badge } from '@/components/ui/badge';
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

const STATUS_FILTER_LABELS: Record<string, string> = {
    passou: 'Sucesso',
    falhou: 'Falha',
    parcial: 'Parcial',
    pendente: 'Pendente',
};

export default function TestesIndex({
    testes,
    statusFilter,
}: {
    testes: Teste[];
    statusFilter: string | null;
}) {
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

                {statusFilter && (
                    <div className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">
                            Filtrado por:
                        </span>
                        <Badge variant="outline">
                            {STATUS_FILTER_LABELS[statusFilter] ?? statusFilter}
                        </Badge>
                        <Link
                            href={index()}
                            className="text-muted-foreground hover:text-foreground"
                        >
                            <X className="size-3.5" />
                        </Link>
                    </div>
                )}

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
