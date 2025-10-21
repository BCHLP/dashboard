import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sensors',
        href: '/sensors',
    },
];

type Node = {
    id: number;
    name: string;
};

type Props = {
    sensors: Node[];
};

export default function Index({ sensors }: Props) {
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [sensorToDelete, setSensorToDelete] = useState<Node | null>(null);
    const { delete: destroy, processing, recentlySuccessful } = useForm();

    const handleEdit = (sensorId: number) => {
        router.visit(`/sensors/${sensorId}/edit`);
    };

    const openDeleteModal = (sensor: Node) => {
        setSensorToDelete(sensor);
        setDeleteModalOpen(true);
    };

    const closeDeleteModal = () => {
        setDeleteModalOpen(false);
        setSensorToDelete(null);
    };

    const handleDelete = () => {
        if (!sensorToDelete) return;

        destroy(`/sensors/${sensorToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
            },
        });
    };

    const rightContent = <Button onClick={() => router.visit('/sensors/create')}>Create Sensor</Button>;

    return (
        <AppLayout breadcrumbs={breadcrumbs} rightContent={rightContent}>
            <Head title="Sensors" />

            {sensors.length > 0 && (
                <div className="m-6">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Name
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            {sensors.map((sensor) => (
                                <tr key={sensor.id} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {sensor.name}
                                    </td>
                                    <td className="space-x-2 px-6 py-4 text-sm font-medium whitespace-nowrap">
                                        <Button variant="outline" size="sm" onClick={() => handleEdit(sensor.id)}>
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" onClick={() => openDeleteModal(sensor)}>
                                            Delete
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {sensors.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-gray-500">No sensors found.</p>
                    <Button className="mt-4" onClick={() => router.visit('/sensors/create')}>
                        Create Your First Sensor
                    </Button>
                </div>
            )}

            {/* Success Message */}
            <Transition
                show={recentlySuccessful}
                enter="transition ease-in-out"
                enterFrom="opacity-0"
                leave="transition ease-in-out"
                leaveTo="opacity-0"
            >
                <div className="fixed top-4 right-4 z-50 rounded-md bg-green-500 px-4 py-2 text-white shadow-lg">Sensor deleted successfully!</div>
            </Transition>

            {/* Delete Confirmation Modal */}
            <Dialog open={deleteModalOpen} onOpenChange={setDeleteModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Sensor</DialogTitle>
                        <DialogDescription>Are you sure you want to delete "{sensorToDelete?.name}"? This action cannot be undone.</DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="secondary" onClick={closeDeleteModal}>
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button variant="destructive" onClick={handleDelete} disabled={processing}>
                            {processing ? 'Deleting...' : 'Delete'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
