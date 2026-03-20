<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileQuestion, FolderGit2, LayoutGrid, DollarSign, Users, UserCog } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import { usePermission } from '@/composables/usePermission';

const page = usePage();
const auth = computed(() => page.props.auth);
const { hasPermission, hasRole, isAdmin, isOwner } = usePermission();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Questions - visible for users with view-questions permission
    if (hasPermission('view-questions')) {
        items.push({
            title: 'Questions',
            href: '/questions',
            icon: FileQuestion,
        });
    }

    // Tests - visible for users with view-tests permission
    if (hasPermission('view-tests')) {
        items.push({
            title: 'My Tests',
            href: '/tests',
            icon: FolderGit2,
        });
        items.push({
            title: 'Start Test',
            href: '/test/start',
            icon: FileQuestion,
        });
    }

    // Staff Management - visible for users with manage-staff permission
    if (hasPermission('manage-staff')) {
        items.push({
            title: 'Staff',
            href: '/staff',
            icon: Users,
        });
    }

    // User Management - visible for admin only
    if (hasPermission('manage-users') || isAdmin()) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: UserCog,
        });
        items.push({
            title: 'Roles',
            href: '/roles',
            icon: Users,
        });
    }

    // Tokens - visible for admin or users with token management
    if (hasPermission('manage-tokens') || isAdmin()) {
        items.push({
            title: 'Tokens',
            href: '/tokens',
            icon: DollarSign,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [

];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
