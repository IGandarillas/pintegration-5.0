@extends('layouts.master')

@section('content')

    <div class="row vertical-center ">
            <div class="container">
                <div class="row">
                    <div class='col-sm-6 col-sm-offset-3'>

                        <h5 class="">Sincronizar desde una fecha concreta. Por defecto, fecha de la ultima actualizacion.</h5>
                        <div class="jumbotron">
                            @if(isset($user))
                                {!! Form::model($user, ['route' => ['synchronization.syncproductssince', $user->id], 'method' => 'put'] ) !!}

                            @else
                                {!! Form::open(array('url' => '/entrar', 'class' => 'form')) !!}

                            @endif

                            {!! Form::label('Primera sincronización.') !!}
                        <div class="form-group">
                            <div class="inline-group">
                                {!! Form::submit('Sincronizar productos', array('class' => 'btn btn-default btn-width')) !!}
                                <div class='input-group date datetimepicker' >
                                    {!! Form::datetime('last_products_sync',null,array('type'=>'text', 'class'=>"form-control")) !!}
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                                @if(isset($user))
                                    {!! Form::model($user, ['route' => ['synchronization.syncclientssince', $user->id], 'method' => 'put'] ) !!}

                                @else
                                    {!! Form::open(array('url' => '/entrar', 'class' => 'form')) !!}

                                @endif

                                {!! Form::label('Primera sincronización.') !!}
                        <div class="form-group">
                            <div class="inline-group ">
                                {!! Form::submit('Sincronizar clientes',array('class' => 'btn btn-default btn-width')) !!}
                                <div class='input-group date datetimepicker' >
                                    {!! Form::datetime('last_clients_sync',null,array('type'=>'text', 'class'=>"form-control")) !!}
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                                </div>
                            </div>
                        </div>
                                {!! Form::close() !!}
                    </div>
                </div>

                <div class="row">
                    <div class='col-sm-6 col-sm-offset-3'>

                        @if(isset($user))
                            {!! Form::model($user, ['route' => ['synchronization.update', $user->id], 'method' => 'put'] ) !!}

                        @else
                            {!!  Form::open(array('route' => 'synchronization.store','class'=>'form')) !!}

                        @endif
                        <h5>Frecuencia de sincronizacion con prestashop.</h5>
                        <div class="jumbotron ">
                            <div class="form-group ">
                                {!! Form::label('Productos',null,['class'=>'btn-width']) !!}
                                {!! Form::select('freq_products',
                                [
                                    '0' => 'Nunca',
                                    '1' => 'Cada minuto',
                                    '2' => 'Cada dia',
                                    '3' => 'Cada semana',
                                    '4' => 'Cada mes'
                                 ],$user->configuration->freq_products,array('class'=>"btn-width btn btn-default align-left")) !!}
                            </div>
                            <div class="form-group ">
                                {!! Form::label('Clientes',null,['class'=>'btn-width']) !!}
                                {!! Form::select('freq_clients',
                                [
                                    '0' => 'Nunca',
                                    '1' => 'Cada minuto',
                                    '2' => 'Cada dia',
                                    '3' => 'Cada semana',
                                    '4' => 'Cada mes'
                                 ],$user->configuration->freq_clients,array('class'=>"btn-width btn btn-default align-left")) !!}
                            </div>
                            <div class="form-group text-center">
                                {!! Form::submit('Guardar', array('class'=>' btn btn-primary')) !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class='col-sm-6 col-sm-offset-3'>
                        <h5>Primer uso</h5>
                        <div class="jumbotron">
                            <div class="form-group">

                                {!! link_to_route('synchronization.syncallproducts','Sincronizar productos',null,array('class' => 'btn btn-default')) !!}
                                {!! link_to_route('synchronization.syncallclients','Sincronizar clientes',null,array('class' => 'btn btn-default')) !!}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <h1></h1>
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        Errores:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


        </div>
        <script type="text/javascript">
            $(function () {
                $('.datetimepicker').datetimepicker({
                    format: 'YYYY/MM/DD HH:mm:ss'
                });
            });
        </script>

</div>



@endsection
