import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React from 'react';
import { useState, useMemo, useCallback } from 'react';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEchoModel } from "@laravel/echo-react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];


export default function Dashboard () {

    useEchoModel("App.Models.Node", "1", ["App\\Events\\DatapointCreatedEvent"], (e) => {
        console.log(e);

    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            Starting from scratch
        </AppLayout>
    );
};
