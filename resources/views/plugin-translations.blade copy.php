@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="widget meta-boxes">
        <div class="widget-title">
            <h4>&nbsp; {{ trans('plugins/translation::translation.translations') }}</h4>
        </div>
        <div class="widget-body box-translation" v-pre>
            {!! Form::open(['role' => 'form']) !!}
            <div class="row">


                <div class="form-group col-md-6">
                    <label>GROUP</label>
                    <select name="group" id="group" data-value="group" class="form-control ui-select group-select select-search-full">
                        @foreach ($groups as $key => $value)
                            <option value="{{ $key }}"{{ $key == $group ? ' selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label>lang</label>
                    <select name="ref_lang" id="ref_lang" data-value="ref_lang" class="form-control ui-select group-select select-search-full">
                        @foreach ($localesAll as $key => $value)
                            <option value="{{ $value }}"{{ $value == request('ref_lang') ? ' selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <br>
            {!! Form::close() !!}

            @if (!empty($group))

                <button class="btn btn-warning btn-xs btn-translate-all">
                    <i class="fa-sharp fa-solid fa-language"></i> Begin Translate All Text
                </button>

                <form method="POST" action="{{ route('translations.group.publish', compact('group')) }}" class="form-inline" role="form">
                    @csrf
                    <button type="submit" class="btn btn-info button-publish-groups">{{ trans('plugins/translation::translation.publish_translations') }}</button>
                </form>

                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Key</th>
                                @foreach ($locales as $locale)
                                    <th>{{ $locale }}</th>
                                    <th></th>
                                @endforeach

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($translations as $key => $translation)
                                <tr id="{{ $key }}">
                                    <td>{{ $key }}</td>
                                    @foreach ($locales as $locale)
                                        @php $item = $translation[$locale] ?? null @endphp
                                        <td class="text-start">
                                            <br />
                                            <a href="#edit" class="editable status-{{ $item ? $item->status : 0 }} locale-{{ $locale }}"
                                               data-locale="{{ $locale }}" data-name="{{ $locale . '|' . $key }}"
                                               data-type="textarea" data-pk="{{ $item ? $item->id : 0 }}" data-url="{{ $editUrl }}"
                                               data-title="{{ trans('plugins/translation::translation.edit_title') }}">{!! $item ? htmlentities($item->value, ENT_QUOTES, 'UTF-8', false) : '' !!}</a>
                                        </td>
                                        <td>
                                            @if ($locale !== 'en')
                                                <button class="btn btn-primary btn-xs btn-begin-translate-auto"
                                                        data-name="{{ $locale . '|' . $key }}"
                                                        data-value="{{ !empty($translation['en']) ? $translation['en']->value : '' }}"
                                                        data-reset="0"
                                                        type="button"
                                                        title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}">
                                                    <i class="fa-sharp fa-solid fa-language"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-info">{{ trans('plugins/translation::translation.choose_group_msg') }}</p>
            @endif
        </div>
        <div class="clearfix"></div>
    </div>
@stop


@push('footer')
    <script>
        // $('.editable').editable({
        //     mode: 'inline'
        // }).on('hidden', (e, reason) => {
        //     let locale = $(event.currentTarget).data('locale');
        //     if (reason === 'save') {
        //         $(event.currentTarget).removeClass('status-0').addClass('status-1');
        //     }
        //     if (reason === 'save' || reason === 'nochange') {
        //         let $next = $(event.currentTarget).closest('tr').next().find('.editable.locale-' + locale);
        //         setTimeout(() => {
        //             $next.editable('show');
        //         }, 300);
        //     }
        // });

        // $('.group-select').on('change', event => {
        //     let group = $('#group').val();
        //     let ref_lang = $('#ref_lang').val();
        //     window.location.href = getRouteGetData(group, ref_lang);
        // });

        // function getRouteGetData(group, ref_lang) {
        //     return route('vig-auto-translations.plugin', {
        //         group: group,
        //         ref_lang: ref_lang
        //     })
        // }

        // $(document).on('click', '.btn-begin-translate-auto', function(event) {
        //     event.preventDefault();
        //     event.stopPropagation();
        //     $(this).prop('disabled', true).addClass('button-loading');
        //     var name = $(this).data('name');
        //     var value = $(this).data('value');
        //     var reset = $(this).data('reset');

        //     $.ajax({
        //         type: 'POST',
        //         url: "{{ route('vig-auto-translations.plugin') }}",
        //         data: {
        //             '_token': "{{ csrf_token() }}",
        //             'group': "{{ $group }}",
        //             'auto': !reset,
        //             'value': value,
        //             'name': name
        //         },
        //         success: res => {
        //             if (!res.error) {
        //                 Botble.showSuccess(res.message);
        //             } else {
        //                 Botble.showError(res.message);
        //             }
        //             location.reload();
        //             $(this).prop('disabled', false).removeClass('button-loading');
        //         },
        //         error: res => {
        //             $(this).prop('disabled', false).removeClass('button-loading');
        //             Botble.handleError(res);
        //         }
        //     });
        // });

        // $(document).on('click', '.btn-translate-all', function(event) {
        //     event.preventDefault();
        //     event.stopPropagation();
        //     $(this).prop('disabled', true).addClass('button-loading');

        //     $.ajax({
        //         type: 'POST',
        //         url: "{{ route('vig-auto-translations.plugin.all') }}",
        //         data: {
        //             '_token': "{{ csrf_token() }}",
        //             'group': "{{ $group }}",
        //             'ref_lang': "{{ request('ref_lang') }}",
        //         },
        //         success: res => {
        //             if (!res.error) {
        //                 Botble.showSuccess(res.message);
        //             } else {
        //                 Botble.showError(res.message);
        //             }
        //             location.reload();
        //             $(this).prop('disabled', false).removeClass('button-loading');
        //         },
        //         error: res => {
        //             $(this).prop('disabled', false).removeClass('button-loading');
        //             Botble.handleError(res);
        //         }
        //     });
        // });
    </script>
@endpush
