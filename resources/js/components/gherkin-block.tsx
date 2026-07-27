import { PALAVRA_CHAVE_LABELS } from '@/lib/gherkin';
import { cn } from '@/lib/utils';
import type { CasoDeTestePasso } from '@/types';

type GherkinBlockProps = {
    titulo?: string;
    passos: Pick<CasoDeTestePasso, 'palavra_chave' | 'texto'>[];
    className?: string;
};

export default function GherkinBlock({
    titulo,
    passos,
    className,
}: GherkinBlockProps) {
    return (
        <div
            className={cn(
                'rounded-md bg-muted p-4 font-mono text-sm whitespace-pre-wrap',
                className,
            )}
        >
            {titulo && <p className="mb-2 font-semibold">{titulo}</p>}
            {passos.map((passo, index) => (
                <p key={index}>
                    {PALAVRA_CHAVE_LABELS[passo.palavra_chave]} {passo.texto}
                </p>
            ))}
        </div>
    );
}
