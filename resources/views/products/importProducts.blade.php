@extends('layouts.master')

@section('content')

    <div class="row vertical-center ">
        <div class="col-md-6 ">

            {!! Form::open (array('url' => '/products', 'class' => 'form', 'method' => 'POST', 'files'=>'true')) !!}

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

            <div class="form-group">
                {!! Form::label('Csv productos') !!}
                {!! Form::file('csvproducts', ['class' => 'field', 'accept'=>'.csv' ]) !!}
                <p class="errors">{!!$errors->first('csvproducts')!!}</p>

            </div>
            <div class="form-group text-center">
                {!! Form::submit('Guardar', array('class'=>' btn btn-primary')) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>

@endsection
