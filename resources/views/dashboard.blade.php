@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $greeting = now()->format('H') < 12 ? 'Good morning' : (now()->format('H') < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="w-full max-w-full sm:max-w-3xl md:max-w-5xl lg:max-w-7xl mx-auto py-4 px-2 sm:px-4 pb-12" x-data="dashboardData()" x-init="loadDashboardData()">
    <!-- Page header -->
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm sm:text-base text-gray-600">
                {{ $greeting }}, <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                <span class="text-gray-400 mx-1">·</span>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </p>
        </div>
        <a href="{{ route('sales.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New sale
        </a>
    </div>

    <!-- Error banner -->
    <div x-show="loadError" x-cloak class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center justify-between gap-3">
        <span x-text="loadError"></span>
        <button type="button" @click="loadDashboardData()" class="shrink-0 font-semibold text-red-900 hover:underline">Retry</button>
    </div>

    <!-- KPI grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $user->role === 'admin' ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }} gap-4 mb-6">
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5" x-show="role !== 'staff'">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Inventory value</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900">₱<span x-text="formatNumber(summary.inventoryValue)"></span></p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sales today</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-emerald-700">₱<span x-text="formatNumber(summary.salesToday)"></span></p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This week</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-teal-700">₱<span x-text="formatNumber(summary.salesThisWeek)"></span></p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-teal-100 text-teal-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This month</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-cyan-800">₱<span x-text="formatNumber(summary.salesThisMonth)"></span></p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100 text-cyan-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
        </div>
        @if($user->role === 'admin')
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active branches</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-violet-800" x-text="summary.activeBranches ?? '0'"></p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
        </div>
        @endif
    </div>

    <!-- SQ / PO pipeline -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-8" x-show="role === 'admin' || role === 'manager'">
        <a href="{{ route('sales-quotations.index') }}?status=pending_approval" class="rounded-lg border border-yellow-200 bg-yellow-50/80 px-4 py-3 hover:border-yellow-300 transition">
            <p class="text-xs font-medium text-yellow-900/80">SQ awaiting approval</p>
            <p class="text-xl font-bold text-yellow-950 tabular-nums" x-text="pipeline.quotationsPendingApproval ?? '0'"></p>
        </a>
        <a href="{{ route('sales-quotations.index') }}?status=draft" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 hover:border-slate-300 transition">
            <p class="text-xs font-medium text-slate-600">SQ drafts</p>
            <p class="text-xl font-bold text-slate-900 tabular-nums" x-text="pipeline.quotationsDraft ?? '0'"></p>
        </a>
        <a href="{{ route('sales-quotations.index') }}?status=approved" class="rounded-lg border border-teal-200 bg-teal-50/80 px-4 py-3 hover:border-teal-300 transition">
            <p class="text-xs font-medium text-teal-800/80">SQ approved (no sale)</p>
            <p class="text-xl font-bold text-teal-900 tabular-nums" x-text="pipeline.quotationsApprovedOpen ?? '0'"></p>
        </a>
        <a href="{{ route('purchases.index') }}?status=draft" class="rounded-lg border border-violet-200 bg-violet-50/80 px-4 py-3 hover:border-violet-300 transition">
            <p class="text-xs font-medium text-violet-800/80">Open POs (draft)</p>
            <p class="text-xl font-bold text-violet-900 tabular-nums" x-text="pipeline.purchaseOrdersDraft ?? '0'"></p>
        </a>
        <a href="{{ route('purchases.index') }}?status=received" class="rounded-lg border border-blue-200 bg-blue-50/80 px-4 py-3 hover:border-blue-300 transition">
            <p class="text-xs font-medium text-blue-800/80">PO received today</p>
            <p class="text-xl font-bold text-blue-900 tabular-nums" x-text="pipeline.purchaseOrdersReceivedToday ?? '0'"></p>
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-4 py-3">
            <p class="text-xs font-medium text-amber-800/80">Low stock SKUs</p>
            <p class="text-xl font-bold text-amber-900 tabular-nums" x-text="summary.lowStockCount ?? '0'"></p>
        </div>
        <div class="rounded-lg border border-rose-100 bg-rose-50/80 px-4 py-3">
            <p class="text-xs font-medium text-rose-800/80">Out of stock</p>
            <p class="text-xl font-bold text-rose-900 tabular-nums" x-text="summary.outOfStockCount ?? '0'"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-xs font-medium text-slate-600">Sales today (count)</p>
            <p class="text-xl font-bold text-slate-900 tabular-nums" x-text="summary.transactionsToday ?? '0'"></p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-xs font-medium text-slate-600">Inventory lines</p>
            <p class="text-xl font-bold text-slate-900 tabular-nums" x-text="summary.productsTracked ?? '0'"></p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
        <!-- Main column -->
        <div class="xl:col-span-2 space-y-8">
            <!-- Branch performance -->
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 px-4 sm:px-6 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Branch performance</h2>
                        <p class="text-sm text-slate-500 mt-0.5" x-text="branchPeriodLabel"></p>
                    </div>
                    <select x-model="branchTableFilter" @change="loadDashboardData()" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-auto min-w-[140px]">
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <div x-show="loading" class="flex justify-center items-center py-16 text-slate-500">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-indigo-600"></div>
                        <span>Loading…</span>
                    </div>
                </div>

                <div x-show="!loading && branches.length === 0" class="px-6 py-12 text-center text-slate-500 text-sm">
                    No active branches to show.
                </div>

                <div x-show="!loading && branches.length > 0" class="overflow-x-auto">
                    <table class="min-w-[640px] w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 sm:px-6 py-3">Branch</th>
                                <th class="px-4 sm:px-6 py-3">Sales</th>
                                <th class="px-4 sm:px-6 py-3" x-show="role !== 'staff'">Inv. value</th>
                                <th class="px-4 sm:px-6 py-3">Low</th>
                                <th class="px-4 sm:px-6 py-3">Out</th>
                                <th class="px-4 sm:px-6 py-3">Last activity</th>
                                <th class="px-4 sm:px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="branch in branches" :key="branch.id">
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 sm:px-6 py-3 font-semibold text-slate-900" x-text="branch.name"></td>
                                    <td class="px-4 sm:px-6 py-3 font-semibold tabular-nums text-emerald-700">₱<span x-text="formatNumber(branch.sales)"></span></td>
                                    <td class="px-4 sm:px-6 py-3 tabular-nums text-slate-700" x-show="role !== 'staff'">₱<span x-text="formatNumber(branch.inventoryValue)"></span></td>
                                    <td class="px-4 sm:px-6 py-3 tabular-nums text-amber-700 font-medium" x-text="branch.lowStock || '0'"></td>
                                    <td class="px-4 sm:px-6 py-3 tabular-nums text-rose-700 font-medium" x-text="branch.outOfStock || '0'"></td>
                                    <td class="px-4 sm:px-6 py-3 text-slate-500 text-xs sm:text-sm" x-text="branch.lastActivity || '—'"></td>
                                    <td class="px-4 sm:px-6 py-3 text-right">
                                        <a :href="`/inventory/${branch.id}`" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Inventory</a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Low stock -->
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
                <div class="border-b border-slate-100 px-4 sm:px-6 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Low stock (reorder soon)</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Items at or below reorder level with stock remaining</p>
                </div>
                <div x-show="!loading && inventoryAlerts.length === 0" class="px-6 py-10 text-center text-slate-500 text-sm">No low-stock alerts. You’re in good shape.</div>
                <div x-show="inventoryAlerts.length > 0" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-4 sm:px-6 py-3">Product</th>
                                <th class="px-4 sm:px-6 py-3">Branch</th>
                                <th class="px-4 sm:px-6 py-3">Stock</th>
                                <th class="px-4 sm:px-6 py-3">Reorder at</th>
                                <th class="px-4 sm:px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="alert in inventoryAlerts" :key="alert.id">
                                <tr :class="alert.stock <= alert.minStock ? 'bg-amber-50/50' : ''">
                                    <td class="px-4 sm:px-6 py-3 font-medium text-slate-900" x-text="alert.product"></td>
                                    <td class="px-4 sm:px-6 py-3 text-slate-600" x-text="alert.branch"></td>
                                    <td class="px-4 sm:px-6 py-3 font-semibold tabular-nums" :class="alert.stock < alert.minStock ? 'text-rose-700' : 'text-slate-800'" x-text="formatNumber(alert.stock)"></td>
                                    <td class="px-4 sm:px-6 py-3 tabular-nums text-slate-600" x-text="formatNumber(alert.minStock)"></td>
                                    <td class="px-4 sm:px-6 py-3 text-right">
                                        <a :href="`/inventory/${alert.branchId}`" class="inline-flex rounded-md bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-900 hover:bg-amber-400">Restock</a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Out of stock -->
            <section class="rounded-xl border border-rose-200/60 bg-white shadow-sm ring-1 ring-rose-900/5 overflow-hidden">
                <div class="border-b border-rose-100 bg-rose-50/30 px-4 sm:px-6 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Out of stock</h2>
                    <p class="text-sm text-slate-600 mt-0.5">Zero on hand — prioritize receiving or transfers</p>
                </div>
                <div x-show="!loading && outOfStockItems.length === 0" class="px-6 py-10 text-center text-slate-500 text-sm">No zero-stock lines in your scope.</div>
                <div x-show="outOfStockItems.length > 0" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-rose-50/50 text-left text-xs font-semibold uppercase tracking-wide text-rose-900/70">
                                <th class="px-4 sm:px-6 py-3">Product</th>
                                <th class="px-4 sm:px-6 py-3">Branch</th>
                                <th class="px-4 sm:px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100/80">
                            <template x-for="row in outOfStockItems" :key="row.id">
                                <tr class="hover:bg-rose-50/20">
                                    <td class="px-4 sm:px-6 py-3 font-medium text-slate-900" x-text="row.product"></td>
                                    <td class="px-4 sm:px-6 py-3 text-slate-600" x-text="row.branch"></td>
                                    <td class="px-4 sm:px-6 py-3 text-right">
                                        <a :href="`/inventory/${row.branchId}`" class="text-sm font-semibold text-rose-700 hover:text-rose-900">Open branch</a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Side column -->
        <div class="space-y-8">
            <!-- Recent sales -->
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 sm:px-5 py-4">
                    <h2 class="text-base font-bold text-slate-900">Recent sales</h2>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                <ul class="divide-y divide-slate-100 max-h-[28rem] overflow-y-auto" x-show="recentSales.length > 0">
                    <template x-for="sale in recentSales" :key="sale.id">
                        <li>
                            <a :href="`/sales/${sale.id}/edit`" class="flex flex-col gap-1 px-4 sm:px-5 py-3 hover:bg-slate-50 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-semibold text-slate-900 text-sm" x-text="sale.reference"></span>
                                    <span class="text-sm font-bold tabular-nums text-emerald-700 shrink-0">₱<span x-text="formatNumber(sale.amount)"></span></span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500">
                                    <span x-text="sale.branch"></span>
                                    <span class="text-slate-300">·</span>
                                    <span x-text="sale.customer"></span>
                                </div>
                                <span class="text-xs text-slate-400" x-text="sale.time"></span>
                            </a>
                        </li>
                    </template>
                </ul>
                <p x-show="!loading && recentSales.length === 0" class="px-5 py-10 text-center text-sm text-slate-500">No sales yet.</p>
            </section>

            <!-- Activity (admin & manager) -->
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden" x-show="role === 'admin' || role === 'manager'">
                <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                    <h2 class="text-base font-bold text-slate-900">Activity</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Recent sales and inventory updates</p>
                </div>
                <ul class="divide-y divide-slate-100">
                    <template x-for="log in activityLog" :key="log.id">
                        <li class="px-4 sm:px-5 py-3">
                            <p class="text-xs text-slate-400" x-text="log.time"></p>
                            <p class="text-sm text-slate-800 mt-1"><span class="font-semibold text-slate-700" x-text="log.user"></span> — <span x-text="log.action"></span></p>
                        </li>
                    </template>
                </ul>
                <p x-show="(role === 'admin' || role === 'manager') && !loading && activityLog.length === 0" class="px-5 py-8 text-center text-sm text-slate-500">No recent activity.</p>
            </section>

            <!-- Quick links -->
            <section class="rounded-xl border border-slate-200/80 bg-gradient-to-b from-slate-50 to-white p-4 sm:p-5 shadow-sm ring-1 ring-slate-900/5">
                <h2 class="text-base font-bold text-slate-900 mb-3">Shortcuts</h2>
                <nav class="flex flex-col gap-2">
                    <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-indigo-300 hover:bg-indigo-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 text-xs">Inv</span>
                        Inventory
                    </a>
                    <a href="{{ route('sales.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-emerald-300 hover:bg-emerald-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 text-xs">$</span>
                        Sales
                    </a>
                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-amber-300 hover:bg-amber-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-100 text-amber-800 text-xs">Exp</span>
                        Expenses
                    </a>
                    @if(in_array($user->role, ['admin', 'manager'], true))
                    <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-violet-300 hover:bg-violet-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-100 text-violet-800 text-xs">PO</span>
                        Purchases
                    </a>
                    <a href="{{ route('sales-quotations.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-teal-300 hover:bg-teal-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-100 text-teal-800 text-xs">SQ</span>
                        Quotations
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-slate-400 hover:bg-slate-100 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-200 text-slate-800 text-xs">Rpt</span>
                        Reports
                    </a>
                    @endif
                    @if($user->role === 'admin')
                    <a href="{{ route('branches.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-blue-300 hover:bg-blue-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-100 text-blue-800 text-xs">Br</span>
                        Branches
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 hover:border-green-300 hover:bg-green-50/40 transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-green-100 text-green-800 text-xs">Usr</span>
                        Users
                    </a>
                    @endif
                </nav>
            </section>
        </div>
    </div>
