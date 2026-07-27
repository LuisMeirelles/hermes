import type { ReactNode } from 'react';
import { useState } from 'react';
import CasoDeTesteController from '@/actions/App/Http/Controllers/CasoDeTesteController';
import GherkinStepEditor from '@/components/gherkin-step-editor';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { getXsrfTokenFromCookie } from '@/lib/csrf';
import {
    buildPassosPayload,
    createEmptyGherkinFormState,
    hasStepInEveryFase,
} from '@/lib/gherkin';
import type { GherkinFormState } from '@/lib/gherkin';
import type { CasoDeTeste, Severidade } from '@/types';

const SEVERIDADE_OPTIONS: { value: Severidade; label: string }[] = [
    { value: 'bloqueante', label: 'Bloqueante' },
    { value: 'critica', label: 'Crítica' },
    { value: 'maior', label: 'Maior' },
    { value: 'menor', label: 'Menor' },
];

type AdicionarCenariosDialogProps = {
    casosDeTeste: CasoDeTeste[];
    onConfirm: (
        casos: { caso_de_teste_id: number; severidade: Severidade }[],
    ) => void;
    trigger: ReactNode;
};

export default function AdicionarCenariosDialog({
    casosDeTeste,
    onConfirm,
    trigger,
}: AdicionarCenariosDialogProps) {
    const [open, setOpen] = useState(false);
    const [modo, setModo] = useState<'lista' | 'criar'>('lista');
    const [busca, setBusca] = useState('');
    const [severidades, setSeveridades] = useState<Record<number, Severidade>>(
        {},
    );
    const [criados, setCriados] = useState<CasoDeTeste[]>([]);

    const todosOsCasos = [...criados, ...casosDeTeste];
    const filtrados = todosOsCasos.filter((caso) =>
        caso.titulo.toLowerCase().includes(busca.toLowerCase()),
    );
    const selecionados = Object.keys(severidades).map(Number);

    function toggle(casoId: number, checked: boolean) {
        setSeveridades((current) => {
            const next = { ...current };

            if (checked) {
                next[casoId] = next[casoId] ?? 'maior';
            } else {
                delete next[casoId];
            }

            return next;
        });
    }

    function aplicarSeveridadeATodos(severidade: Severidade) {
        setSeveridades((current) =>
            Object.fromEntries(
                Object.keys(current).map((id) => [id, severidade]),
            ),
        );
    }

    function confirmar() {
        onConfirm(
            selecionados.map((casoDeTesteId) => ({
                caso_de_teste_id: casoDeTesteId,
                severidade: severidades[casoDeTesteId],
            })),
        );
        setSeveridades({});
        setBusca('');
        setCriados([]);
        setOpen(false);
    }

    function aoCriar(novoCaso: CasoDeTeste) {
        setCriados((current) => [novoCaso, ...current]);
        setSeveridades((current) => ({ ...current, [novoCaso.id]: 'maior' }));
        setBusca('');
        setModo('lista');
    }

    return (
        <Dialog
            open={open}
            onOpenChange={(novoOpen) => {
                setOpen(novoOpen);

                if (!novoOpen) {
                    setModo('lista');
                }
            }}
        >
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="sm:max-w-xl">
                {modo === 'lista' ? (
                    <>
                        <DialogHeader>
                            <DialogTitle>Adicionar Cenários</DialogTitle>
                        </DialogHeader>

                        <Input
                            value={busca}
                            onChange={(event) => setBusca(event.target.value)}
                            placeholder="Buscar Caso de Teste..."
                        />

                        {filtrados.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                Nenhum Caso de Teste encontrado.
                            </p>
                        )}

                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => setModo('criar')}
                        >
                            Cadastrar novo Caso de Teste
                        </Button>

                        {selecionados.length > 0 && (
                            <div className="flex items-center gap-2 text-sm">
                                <span className="text-muted-foreground">
                                    Aplicar severidade a todos selecionados:
                                </span>
                                <Select
                                    onValueChange={(value) =>
                                        aplicarSeveridadeATodos(
                                            value as Severidade,
                                        )
                                    }
                                >
                                    <SelectTrigger size="sm" className="w-40">
                                        <SelectValue placeholder="Severidade" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {SEVERIDADE_OPTIONS.map((opcao) => (
                                            <SelectItem
                                                key={opcao.value}
                                                value={opcao.value}
                                            >
                                                {opcao.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <div className="max-h-80 space-y-1 overflow-y-auto">
                            {filtrados.map((caso) => (
                                <div
                                    key={caso.id}
                                    className="flex items-center gap-3 rounded-md p-2 hover:bg-muted/50"
                                >
                                    <Checkbox
                                        checked={caso.id in severidades}
                                        onCheckedChange={(checked) =>
                                            toggle(caso.id, checked === true)
                                        }
                                    />
                                    <div className="flex-1">
                                        <p className="text-sm">{caso.titulo}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {caso.passos_count ??
                                                caso.passos.length}{' '}
                                            passo(s)
                                        </p>
                                    </div>
                                    {caso.id in severidades && (
                                        <Select
                                            value={severidades[caso.id]}
                                            onValueChange={(value) =>
                                                setSeveridades((current) => ({
                                                    ...current,
                                                    [caso.id]:
                                                        value as Severidade,
                                                }))
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
                                                            key={opcao.value}
                                                            value={opcao.value}
                                                        >
                                                            {opcao.label}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                    )}
                                </div>
                            ))}
                        </div>

                        <DialogFooter>
                            <span className="mr-auto self-center text-sm text-muted-foreground">
                                {selecionados.length} selecionado(s)
                            </span>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setOpen(false)}
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="button"
                                disabled={selecionados.length === 0}
                                onClick={confirmar}
                            >
                                Confirmar
                            </Button>
                        </DialogFooter>
                    </>
                ) : (
                    <CriarCasoDeTesteForm
                        onCancel={() => setModo('lista')}
                        onCreated={aoCriar}
                    />
                )}
            </DialogContent>
        </Dialog>
    );
}

type CriarCasoDeTesteFormProps = {
    onCancel: () => void;
    onCreated: (casoDeTeste: CasoDeTeste) => void;
};

function CriarCasoDeTesteForm({
    onCancel,
    onCreated,
}: CriarCasoDeTesteFormProps) {
    const [titulo, setTitulo] = useState('');
    const [passos, setPassos] = useState<GherkinFormState>(
        createEmptyGherkinFormState(),
    );
    const [enviando, setEnviando] = useState(false);
    const [erro, setErro] = useState<string | null>(null);

    const formValido = titulo.trim() !== '' && hasStepInEveryFase(passos);

    async function criar() {
        setEnviando(true);
        setErro(null);

        try {
            const response = await fetch(CasoDeTesteController.store().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': getXsrfTokenFromCookie(),
                },
                body: JSON.stringify({
                    titulo,
                    descricao: null,
                    passos: buildPassosPayload(passos),
                }),
            });

            if (!response.ok) {
                setErro(
                    'Não foi possível criar o Caso de Teste. Confira o título e os passos.',
                );

                return;
            }

            const novoCaso = (await response.json()) as CasoDeTeste;
            onCreated(novoCaso);
        } catch {
            setErro('Não foi possível criar o Caso de Teste.');
        } finally {
            setEnviando(false);
        }
    }

    return (
        <>
            <DialogHeader>
                <DialogTitle>Cadastrar novo Caso de Teste</DialogTitle>
            </DialogHeader>

            <div className="space-y-4">
                <div className="grid gap-2">
                    <Label htmlFor="novo-caso-titulo">Título</Label>
                    <Input
                        id="novo-caso-titulo"
                        value={titulo}
                        onChange={(event) => setTitulo(event.target.value)}
                        disabled={enviando}
                    />
                </div>

                <GherkinStepEditor
                    titulo={titulo || undefined}
                    value={passos}
                    onChange={setPassos}
                    disabled={enviando}
                />

                {erro && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {erro}
                    </p>
                )}
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onCancel}
                    disabled={enviando}
                >
                    Voltar
                </Button>
                <Button
                    type="button"
                    disabled={enviando || !formValido}
                    onClick={criar}
                >
                    Criar e selecionar
                </Button>
            </DialogFooter>
        </>
    );
}
