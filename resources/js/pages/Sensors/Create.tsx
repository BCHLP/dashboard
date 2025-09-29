import Layout from '@/layouts/app-layout'
import { Head, useForm } from '@inertiajs/react'
import type { BreadcrumbItem } from '@/types'
import React, { FormEventHandler } from 'react'
import { Separator } from '@/components/ui/separator'
import HeadingSmall from '@/components/heading-small'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import InputError from '@/components/input-error'
import { Button } from '@/components/ui/button'
import { Transition } from '@headlessui/react'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sensors',
        href: '/sensors',
    },{
        title: 'Create',
        href: '/sensors/create',
    },
];

type SensorForm = {
    name: string
}

export default function Create() {
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm<Required<SensorForm>>({
        name: ''
    })

    const submit: FormEventHandler = (e) => {
        e.preventDefault()

        post('/sensors', {
            preserveScroll: true,
        })
    }

    return (
        <Layout breadcrumbs={breadcrumbs}>
            <Head title="Create Sensor"/>

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                    </nav>
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
                                    <Button disabled={processing}>
                                        {processing ? 'Creating...' : 'Create Sensor'}
                                    </Button>

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
        </Layout>
    )
}
