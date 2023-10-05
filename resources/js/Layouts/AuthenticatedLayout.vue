<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block w-20"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('purchases.create')" :active="route().current('purchases.create')">
                                    購入画面
                                </NavLink>
                                <NavLink :href="route('purchases.index')" :active="route().current('purchases.index')">
                                    購買履歴
                                </NavLink>
                                <NavLink :href="route('items.index')" :active="route().current('items.index')">
                                    商品管理
                                </NavLink>
                                <NavLink :href="route('customers.index')" :active="route().current('customers.index')">
                                    顧客管理
                                </NavLink>
                                <NavLink :href="route('analysis')" :active="route().current('analysis')">
                                    データ分析
                                </NavLink>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('purchases.create')" :active="route().current('purchases.create')">
                            購入画面
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('purchases.index')" :active="route().current('purchases.index')">
                            購買履歴
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('items.index')" :active="route().current('items.index')">
                            商品管理
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('customers.index')" :active="route().current('customers.index')">
                            顧客管理
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('analysis')" :active="route().current('analysis')">
                            データ分析
                        </ResponsiveNavLink>


                    </div>

                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
