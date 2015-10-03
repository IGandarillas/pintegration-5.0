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
		 	{!! Form::label('PipeDrive') !!}
		 	{!! Form::text('pipedrive_api', null ,array('class'=>'form-control text-', 'placeholder'=>'PipeDrive API key')) !!}
		 </div>
		<div class="form-group">
		 	{!! Form::label('Prestashop') !!}
		 	{!! Form::text('prestashop_api', null ,array('class'=>'form-control text-', 'placeholder'=>'Prestashop API key')) !!}
		 </div>
         <div class="form-group">
			 {!! Form::label('Prestashop URL') !!}
			 {!! Form::text('prestashop_url', null ,array('class'=>'form-control text-', 'placeholder'=>'Prestashop URL')) !!}
		 </div>
		 <div class="form-group">
			 {!! Form::label('Address field code') !!}
			 {!! Form::text('address_field', null ,array('class'=>'form-control text-', 'placeholder'=>'Ex: 57cda8344ed4defb3ad99df35e755b8cfc64c248')) !!}
		 </div>
		 <div class="form-group text-center">
			 {!! Form::submit('Guardar', array('class'=>' btn btn-primary btn-lg')) !!}
		 </div>

