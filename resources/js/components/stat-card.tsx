import { Link } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';
import type { RouteDefinition } from '@/wayfinder';

export default function StatCard({
    label,
    count,
    total,
    href,
    accentClassName,
}: {
    label: string;
    count: number;
    total: number;
    href: RouteDefinition<'get'>;
    accentClassName: string;
}) {
    const percent = total === 0 ? 0 : Math.round((count / total) * 100);

    return (
        <Link href={href} className="block">
            <Card className="transition-colors hover:bg-muted/50">
                <CardHeader>
                    <CardDescription>{label}</CardDescription>
                    <CardTitle className="text-3xl">{count}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className={cn('text-sm font-medium', accentClassName)}>
                        {percent}% do total
                    </p>
                </CardContent>
            </Card>
        </Link>
    );
}
