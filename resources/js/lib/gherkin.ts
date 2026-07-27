import type { CasoDeTestePasso, PalavraChaveGherkin } from '@/types';

export const PALAVRA_CHAVE_LABELS: Record<PalavraChaveGherkin, string> = {
    dado: 'Dado',
    quando: 'Quando',
    entao: 'Então',
    e: 'E',
    mas: 'Mas',
};

export type Fase = 'dado' | 'quando' | 'entao';

export const FASE_LABELS: Record<Fase, string> = {
    dado: 'Dado',
    quando: 'Quando',
    entao: 'Então',
};

export type FaseItem = { key: string; texto: string };

export type GherkinFormState = Record<Fase, FaseItem[]>;

function novoSlotVazio(): FaseItem {
    return { key: crypto.randomUUID(), texto: '' };
}

export function createEmptyGherkinFormState(): GherkinFormState {
    return {
        dado: [novoSlotVazio()],
        quando: [novoSlotVazio()],
        entao: [novoSlotVazio()],
    };
}

/**
 * Atualiza o texto de um slot de uma fase, mantendo o invariante de que a
 * lista sempre termina com exatamente um slot vazio: preencher o último
 * slot cria um novo vazio no final; esvaziar o penúltimo (com o último já
 * vazio) remove o slot redundante. Nunca troca a identidade (key) do slot
 * editado, para não perder o foco do campo.
 */
export function updateFaseTexto(
    fase: FaseItem[],
    index: number,
    texto: string,
): FaseItem[] {
    const atualizado = fase.map((item, i) =>
        i === index ? { ...item, texto } : item,
    );
    const ultimoIndex = atualizado.length - 1;

    if (index === ultimoIndex && texto.trim() !== '') {
        return [...atualizado, novoSlotVazio()];
    }

    if (
        index === ultimoIndex - 1 &&
        texto.trim() === '' &&
        atualizado[ultimoIndex].texto.trim() === ''
    ) {
        return atualizado.slice(0, ultimoIndex);
    }

    return atualizado;
}

/**
 * Remove um slot explicitamente (ícone de lixeira), preservando o mesmo
 * invariante de sempre haver exatamente um slot vazio ao final.
 */
export function removeFasePasso(fase: FaseItem[], index: number): FaseItem[] {
    const removido = fase.filter((_, i) => i !== index);

    if (
        removido.length === 0 ||
        removido[removido.length - 1].texto.trim() !== ''
    ) {
        return [...removido, novoSlotVazio()];
    }

    return removido;
}

/**
 * Verifica se há pelo menos um passo preenchido em cada uma das 3 fases
 * (Dado, Quando e Então) — as 3 são obrigatórias.
 */
export function hasStepInEveryFase(value: GherkinFormState): boolean {
    return (['dado', 'quando', 'entao'] as const).every((fase) =>
        value[fase].some((item) => item.texto.trim() !== ''),
    );
}

const FASES_EM_ORDEM: { fase: Fase; palavraChave: PalavraChaveGherkin }[] = [
    { fase: 'dado', palavraChave: 'dado' },
    { fase: 'quando', palavraChave: 'quando' },
    { fase: 'entao', palavraChave: 'entao' },
];

/**
 * Achata as 3 seções em uma lista única de passos, na ordem Dado -> Quando
 * -> Então, descartando slots vazios: o primeiro passo preenchido de cada
 * seção vira a palavra-chave da seção, os demais viram "E".
 */
export function buildPassosPayload(
    value: GherkinFormState,
): Pick<CasoDeTestePasso, 'palavra_chave' | 'texto'>[] {
    return FASES_EM_ORDEM.flatMap(({ fase, palavraChave }) =>
        value[fase]
            .filter((item) => item.texto.trim() !== '')
            .map((item, index) => ({
                palavra_chave:
                    index === 0 ? palavraChave : ('e' as PalavraChaveGherkin),
                texto: item.texto,
            })),
    );
}

/**
 * Inverso de `buildPassosPayload`: agrupa uma lista salva de passos de volta
 * nas 3 seções, para popular o formulário de edição. Rastreia a última fase
 * "real" (Dado/Quando/Então) vista para saber a quem um "E"/"Mas" pertence.
 */
export function groupPassosByFase(
    passos: Pick<CasoDeTestePasso, 'palavra_chave' | 'texto'>[],
): GherkinFormState {
    const grupos: Record<Fase, FaseItem[]> = {
        dado: [],
        quando: [],
        entao: [],
    };
    let faseAtual: Fase = 'dado';

    for (const passo of passos) {
        if (
            passo.palavra_chave === 'dado' ||
            passo.palavra_chave === 'quando' ||
            passo.palavra_chave === 'entao'
        ) {
            faseAtual = passo.palavra_chave;
        }

        grupos[faseAtual].push({
            key: crypto.randomUUID(),
            texto: passo.texto,
        });
    }

    (['dado', 'quando', 'entao'] as const).forEach((fase) => {
        grupos[fase].push(novoSlotVazio());
    });

    return grupos;
}
