<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>rts</title>
    <meta name="description" content="testing ground">
    <meta name="author" content="elseym">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/default.css">

    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <script src="http://rts.seym:8081/socket.io/socket.io.js"></script>
    <script src="js/jquery-1.8.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</head>

<body>
    <div class="container-fluid">
        <header>
            <h1>rts <small>testing ground</small></h1>
        </header>
        <section id="content">
            <ul class="thumbnails">
                <li class="span3">
                    <div class="thumbnail">
                        <h3>Color</h3>
                        <div class="well" id="out-color"></div>
                        <div class="input-append">
                            <input type="color" class="input-small" id="colorspec"><button class="btn btn-success" id="colorset">Set</button>
                        </div>
                    </div>
                </li>
                <li class="span3">
                    <div class="thumbnail">
                        <h3>Text</h3>
                        <div class="well" id="out-text">&nbsp;</div>
                        <div class="input-append">
                            <input type="text" class="input-small" id="textspec" value="Tester" placeholder="some text..."><button class="btn btn-success" id="textset">Set</button>
                        </div>
                    </div>
                </li>
                <li class="span3">
                    <div class="thumbnail">
                        <h3>Message</h3>
                        <div class="well" id="out-message"></div>
                        <div class="input-append">
                            <input type="text" class="input-small" id="messagespec" value="Tester" placeholder="some text..."><button class="btn btn-success" id="messageset">Set</button>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
        <footer>elseym</footer>
    </div>
</body>
</html>