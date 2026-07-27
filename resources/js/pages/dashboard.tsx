import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import StatCard from '@/components/stat-card';
import StatusBadge from '@/components/status-badge';
import StatusBreakdownBar from '@/components/status-breakdown-bar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as testesIndex, show as testesShow } from '@/routes/testes';
import type { Cenario, Teste } from '@/types';

type DashboardStats = {
    total: number;
    sucesso: number;
    falha: number;
    parcial: number;
    pendente: number;
};

type CenarioBloqueante = Cenario & {
    teste: Pick<Teste, 'id' | 'repo_name' | 'issue_number' | 'titulo'>;
};

type CasosDeTesteStats = {
    total: number;
    naoUtilizados: number;
};

export default function Dashboard({
    stats,
    cenariosBloqueantes,
    testesRecentes,
    casosDeTeste,
}: {
    stats: DashboardStats;
    cenariosBloqueantes: CenarioBloqueante[];
    testesRecentes: Teste[];
    casosDeTeste: CasosDeTesteStats;
}) {
    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Dashboard"
                    description="Visão geral dos testes"
                />

                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard
                        label="Sucesso"
                        count={stats.sucesso}
                        total={stats.total}
                        href={testesIndex({ query: { status: 'passou' } })}
                        accentClassName="text-green-600 dark:text-green-400"
                    />
                    <StatCard
                        label="Falha"
                        count={stats.falha}
                        total={stats.total}
                        href={testesIndex({ query: { status: 'falhou' } })}
                        accentClassName="text-red-600 dark:text-red-400"
                    />
                    <StatCard
                        label="Parcial"
                        count={stats.parcial}
                        total={stats.total}
                        href={testesIndex({ query: { status: 'parcial' } })}
                        accentClassName="text-orange-600 dark:text-orange-400"
                    />
                    <StatCard
                        label="Pendente"
                        count={stats.pendente}
                        total={stats.total}
                        href={testesIndex({ query: { status: 'pendente' } })}
                        accentClassName="text-muted-foreground"
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Distribuição de Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <StatusBreakdownBar
                            segments={[
                                {
                                    label: 'Sucesso',
                                    count: stats.sucesso,
                                    className: 'bg-green-500',
                                },
                                {
                                    label: 'Falha',
                                    count: stats.falha,
                                    className: 'bg-red-500',
                                },
                                {
                                    label: 'Parcial',
                                    count: stats.parcial,
                                    className: 'bg-orange-500',
                                },
                                {
                                    label: 'Pendente',
                                    count: stats.pendente,
                                    className: 'bg-muted-foreground',
                                },
                            ]}
                        />
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Cenários bloqueantes em aberto
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {cenariosBloqueantes.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    Nenhum cenário bloqueante em aberto.
                                </p>
                            )}
                            {cenariosBloqueantes.map((cenario) => (
                                <a
                                    key={cenario.id}
                                    href={testesShow(cenario.teste.id).url}
                                    className="block rounded-md p-2 text-sm hover:bg-muted/50"
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="truncate">
                                            {cenario.titulo}
                                        </span>
                                        <StatusBadge status={cenario.status} />
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {cenario.teste.titulo ??
                                            `${cenario.teste.repo_name}#${cenario.teste.issue_number}`}
                                    </p>
                                </a>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Testes recentes</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {testesRecentes.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    Nenhum teste criado ainda.
                                </p>
                            )}
                            {testesRecentes.map((teste) => (
                                <a
                                    key={teste.id}
                                    href={testesShow(teste.id).url}
                                    className="flex items-center justify-between gap-2 rounded-md p-2 text-sm hover:bg-muted/50"
                                >
                                    <span className="truncate">
                                        {teste.titulo ??
                                            `${teste.repo_name}#${teste.issue_number}`}
                                    </span>
                                    <StatusBadge status={teste.status} />
                                </a>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Biblioteca de Casos de Teste</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-1">
                            <p className="text-3xl font-semibold">
                                {casosDeTeste.total}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {casosDeTeste.naoUtilizados} ainda não
                                utilizados em nenhum cenário
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
