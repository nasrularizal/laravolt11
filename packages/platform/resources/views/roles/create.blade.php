@extends(config('laravolt.epicentrum.view.layout'))

@section('page.title', __('laravolt::label.roles'))

@section('content')

    <x-laravolt::titlebar title="{{ __('laravolt::label.roles') }}">
        <x-laravolt::backlink url="{{ route('epicentrum::roles.index') }}"></x-laravolt::backlink>
    </x-laravolt::titlebar>

    <x-laravolt::panel title="{{ __('laravolt::label.add_role') }}">
        {!! SemanticForm::open()->post()->action(route('epicentrum::roles.store')) !!}
        {!! SemanticForm::text('name', old('name'))->label(trans('laravolt::roles.name'))->required() !!}

        <table class="ui table">
            <thead>
            <tr>
                <th>
                    <div class="ui checkbox" data-toggle="checkall"
                         data-selector=".checkbox[data-type='check-all-child']">
                        <input type="checkbox">
                        <label><strong>@lang('laravolt::label.permissions')</strong></label>
                    </div>
                </th>
                <th>@lang('laravolt::permissions.description')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($permissions as $permission)
                <tr>
                    <td style="width: 300px">
                        <div class="ui checkbox" data-type="check-all-child">
                            <input type="checkbox" name="permissions[]"
                                   value="{{ $permission->id }}" {{ (false)?'checked=checked':'' }}>
                            <label>{{ $permission->name }}</label>
                        </div>
                    </td>
                    <td>{{ $permission->description }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="ui divider hidden"></div>

        <x-laravolt::button>@lang('laravolt::action.save')</x-laravolt::button>
        <x-laravolt::link url="{{ route('epicentrum::roles.index') }}">@lang('laravolt::action.cancel')</x-laravolt::link>

        {!! SemanticForm::close() !!}
    </x-laravolt::panel>
@endsection
