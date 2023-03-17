<?php

namespace VigStudio\VigAutoTranslations\Http\Controllers;

use Assets;
use BaseHelper;
use Theme;
use Botble\Base\Supports\Language;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Translation\Manager;
use Botble\Translation\Models\Translation;
use Botble\Translation\Http\Requests\TranslationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class VigAutoTranslationsController extends BaseController
{
    public function __construct(protected Manager $manager)
    {
    }

    public function getThemeTranslations(Request $request)
    {
        page_title()->setTitle(trans('plugins/vig-auto-translations::vig-auto-translations.title'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable'])
            ->addScriptsDirectly('vendor/core/plugins/translation/js/theme-translations.js')
            ->addStylesDirectly('vendor/core/plugins/translation/css/theme-translations.css');

        $data = $this->getDataTranslations($request->input('ref_lang'));

        $translations = $data['translations'];
        $groups = $data['groups'];
        $group = $data['group'];
        $defaultLanguage = $data['defaultLanguage'];

        return view(
            'plugins/vig-auto-translations::theme-translations',
            compact('translations', 'groups', 'group', 'defaultLanguage')
        );
    }

    public function getDataTranslations(string|null $refLang): array
    {
        $groups = Language::getAvailableLocales();
        $defaultLanguage = Arr::get($groups, 'en');

        if (! $refLang) {
            $group = $defaultLanguage;
        } else {
            $group = Arr::first(Arr::where($groups, function ($item) use ($refLang) {
                return $item['locale'] == $refLang;
            }));
        }

        $translations = [];
        if ($group) {
            $jsonFile = lang_path($group['locale'] . '.json');

            if (! File::exists($jsonFile)) {
                $jsonFile = theme_path(Theme::getThemeName() . '/lang/' . $group['locale'] . '.json');
            }

            if (! File::exists($jsonFile)) {
                $languages = BaseHelper::scanFolder(theme_path(Theme::getThemeName() . '/lang'));

                if (! empty($languages)) {
                    $jsonFile = theme_path(Theme::getThemeName() . '/lang/' . Arr::first($languages));
                }
            }

            if (File::exists($jsonFile)) {
                $translations = BaseHelper::getFileData($jsonFile);
            }

            if ($group['locale'] != 'en') {
                $defaultEnglishFile = theme_path(Theme::getThemeName() . '/lang/en.json');

                if ($defaultEnglishFile) {
                    $enTranslations = BaseHelper::getFileData($defaultEnglishFile);
                    $translations = array_merge($enTranslations, $translations);

                    $enTranslationKeys = array_keys($enTranslations);

                    foreach ($translations as $key => $translation) {
                        if (! in_array($key, $enTranslationKeys)) {
                            Arr::forget($translations, $key);
                        }
                    }
                }
            }
        }

        ksort($translations);

        return [
            'translations' => $translations,
            'groups' => $groups,
            'group' => $group,
            'defaultLanguage' => $defaultLanguage,
        ];
    }

    public function postThemeTranslations(Request $request, BaseHttpResponse $response)
    {
        if (! File::isWritable(lang_path())) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/translation::translation.folder_is_not_writeable', ['lang_path' => lang_path()]));
        }

        $locale = $request->input('pk');
        $name = $request->input('name');
        $value = $request->input('value');

        if ($request->input('auto') == 'true') {
            $value = vig_auto_translate('en', $locale, $name);
        }
        if ($locale) {
            $this->saveTranslations($locale, [$name => $value]);
        }

        return $response
        ->setPreviousUrl(route('vig-auto-translations.theme'))
        ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function saveTranslations(string $locale, array $newTranslations): void
    {
        $translations = [];

        $jsonFile = lang_path($locale . '.json');

        if (! File::exists($jsonFile)) {
            $jsonFile = theme_path(Theme::getThemeName() . '/lang/' . $locale . '.json');
        }

        if (File::exists($jsonFile)) {
            $translations = BaseHelper::getFileData($jsonFile);
        }

        if ($locale != 'en') {
            $defaultEnglishFile = theme_path(Theme::getThemeName() . '/lang/en.json');

            if ($defaultEnglishFile) {
                $enTranslations = BaseHelper::getFileData($defaultEnglishFile);
                $translations = array_merge($enTranslations, $translations);

                $enTranslationKeys = array_keys($enTranslations);

                foreach ($translations as $key => $translation) {
                    if (! in_array($key, $enTranslationKeys)) {
                        Arr::forget($translations, $key);
                    }
                }
            }
        }

        ksort($translations);

        $translations = array_combine(array_map('trim', array_keys($translations)), $translations);

        foreach ($newTranslations as $key => $value) {
            $translations[$key] = $value;
        }

        File::put(lang_path($locale . '.json'), BaseHelper::jsonEncodePrettify($translations));
    }

    public function postThemeAllTranslations(Request $request, BaseHttpResponse $response)
    {
        $locale = $request->input('locale');
        $data = $this->getDataTranslations($locale);
        $translations = $data['translations'];

        foreach ($translations as $key => $translation) {
            $translations[$key] =  vig_auto_translate('en', $locale, $key);
        }

        $this->saveTranslations($locale, $translations);

        return $response
        ->setPreviousUrl(route('vig-auto-translations.theme'))
        ->setMessage(trans('core/base::notices.update_success_message'));
    }

    //Plugin
    public function getPluginsTranslations(Request $request)
    {
        page_title()->setTitle(trans('plugins/translation::translation.translations'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable'])
            ->addStylesDirectly('vendor/core/plugins/translation/css/translation.css');

        $group = $request->input('group');

        $locales = $this->loadLocales();
        $groups = Translation::groupBy('group');
        $excludedGroups = $this->manager->getConfig('exclude_groups');
        if ($excludedGroups) {
            $groups->whereNotIn('group', $excludedGroups);
        }

        $groups = $groups->select('group')->get()->pluck('group', 'group');
        if ($groups instanceof Collection) {
            $groups = $groups->all();
        }
        $groups = ['' => trans('plugins/translation::translation.choose_a_group')] + $groups;
        $numChanged = Translation::where('group', $group)->where('status', Translation::STATUS_CHANGED)->count();

        $allTranslations = Translation::where('group', $group)->orderBy('key')->get();
        $numTranslations = count($allTranslations);
        $translations = [];
        foreach ($allTranslations as $translation) {
            $translations[$translation->key][$translation->locale] = $translation;
        }

        return view('plugins/vig-auto-translations::plugin-translations')
            ->with('translations', $translations)
            ->with('locales', $locales)
            ->with('groups', $groups)
            ->with('group', $group)
            ->with('numTranslations', $numTranslations)
            ->with('numChanged', $numChanged)
            ->with('editUrl', route('translations.group.edit', ['group' => $group]));
    }

    public function postPluginsTranslations(TranslationRequest $request, BaseHttpResponse $response)
    {
        $group = $request->input('group');

        if (! in_array($group, $this->manager->getConfig('exclude_groups'))) {
            $name = $request->input('name');
            $value = $request->input('value');

            [$locale, $key] = explode('|', $name, 2);

            if ($request->input('auto') == 'true') {
                $value = vig_auto_translate('en', $locale, $value);
            }

            $translation = Translation::firstOrNew([
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ]);
            $translation->value = (string)$value ?: null;
            $translation->status = Translation::STATUS_CHANGED;
            $translation->save();
        }

        return $response;
    }

    protected function loadLocales(): array
    {
        // Set the default locale as the first one.
        $locales = Translation::groupBy('locale')
            ->select('locale')
            ->get()
            ->pluck('locale');

        if ($locales instanceof Collection) {
            $locales = $locales->all();
        }
        $locales = array_merge([config('app.locale')], $locales);

        return array_unique($locales);
    }
}
