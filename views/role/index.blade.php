@extends('kit::backend.layouts.master')
@section('content')
    <div class="ibox ibox-table">
        <div class="ibox-title">
            <h5>{!! trans('authority::common.list') !!}</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                <tr>
                    <th class="min-width">#</th>
                    <th class="text-center">{{trans('authority::common.roles')}}</th>
                    <th class="text-center min-width">{{trans('authority::common.level')}}</th>
                    <th class="text-center min-width">{{trans('authority::common.attached')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($roles as $group => $items)
                    <tr>
                        <td colspan="4" class="text-uppercase text-primary bg-warning">
                            <strong>{{trans("authority::role.{$group}.title")}}</strong>
                        </td>
                    </tr>
                    <?php $i = 1; ?>
                    @foreach($items as $name => $role)
                        <tr>
                            <td class="min-width">{{$i++}}</td>
                            <td><a href="{{$role->url}}">{{$role->title}}</a></td>
                            <td class="min-width">{{$role->level}}</td>
                            <td class="min-width text-center text-danger">
                                <strong>{{$countUsers[$group][$name]}}</strong>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
            <div class="alert alert-success"><em>{{trans('authority::common.note')}}</em></div>
        </div>
    </div>
@endsection