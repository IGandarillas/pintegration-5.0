<!doctype html>
 <html lang="en">
 <head>
 <meta charset="UTF-8">
 <title>dev.pintegration</title>
{!! Html::style('assets/css/styles.css') !!}
{!! Html::style('assets/css/bootstrap.css') !!}
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
       <li><a href="/products">Productos <b>{{\pintegration\Item::count()}}</b></a></li>
      <li><a href="/initsynchronization">Sincronizar productos</a></li>
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
      </li>
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
