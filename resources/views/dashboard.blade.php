@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex h-screen bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: false, activeTab: 'information' }">
    <!-- Sidebar -->
    <x-sidebar />
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black opacity-50 lg:hidden"></div>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <x-top-header />
        
        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900">
            <div class="container mx-auto px-4 py-8">
                
                <!-- Stats Card -->
                <div class="mb-8 animate-fade-in">
                    <x-banner-card 
                        count="45"
                        title="Total Banner"
                        icon="fas fa-mountain"
                        color="blue" />
                </div>
                
                <!-- Tab Navigation -->
                <div class="mb-6">
                    <x-tab-navigation />
                </div>
                
                <!-- Controls Bar -->
                <div class="mb-6">
                    <x-controls-bar />
                </div>
                
                <!-- Data Table -->
                <div class="animate-fade-in" style="animation-delay: 300ms">
                    <x-advanced-data-table />
                </div>
                
            </div>
        </main>
    </div>
</div>
@endsection