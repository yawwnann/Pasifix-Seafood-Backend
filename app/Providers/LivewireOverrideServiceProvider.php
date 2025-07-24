<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomLivewireUploadController;

class LivewireOverrideServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->overrideRoute();
    }

    public function overrideRoute(): void
    {
        Route::post('/livewire/upload-file', [CustomLivewireUploadController::class, 'handle'])
            ->name('livewire.upload-file')
            ->middleware('web');
    }
}