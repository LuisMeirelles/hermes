import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { CenarioStatus, TesteStatus } from '@/types';

type Status = CenarioStatus | TesteStatus;

const STATUS_CONFIG: Record<Status, { label: string; className: string }> = {
    a_fazer: { label: 'A Fazer', className: 'bg-muted text-muted-foreground' },
    nao_iniciado: {
        label: 'Não Iniciado',
        className: 'bg-muted text-muted-foreground',
    },
    em_andamento: {
        label: 'Em Andamento',
        className:
            'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
    },
    passou: {
        label: 'Passou',
        className:
            'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300',
    },
    falhou: {
        label: 'Falhou',
        className: 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
    },
    bloqueado: {
        label: 'Bloqueado',
        className:
            'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    },
    parcial: {
        label: 'Parcial',
        className:
            'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300',
    },
};

export default function StatusBadge({
    status,
    className,
}: {
    status: Status;
    className?: string;
}) {
    const config = STATUS_CONFIG[status];

    return (
        <Badge
            variant="outline"
            className={cn('border-transparent', config.className, className)}
        >
            {config.label}
        </Badge>
    );
}
