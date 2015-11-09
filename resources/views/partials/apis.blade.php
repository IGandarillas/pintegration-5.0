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
		 	{!! Form::label('PipeDrive API key') !!}
		 	{!! Form::text('pipedrive_api', null ,array('class'=>'form-control text-', 'placeholder'=>'Ex: e9748c75a8b8a2179354dd2226665332c04c71ea')) !!}
		 </div>
		<div class="form-group">
		 	{!! Form::label('Prestashop API key') !!}
		 	{!! Form::text('prestashop_api', null ,array('class'=>'form-control text-', 'placeholder'=>'Ex: 5AWMFQ9B8AJG4NV88GS16NWUB1CXC7WS')) !!}
		 </div>
         <div class="form-group">
			 {!! Form::label('Prestashop URL') !!}
			 {!! Form::text('prestashop_url', null ,array('class'=>'form-control text-', 'placeholder'=>'Ex: http://osteox.esy.es/prestashop/')) !!}
		 </div>
		 <div class="form-group">
			 {!! Form::label('Address field code') !!}
			 {!! Form::text('address_field', null ,array('class'=>'form-control text-', 'placeholder'=>'Ex: 57cda8344ed4defb3ad99df35e755b8cfc64c248')) !!}
		 </div>
		 <div class="form-group">
			 {!! Form::label('Email Log') !!}
			 {!! Form::text('email_log', null ,array('class'=>'form-control text-')) !!}
		 </div>
		 <div class="form-group text-center">
			 {!! Form::submit('Guardar', array('class'=>' btn btn-primary btn-md')) !!}
		 </div>

