@extends('layouts.master')

@section('content')

	<div class="row vertical-center ">
		<div class="col-md-6 ">
			@if(isset($user))
				{!! Form::model($user, ['route' => ['home.update', $user->id], 'method' => 'put'] ) !!}

			@else
				{!! Form::open(array('url' => '/entrar', 'class' => 'form')) !!}

			@endif
			@include('partials.apis');
			{!! Form::close() !!}
		</div>
	</div>

@endsection
