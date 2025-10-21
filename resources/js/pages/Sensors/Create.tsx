import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import Layout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sensors',
        href: '/sensors',
    },
    {
        title: 'Create',
        href: '/sensors/create',
    },
];

type SensorForm = {
    name: string;
};

export default function Create() {
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm<Required<SensorForm>>({
        name: '',
    });
    const [showDownloadModal, setShowDownloadModal] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        setShowDownloadModal(true);
    };

    const handleDownload = () => {
        // Create a form and submit it to trigger file download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/sensors';

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        // Add form data
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = data.name;
        form.appendChild(nameInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    return (
        <Layout breadcrumbs={breadcrumbs}>
            <Head title="Create Sensor" />

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0"></nav>
                </aside>

                <Separator className="my-6 md:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        <div className="space-y-6">
                            <HeadingSmall title="Create a sensor" description="Enter the sensor details to create a new sensor" />

                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoComplete="name"
                                        placeholder="Sensor name"
                                    />
                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>{processing ? 'Creating...' : 'Create Sensor'}</Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600 dark:text-neutral-400">Sensor created successfully!</p>
                                    </Transition>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>

            <Dialog open={showDownloadModal} onOpenChange={setShowDownloadModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Sensor Created Successfully</DialogTitle>
                        <DialogDescription>
                            Your sensor configuration file is ready to download. This file will only be available here and cannot be re-downloaded
                            once you leave this page.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="flex flex-col gap-4 py-4">
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            Please download your sensor configuration file now. You will not be able to access it again after closing this dialog.
                        </p>
                        <Button onClick={handleDownload} disabled={processing}>
                            {processing ? 'Generating...' : 'Download Configuration File'}
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </Layout>
    );
}
