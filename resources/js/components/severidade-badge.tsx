import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { Severidade } from '@/types';

const SEVERIDADE_CONFIG: Record<
    Severidade,
    { label: string; className: string }
> = {
    bloqueante: {
        label: 'Bloqueante',
        className: 'bg-red-600 text-white dark:bg-red-700',
    },
    critica: {
        label: 'Crítica',
        className: 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
    },
    maior: {
        label: 'Maior',
        className:
            'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    },
    menor: {
        label: 'Menor',
        className:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    },
};

export default function SeveridadeBadge({
    severidade,
    className,
}: {
    severidade: Severidade;
    className?: string;
}) {
    const config = SEVERIDADE_CONFIG[severidade];

    return (
        <Badge
            variant="outline"
            className={cn('border-transparent', config.className, className)}
        >
            {config.label}
        </Badge>
    );
}
