 <html>
    <head>
        <title>Log in</title>
        <meta charset="utf-8">
        <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
        <link href="css/style.css" rel="stylesheet" type="text/css" />
        <!-- END THEME LAYOUT STYLES -->
        <link rel="shortcut icon" href="favicon.ico" />
    </head>
    <body>
        {!! Form::open(['url' => 'register']) !!}
        <div class="login">
            <div class="form-group">                
                <input class="form-control" type="text" name="firstname" class="firstname" placeholder="Enter Your First Name">
            </div>
            <div class="form-group">                
                <input class="form-control" type="text" name="lastname" class="lastname" placeholder="Enter Your Last Name">
            </div>
            <div class="form-group">                
                <input class="form-control" type="email" name="email" class="email" placeholder="Enter Your E-Mail">
            </div>
            <div class="form-group">                
                <input class="form-control" type="password" name="password" class="password" placeholder="Enter Your Password">
            </div>
            <input type="submit" name="submit" value="Sign In" class="btn btn-primary">
            <a class="btn btn-default" href="register">Register new account</a>
        </div>
        {!! Form::close() !!}

        <script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  </body>
  </html>
 