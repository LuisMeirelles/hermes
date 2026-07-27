import { cn } from '@/lib/utils';

export default function StatusBreakdownBar({
    segments,
}: {
    segments: { label: string; count: number; className: string }[];
}) {
    const total = segments.reduce((sum, segment) => sum + segment.count, 0);

    return (
        <div className="space-y-2">
            <div className="flex h-3 w-full overflow-hidden rounded-full bg-muted">
                {segments.map((segment) => (
                    <div
                        key={segment.label}
                        title={`${segment.label}: ${segment.count}`}
                        className={segment.className}
                        style={{
                            width:
                                total === 0
                                    ? 0
                                    : `${(segment.count / total) * 100}%`,
                        }}
                    />
                ))}
            </div>
            <div className="flex flex-wrap gap-4 text-xs text-muted-foreground">
                {segments.map((segment) => (
                    <span
                        key={segment.label}
                        className="flex items-center gap-1.5"
                    >
                        <span
                            className={cn(
                                'size-2 rounded-full',
                                segment.className,
                            )}
                        />
                        {segment.label} ({segment.count})
                    </span>
                ))}
            </div>
        </div>
    );
}
