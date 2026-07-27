import { Head, router } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Plus } from 'lucide-react';
import { Fragment, useState } from 'react';
import { toast } from 'sonner';
import CenarioController from '@/actions/App/Http/Controllers/CenarioController';
import AdicionarCenariosDialog from '@/components/adicionar-cenarios-dialog';
import GherkinBlock from '@/components/gherkin-block';
import Heading from '@/components/heading';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/testes';
import type {
    CasoDeTeste,
    Cenario,
    CenarioStatus,
    GithubIssuePreview,
    Severidade,
    Teste,
} from '@/types';

const STATUS_OPTIONS: CenarioStatus[] = [
    'a_fazer',
    'em_andamento',
    'passou',
    'falhou',
    'bloqueado',
];
const STATUS_LABELS: Record<CenarioStatus, string> = {
    a_fazer: 'A Fazer',
    em_andamento: 'Em Andamento',
    passou: 'Passou',
    falhou: 'Falhou',
    bloqueado: 'Bloqueado',
};

const SEVERIDADE_OPTIONS: Severidade[] = [
    'bloqueante',
    'critica',
    'maior',
    'menor',
];
const SEVERIDADE_LABELS: Record<Severidade, string> = {
    bloqueante: 'Bloqueante',
    critica: 'Crítica',
    maior: 'Maior',
    menor: 'Menor',
};

type TestesShowProps = {
    teste: Teste;
    issue: GithubIssuePreview;
    cenarios: Cenario[];
    casosDeTeste: CasoDeTeste[];
};

export default function TestesShow({
    teste,
    issue,
    cenarios,
    casosDeTeste,
}: TestesShowProps) {
    const [expandido, setExpandido] = useState<number | null>(null);

    function adicionarCenarios(
        casos: { caso_de_teste_id: number; severidade: Severidade }[],
    ) {
        router.post(
            CenarioController.store(teste.id).url,
            { casos },
            { preserveScroll: true },
        );
    }

    function atualizarCenario(
        cenario: Cenario,
        changes: Partial<Pick<Cenario, 'status' | 'severidade'>>,
    ) {
        router.patch(
            CenarioController.update({ teste: teste.id, cenario: cenario.id })
                .url,
            changes,
            {
                preserveScroll: true,
                onError: () =>
                    toast.error('Não foi possível atualizar o cenário.'),
            },
        );
    }

    return (
        <>
            <Head
                title={
                    teste.titulo ?? `${teste.repo_name}#${teste.issue_number}`
                }
            />

            <div className="space-y-6">
                <div className="space-y-2">
                    <Heading
                        title={teste.titulo ?? issue.title}
                        description={`${teste.repo_name}#${teste.issue_number}`}
                    />
                    <div className="flex flex-wrap items-center gap-3">
                        <StatusBadge status={teste.status} />
                        <span className="text-sm text-muted-foreground">
                            {Number(teste.percent_complete)}% concluído
                        </span>
                        <a
                            href={issue.html_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm text-primary hover:underline"
                        >
                            Ver issue no GitHub
                        </a>
                    </div>
                </div>

                <AdicionarCenariosDialog
                    casosDeTeste={casosDeTeste}
                    onConfirm={adicionarCenarios}
                    trigger={
                        <Button>
                            <Plus /> Adicionar Cenários
                        </Button>
                    }
                />

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-10" />
                            <TableHead>Título</TableHead>
                            <TableHead>Severidade</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {cenarios.map((cenario) => (
                            <Fragment key={cenario.id}>
                                <TableRow>
                                    <TableCell>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                setExpandido(
                                                    expandido === cenario.id
                                                        ? null
                                                        : cenario.id,
                                                )
                                            }
                                        >
                                            {expandido === cenario.id ? (
                                                <ChevronDown />
                                            ) : (
                                                <ChevronRight />
                                            )}
                                        </Button>
                                    </TableCell>
                                    <TableCell>{cenario.titulo}</TableCell>
                                    <TableCell>
                                        <Select
                                            value={cenario.severidade}
                                            onValueChange={(value) =>
                                                atualizarCenario(cenario, {
                                                    severidade:
                                                        value as Severidade,
                                                })
                                            }
                                        >
                                            <SelectTrigger
                                                size="sm"
                                                className="w-32"
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {SEVERIDADE_OPTIONS.map(
                                                    (opcao) => (
                                                        <SelectItem
                                                            key={opcao}
                                                            value={opcao}
                                                        >
                                                            {
                                                                SEVERIDADE_LABELS[
                                                                    opcao
                                                                ]
                                                            }
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                    <TableCell>
                                        <Select
                                            value={cenario.status}
                                            onValueChange={(value) =>
                                                atualizarCenario(cenario, {
                                                    status: value as CenarioStatus,
                                                })
                                            }
                                        >
                                            <SelectTrigger
                                                size="sm"
                                                className="w-36"
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {STATUS_OPTIONS.map((opcao) => (
                                                    <SelectItem
                                                        key={opcao}
                                                        value={opcao}
                                                    >
                                                        {STATUS_LABELS[opcao]}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                </TableRow>
                                {expandido === cenario.id && (
                                    <TableRow>
                                        <TableCell colSpan={4}>
                                            <GherkinBlock
                                                passos={cenario.passos_snapshot}
                                            />
                                        </TableCell>
                                    </TableRow>
                                )}
                            </Fragment>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}

TestesShow.layout = {
    breadcrumbs: [{ title: 'Testes', href: index() }],
};
