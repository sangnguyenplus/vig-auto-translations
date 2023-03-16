<?php

Route::group(['namespace' => 'VigStudio\VigAutoTranslations\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'vig-auto-translations', 'permission' => 'vig-auto-translations.index', ], function () {
            Route::get('theme', [
                'as' => 'vig-auto-translations.theme',
                'uses' => 'VigAutoTranslationsController@getThemeTranslations',
            ]);

            Route::post('theme', [
                'as' => 'vig-auto-translations.theme.post',
                'uses' => 'VigAutoTranslationsController@postThemeTranslations',
            ]);

            Route::post('theme-all', [
                'as' => 'vig-auto-translations.theme.post-all',
                'uses' => 'VigAutoTranslationsController@postThemeAllTranslations',
            ]);
        });
    });
});
