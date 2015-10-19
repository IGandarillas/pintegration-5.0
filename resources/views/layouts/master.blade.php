<!doctype html>
 <html lang="en">
 <head>
 <meta charset="UTF-8">
 <title>dev.pintegration</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
  {!! Html::style(asset('/css/app.css')) !!}
  {!! Html::style(asset('/css/styles.css')) !!}
  <script src="{{ asset('/js/vendor.js') }}"></script>
  {!! Html::style(asset('/css/bootstrap-datetimepicker.min.css')) !!}
 </head>
 <body>
 <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container-fluid">
   <!-- Brand and toggle get grouped for better mobile display -->
   <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
     <span class="sr-only">Toggle navigation</span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </button>
   </div>

   <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    <ul class="nav navbar-nav navbar-left">
     <li><a class="navbar-brand" href="/">dev.pintegration</a></li>
    </ul>

    <ul class="nav navbar-nav navbar-left">
     @if(Auth::check())
      <li class="nav-item" ><a class="nav-title" >Productos </a><a class="nav-subtitle"><b><span class="badge">{{\pintegration\Item::count()}}</span></b></a></li>

      <li class="nav-item" ><a class="nav-title">Clientes / Direcciones </a><a class="nav-subtitle"><b><span class="badge">{{\pintegration\Client::count()}} / {{\pintegration\Direccion::count()}}</span></b></a></li>
      <li><a href="/sync">Sincronizar</a></li>
      <li><a href="/logs">Logs</a></li>
     @endif
    </ul>

    <ul class="nav navbar-nav navbar-right">
     @if(Auth::check())
      <li><a href="">{{ Auth::user()->name }}</a></li>
      <li><a href="/auth/logout">Salir</a></li>
     @else
      <li><a href="/auth/login">Entra</a></li>
      <li><a href="/auth/register">Crear cuenta</a></li>
      @endif

    </ul>
   </div>

  </div>
 </nav>
 <div class="container">
	 @yield('content')
</div>
<!-- Scripts -->
{!! Html::script('assets/js/bootstrap.min.js') !!}
 </body>
</html>
