@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="widget meta-boxes">
        <div class="widget-title">
            <h4>&nbsp; {{ trans('plugins/translation::translation.translations') }}</h4>
        </div>
        <div class="widget-body box-translation" v-pre>

            {!! Form::open(['role' => 'form']) !!}
            <div class="ui-select-wrapper">
                <select name="group" id="group" class="form-control ui-select group-select select-search-full">
                    @foreach ($groups as $key => $value)
                        <option value="{{ $key }}"{{ $key == $group ? ' selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
                <svg class="svg-next-icon svg-next-icon-size-16">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                </svg>
            </div>
            <br>
            {!! Form::close() !!}

            @if (!empty($group))
                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                @foreach ($locales as $locale)
                                    <th>{{ $locale }}</th>
                                @endforeach
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($translations as $key => $translation)
                                <tr id="{{ $key }}">
                                    @foreach ($locales as $locale)
                                        @php $item = $translation[$locale] ?? null @endphp
                                        <td class="text-start">
                                            <a href="#edit" class="editable status-{{ $item ? $item->status : 0 }} locale-{{ $locale }}"
                                               data-locale="{{ $locale }}" data-name="{{ $locale . '|' . $key }}"
                                               data-type="textarea" data-pk="{{ $item ? $item->id : 0 }}" data-url="{{ $editUrl }}"
                                               data-title="{{ trans('plugins/translation::translation.edit_title') }}">{!! $item ? htmlentities($item->value, ENT_QUOTES, 'UTF-8', false) : '' !!}</a>
                                        </td>
                                    @endforeach
                                    <td>
                                        <button class="btn btn-primary btn-begin-translate-auto"
                                                data-name="{{ $locale . '|' . $key }}"
                                                data-value="{{ !empty($translation['en']) ? htmlentities($translation['en']->value, ENT_QUOTES, 'UTF-8', false) : '' }}"
                                                data-reset="0"
                                                type="button"
                                                title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}">
                                            <i class="fa-sharp fa-solid fa-language"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}
                                        </button>
                                    </td>
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
        $('.editable').editable({
            mode: 'inline'
        }).on('hidden', (e, reason) => {
            let locale = $(event.currentTarget).data('locale');
            if (reason === 'save') {
                $(event.currentTarget).removeClass('status-0').addClass('status-1');
            }
            if (reason === 'save' || reason === 'nochange') {
                let $next = $(event.currentTarget).closest('tr').next().find('.editable.locale-' + locale);
                setTimeout(() => {
                    $next.editable('show');
                }, 300);
            }
        });

        $('.group-select').on('change', event => {
            let group = $(event.currentTarget).val();
            if (group) {
                window.location.href = route('vig-auto-translations.plugin') + '?group=' + encodeURI($(event.currentTarget).val());
            } else {
                window.location.href = route('vig-auto-translations.plugin');
            }
        });

        $(document).on('click', '.btn-begin-translate-auto', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).prop('disabled', true).addClass('button-loading');
            var name = $(this).data('name');
            var value = $(this).data('value');
            var reset = $(this).data('reset');

            $.ajax({
                type: 'POST',
                url: "{{ route('vig-auto-translations.plugin') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'group': "{{ $group }}",
                    'auto': !reset,
                    'value': value,
                    'name': name
                },
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message);
                    } else {
                        Botble.showError(res.message);
                    }
                    location.reload();
                    $(this).prop('disabled', false).removeClass('button-loading');
                },
                error: res => {
                    $(this).prop('disabled', false).removeClass('button-loading');
                    Botble.handleError(res);
                }
            });
        });
    </script>
@endpush
