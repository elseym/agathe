<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>agathe</title>
    <meta name="description" content="testing ground">
    <meta name="author" content="elseym">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/default.css">

    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <script src="http://127.0.0.1:8081/socket.io/socket.io.js"></script>
    <script src="js/jquery-1.8.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <h1><a href="/">agathe</a> <small>testing ground</small></h1>
        </header>
        <section id="content">
            <ul class="thumbnails">
                <li class="span3">
                    <div class="thumbnail">
                        <h3>Color</h3>
                        <div class="well" id="out-color"></div>
                        <div class="input-append input-prepend">
                            <span class="add-on"><i class="icon-eye-open"></i></span>
                            <input type="color" class="input-small" id="colorspec">
                            <button class="btn btn-success input-mini" id="colorset">Set</button>
                        </div>
                    </div>
                </li>
                <li class="span6">
                    <div class="thumbnail">
                        <h3>Message</h3>
                        <div class="well" id="out-messages"></div>
                        <div class="input-append input-prepend">
                            <span class="add-on"><i class="icon-envelope"></i></span>
                            <input type="text" class="input-xlarge" id="messagesspec" value="" placeholder="your message goes here...">
                            <button class="btn btn-success input-small" id="messagesset">Send</button>
                        </div>
                    </div>
                </li>
                <li class="span3">
                    <div class="thumbnail">
                        <h3>Text</h3>
                        <div class="well" id="out-text">&nbsp;</div>
                        <div class="input-append input-prepend">
                            <span class="add-on"><i class="icon-pencil"></i></span>
                            <input type="text" class="input-small" id="textspec" value="" placeholder="some text...">
                            <button class="btn btn-success input-mini" id="textset">Set</button>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
        <footer>elseym</footer>
    </div>
</body>
</html>
