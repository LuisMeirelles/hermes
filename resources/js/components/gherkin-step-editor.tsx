import {
    DndContext,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import type { DragEndEvent } from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Trash2 } from 'lucide-react';
import GherkinBlock from '@/components/gherkin-block';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    buildPassosPayload,
    FASE_LABELS,
    removeFasePasso,
    updateFaseTexto,
} from '@/lib/gherkin';
import type { Fase, FaseItem, GherkinFormState } from '@/lib/gherkin';

const FASES: Fase[] = ['dado', 'quando', 'entao'];

type GherkinStepEditorProps = {
    titulo?: string;
    value: GherkinFormState;
    onChange: (value: GherkinFormState) => void;
    disabled?: boolean;
};

export default function GherkinStepEditor({
    titulo,
    value,
    onChange,
    disabled,
}: GherkinStepEditorProps) {
    function setFase(fase: Fase, itens: FaseItem[]) {
        onChange({ ...value, [fase]: itens });
    }

    return (
        <div className="space-y-6">
            {FASES.map((fase) => (
                <FaseEditor
                    key={fase}
                    fase={fase}
                    itens={value[fase]}
                    onChange={(itens) => setFase(fase, itens)}
                    disabled={disabled}
                />
            ))}

            <div className="space-y-2">
                <p className="text-sm font-medium">Pré-visualização</p>
                <GherkinBlock
                    titulo={titulo}
                    passos={buildPassosPayload(value)}
                />
            </div>
        </div>
    );
}

type FaseEditorProps = {
    fase: Fase;
    itens: FaseItem[];
    onChange: (itens: FaseItem[]) => void;
    disabled?: boolean;
};

function FaseEditor({ fase, itens, onChange, disabled }: FaseEditorProps) {
    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 4 } }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const ultimoIndex = itens.length - 1;

    function handleDragEnd(event: DragEndEvent) {
        const { active, over } = event;

        if (!over || active.id === over.id) {
            return;
        }

        const oldIndex = itens.findIndex((item) => item.key === active.id);
        const newIndex = itens.findIndex((item) => item.key === over.id);

        if (
            oldIndex === -1 ||
            newIndex === -1 ||
            oldIndex === ultimoIndex ||
            newIndex === ultimoIndex
        ) {
            return;
        }

        onChange(arrayMove(itens, oldIndex, newIndex));
    }

    return (
        <div className="space-y-2">
            <p className="text-sm font-medium">{FASE_LABELS[fase]}</p>

            <div className="space-y-2">
                <DndContext sensors={sensors} onDragEnd={handleDragEnd}>
                    <SortableContext
                        items={itens.map((item) => item.key)}
                        strategy={verticalListSortingStrategy}
                    >
                        {itens.map((item, index) => {
                            const ultimo = index === ultimoIndex;

                            return (
                                <SortableFaseRow
                                    key={item.key}
                                    item={item}
                                    arrastavel={!ultimo}
                                    removivel={!ultimo}
                                    placeholder={
                                        ultimo
                                            ? 'Adicionar passo...'
                                            : 'Descreva o passo'
                                    }
                                    disabled={disabled}
                                    onTextoChange={(texto) =>
                                        onChange(
                                            updateFaseTexto(
                                                itens,
                                                index,
                                                texto,
                                            ),
                                        )
                                    }
                                    onRemove={() =>
                                        onChange(removeFasePasso(itens, index))
                                    }
                                />
                            );
                        })}
                    </SortableContext>
                </DndContext>
            </div>
        </div>
    );
}

type SortableFaseRowProps = {
    item: FaseItem;
    arrastavel: boolean;
    removivel: boolean;
    placeholder: string;
    disabled?: boolean;
    onTextoChange: (texto: string) => void;
    onRemove: () => void;
};

function SortableFaseRow({
    item,
    arrastavel,
    removivel,
    placeholder,
    disabled,
    onTextoChange,
    onRemove,
}: SortableFaseRowProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: item.key, disabled: !arrastavel || disabled });

    return (
        <div
            ref={setNodeRef}
            style={{
                transform: CSS.Transform.toString(transform),
                transition,
                opacity: isDragging ? 0.5 : 1,
            }}
            className="flex items-center gap-2"
        >
            <button
                type="button"
                className="cursor-grab touch-none rounded-sm text-muted-foreground outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-30"
                disabled={!arrastavel || disabled}
                {...attributes}
                {...listeners}
            >
                <GripVertical className="size-4" />
            </button>

            <Input
                value={item.texto}
                onChange={(event) => onTextoChange(event.target.value)}
                placeholder={placeholder}
                disabled={disabled}
                className="flex-1"
            />

            {removivel ? (
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    disabled={disabled}
                    onClick={onRemove}
                >
                    <Trash2 />
                </Button>
            ) : (
                <span className="size-9 shrink-0" />
            )}
        </div>
    );
}
