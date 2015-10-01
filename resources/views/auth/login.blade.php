@extends('layouts.master')

@section('content')

	<div class="row vertical-center ">
		 <div class="col-md-6 ">
		 {!! Form::open(array('url' => '/auth/login', 'class' => 'form')) !!}
		 <h1>Integración PipeDrive-Prestashop</h1>
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
		 	{!! Form::label('') !!}
		 	{!! Form::text('email', null,array('class'=>'form-control text-', 'placeholder'=>'Correo electrónico')) !!}
		 </div>


		 <div class="form-group">
			 {!! Form::label('') !!}
			 {!! Form::password('password', array('class'=>'form-control', 'placeholder'=>'Contraseña')) !!}
		 </div>

		 <div class="form-group">
		 <label>
			 {!! Form::checkbox('remember', 'remember') !!} Mantener sesión activa
		 </label>
		 </div>

		 <div class="form-group text-center">
			 {!! Form::submit('Entra', array('class'=>' btn btn-primary btn-lg')) !!}
		 </div>

		 
		 </div>
		 </div>
			 {!! Form::close() !!}

		 </div>
	</div>

 @endsection
