@extends('layouts.master')

@section('content')
 <div class="row vertical-center">
  {!! Form::open(array('url' => '/auth/register',
  'class' => 'form')) !!}

  @if (count($errors) > 0)
   <div class="alert alert-danger">
    Algo fue mal:
    <ul>
     @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
     @endforeach
    </ul>
   </div>
  @endif

  <div class="form-group">
   {!! Form::label('name', 'Nombre') !!}
   {!! Form::text('name', null, array('class'=>'form-control', 'placeholder'=>'Nombre')) !!}
  </div>
  <div class="form-group">
   {!! Form::label('Email') !!}
   {!! Form::text('email', null,
   array(
   'class'=>'form-control',
   'placeholder'=>'Email')
   ) !!}
  </div>
  <div class="form-group">
   {!! Form::label('Contraseña') !!}
   {!! Form::password('password',  array('class'=>'form-control', 'placeholder'=>'Contraseña')) !!}
  </div>
  <div class="form-group">
   {!! Form::label('Confirmar contraseña') !!}
   {!! Form::password('password_confirmation',  array( 'class'=>'form-control', 'placeholder'=>'Confirmar contraseña') ) !!}
  </div>

  <div class="form-group">
   {!! Form::submit('Crear cuenta', array('class'=>'btn btn-primary btn-lg')) !!}
  </div>
  {!! Form::close() !!}
 </div>
@endsection

