<?php

use VigStudio\VigAutoTranslations\Http\Controllers\VigAutoTranslationsController;

Route::group(['controller' => VigAutoTranslationsController::class, 'middleware' => ['web', 'core']], function () {
    Route::group([
        'prefix' => BaseHelper::getAdminPrefix() . '/vig-auto-translations',
        'middleware' => 'auth',
        'permission' => 'vig-auto-translations.index',
        'as' => 'vig-auto-translations.',
    ], function () {
        Route::get('theme', [
            'as' => 'theme',
            'uses' => 'getThemeTranslations',
        ]);

        Route::post('theme', [
            'as' => 'theme.post',
            'uses' => 'postThemeTranslations',
        ]);

        Route::post('theme-all', [
            'as' => 'theme.post-all',
            'uses' => 'postThemeAllTranslations',
        ]);
    });
});
