@extends('layouts.master')

@section('content')

	<div class="row vertical-center ">
		 <div class="col-md-6 ">

		{!! Form::open(array('url' => '/auth/login', 'class' => 'form')) !!}
		@include('partials.apis');
		{!! Form::close() !!}

		 </div>
	</div>

 @endsection
