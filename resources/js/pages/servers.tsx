import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import '@patternfly/react-core/dist/styles/base-no-reset.css';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
];

type Node = {
    id: number;
    name: string;
};
type Props = {
    servers: Node[];
};

export default function Servers({ servers }: Props) {
    const rightContent = (
        <>
            <Button
                onClick={() => {
                    window.location.href = '/servers/create';
                }}
            >
                Create Server
            </Button>
        </>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs} rightContent={rightContent}>
            <Head title="Servers" />

            {servers.length > 0 && (
                <table>
                    <thead>
                        <tr>
                            <th>Server</th>
                        </tr>
                    </thead>
                    <tbody>
                        {servers.map((server) => (
                            <tr key={server.id}>
                                <td>{server.name}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </AppLayout>
    );
}
