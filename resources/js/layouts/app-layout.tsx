import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    rightContent?: React.ReactNode;
}

export default ({ children, breadcrumbs, rightContent, ...props }: AppLayoutProps) => (
        <AppLayoutTemplate breadcrumbs={breadcrumbs} rightContent={rightContent} {...props}>
            {children}
        </AppLayoutTemplate>
);
