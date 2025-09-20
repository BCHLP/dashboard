import AppLayout from '@/layouts/app-layout'
import { Head, useForm } from '@inertiajs/react'
import type { BreadcrumbItem, Role } from '@/types'
import React, { FormEventHandler } from 'react'
import { Separator } from '@/components/ui/separator'
import HeadingSmall from '@/components/heading-small'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import InputError from '@/components/input-error'
import { Button } from '@/components/ui/button'
import { Transition } from '@headlessui/react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Create',
        href: '/users/create',
    },
]

type UserForm = {
    name: string
    email: string
    role: string
}

type Props = {
    roles: Role[];
}

export default function Create({roles}:Props) {
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm<Required<UserForm>>({
        name: '',
        email: '',
        role: ''
    })

    console.log("roles", roles);

    const submit: FormEventHandler = (e) => {
        e.preventDefault()

        post('/users', {
            preserveScroll: true,
        })
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create User" />

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                    </nav>
                </aside>

                <Separator className="my-6 md:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        <div className="space-y-6">
                            <HeadingSmall title="Create a user" description="Enter the user details to create a new account" />

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
                                        placeholder="Full name"
                                    />
                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="email"
                                        placeholder="user@example.com"
                                    />
                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="role">Role</Label>
                                    <Select value={data.role} onValueChange={(value) => setData('role', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roles.map((role) => (
                                                <SelectItem key={role.id} value={role.name}>
                                                    {role.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError className="mt-2" message={errors.role} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>
                                        {processing ? 'Creating...' : 'Create User'}
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600 dark:text-neutral-400">User created successfully!</p>
                                    </Transition>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </AppLayout>
    )
}