</div>

<script>
function dashboardData() {
    const periodLabels = { today: 'Sales totals for today', week: 'Sales totals for this week', month: 'Sales totals for this month' };
    return {
        role: @json($user->role),
        loading: true,
        loadError: '',
        branchTableFilter: 'today',
        summary: {
            inventoryValue: 0,
            salesToday: 0,
            salesThisWeek: 0,
            salesThisMonth: 0,
            activeBranches: 0,
            lowStockCount: 0,
            outOfStockCount: 0,
            transactionsToday: 0,
            productsTracked: 0,
        },
        pipeline: {
            quotationsPendingApproval: 0,
            quotationsDraft: 0,
            quotationsApprovedOpen: 0,
            purchaseOrdersDraft: 0,
            purchaseOrdersReceivedToday: 0,
        },
        branches: [],
        inventoryAlerts: [],
        outOfStockItems: [],
        recentSales: [],
        activityLog: [],

        get branchPeriodLabel() {
            return periodLabels[this.branchTableFilter] || periodLabels.today;
        },

        formatNumber(value) {
            const num = Number(value || 0);
            if (Number.isNaN(num)) return '0';
            return num.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        async loadDashboardData() {
            try {
                this.loading = true;
                this.loadError = '';
                const period = this.branchTableFilter || 'today';
                const response = await fetch('/api/dashboard/data?period=' + encodeURIComponent(period), {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Could not load dashboard data (' + response.status + ')');
                }

                const data = await response.json();

                this.summary = { ...this.summary, ...(data.summary || {}) };
                this.pipeline = { ...this.pipeline, ...(data.pipeline || {}) };
                this.branches = data.branches || [];
                this.inventoryAlerts = data.inventoryAlerts || [];
                this.outOfStockItems = data.outOfStockItems || [];
                this.recentSales = data.recentSales || [];
                this.activityLog = data.activityLog || [];
            } catch (error) {
                console.error('Dashboard load error:', error);
                this.loadError = error.message || 'Something went wrong.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endsection
