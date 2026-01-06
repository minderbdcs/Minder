<html>
<head>
<style>
    /* http://meyerweb.com/eric/tools/css/reset/
       v2.0 | 20110126
       License: none (public domain)
    */

    html, body, div, span, applet, object, iframe,
    h1, h2, h3, h4, h5, h6, p, blockquote, pre,
    a, abbr, acronym, address, big, cite, code,
    del, dfn, em, img, ins, kbd, q, s, samp,
    small, strike, strong, sub, sup, tt, var,
    b, u, i, center,
    dl, dt, dd, ol, ul, li,
    fieldset, form, label, legend,
    table, caption, tbody, tfoot, thead, tr, th, td,
    article, aside, canvas, details, embed,
    figure, figcaption, footer, header, hgroup,
    menu, nav, output, ruby, section, summary,
    time, mark, audio, video {
        margin: 0;
        padding: 0;
        border: 0;
        font-size: 100%;
        font: inherit;
        vertical-align: baseline;
    }
    /* HTML5 display-role reset for older browsers */
    article, aside, details, figcaption, figure,
    footer, header, hgroup, menu, nav, section {
        display: block;
    }
    body {
        line-height: 1;
    }
    ol, ul {
        list-style: none;
    }
    blockquote, q {
        quotes: none;
    }
    blockquote:before, blockquote:after,
    q:before, q:after {
        content: '';
        content: none;
    }
    table {
        border-collapse: collapse;
        border-spacing: 0;
    }
</style>
<style>
    body {
        background-image: url("data:image/jpeg;base64,/9j/2wCEAAYEBAQFBAYFBQYJBgUGCQsIBgYICwwKCgsKCgwQDAwMDAwMEAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwBBwcHDQwNGBAQGBQODg4UFA4ODg4UEQwMDAwMEREMDAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDP/dAAQABv/uAA5BZG9iZQBkwAAAAAH/wAARCAAkACwDABEAAREBAhEB/8QBogAAAAcBAQEBAQAAAAAAAAAABAUDAgYBAAcICQoLAQACAgMBAQEBAQAAAAAAAAABAAIDBAUGBwgJCgsQAAIBAwMCBAIGBwMEAgYCcwECAxEEAAUhEjFBUQYTYSJxgRQykaEHFbFCI8FS0eEzFmLwJHKC8SVDNFOSorJjc8I1RCeTo7M2F1RkdMPS4ggmgwkKGBmElEVGpLRW01UoGvLj88TU5PRldYWVpbXF1eX1ZnaGlqa2xtbm9jdHV2d3h5ent8fX5/c4SFhoeIiYqLjI2Oj4KTlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+hEAAgIBAgMFBQQFBgQIAwNtAQACEQMEIRIxQQVRE2EiBnGBkTKhsfAUwdHhI0IVUmJy8TMkNEOCFpJTJaJjssIHc9I14kSDF1STCAkKGBkmNkUaJ2R0VTfyo7PDKCnT4/OElKS0xNTk9GV1hZWltcXV5fVGVmZ2hpamtsbW5vZHV2d3h5ent8fX5/c4SFhoeIiYqLjI2Oj4OUlZaXmJmam5ydnp+So6SlpqeoqaqrrK2ur6/9oADAMAAAERAhEAPwD1HFAsNZCS7kUJHh7D2FNt+mw7YKSsluCsg9I8+7JTYD3OxU+Na070xJVq3aSdmMjAoPsqu2/j/MOlRXvuOgxCtGeeN2jJWQgfD0B39h+r7RrttgtVaGZGRaOZGPUbAjxqB0A9/oJqKkFCm1mQf3UjRqf2RXr94xpNoP/Q9PXLcJT6bkPLs4G5FBtSm4J6Dxr1FK5EpCnFbNKCSaKG3U9WI68u4/GlfpZAW0XxiQiQ0jNOO9B4bH5U23woU2tY/SESrXqeZ6ivfbqfboabnpjSbQzc4ZTv+8UVLjcEf5Q6gnx8aePLIqjLVg0fPkWLGrEgDfp0HTYD9eSCCv/R9QmQQy0dK8q0lFCSOpqOtB37bbADYBK+F1kZpFUU6c/2jTxFNh8zXptiEKVxBI7+otJVI2QnYVHUb0379D4b7gEJVbaJoo+DMC1SQPb2/wA+pwhBWfWI4i0ZTiw3olCD+renWoGw+WNpaiUyoHQCJT0UV39/hKj26V264hX/0vUU0LOWLuQgp6VOx8adzXpueu2+AhKFYSRtueJO9WqFcf5XgfGvY/FQ7mP496UXDM8rE04qooQSCa/LqKUPWlfDwkChRlllC+k4Uuu7SEigr0I6EN4CldqgGuAlVkUJcguCsLb8j1Y17nqAT9HSlSQSgKjIkZAVLFhX4SdzSnf6a/RkkID/0/T9rLJ68kTMWVa0J67GnX6f6ZEJKu8KuwLksBuEPSv8fpr1OGkKLRrb1eOvxAgqTUbKT8+o8e5x5JXraQipYF2PVmNT/n79ffGltVRSq0LFvc0r+FP64UJXJdTu1eRX2UkDIWyp/9T/2Q==");
        *background-image: url("/minder/background.png");
        font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
        font-size: 1.2em;
    }
    #content {
        display: inline-block;
        position: relative;
        *margin: 0 auto;
        *width: 80%;
    }
    .table-cell {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
    }
    .table {
        display: table;
        width: 100%;
        height: 100%;
    }
    h1 {
        text-transform: uppercase;
        color: #0000FF;
        font-weight: bolder;
        display: inline-block;

    }
    h2 {
        color: #0000FF;
        display: block;
        background-color: #F8F8E2;
        border: 1px solid #000000;
        margin-bottom: 2%;
        margin-top: 2%;
        padding: 1%;
        clear: both;
        font-weight: bolder;
    }
    h1, h2 {
        text-align: left;
    }
    img {
        display: inline-block;
        float: left;
        margin-right: 5px;
    }
    #menu {
        text-align: justify;
        text-align-last: justify;
    }
    #menu:after {
        content: "";
        display: inline-block;
        width: 100%;

    }
    .menu-button-second, .menu-button-first, .menu-button-third, .menu-button-fourth {
        background-color: #0000FF;
        border: 1px solid #000000;
        text-align: center;
        margin-bottom: 5%;
        display: inline-block;
        width: 46%;
        position: relative;
        zoom: 1;
        *display: inline;
        _height: 200px;
    }
    .menu-button-second:after, .menu-button-first:after, .menu-button-third:after, .menu-button-fourth:after {
        content: "";
        display: block;
        margin-top: 105%;
    }
    .menu-button-first, .menu-button-third {
        *margin-right: 7%;
    }
    .menu-button-second, .menu-button-fourth {
        *margin-left: 7%;
    }
    .menu-button-first {
        background-color: #FFFF00;
        color: #0000FF;
    }
    .menu-button-second {
        background-color: #006837;
    }
    #menu a {
        display: block;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        text-decoration: none;
    }
    #menu a, #menu span {
        color: #FFFF00;
        font-size: 2rem;
        text-transform: uppercase;
        font-weight: bolder;
        text-align: center;
    }
    #menu .menu-button-first a, #menu .menu-button-first span {
        color: #0000FF;
    }
    #menu .hot-key {
        display: block;
        font-size: 12rem;
        *font-size: 9em;
        *width: 100%;
    }
    #menu .hot-key.large {
        font-size: 22rem;
        *font-size: 9em;
        line-height: 12rem;
        *line-height: 1em;
        transform: translate(0, 4rem);
        -webkit-transform: translate(0,4rem);
    }
    #barcode-container {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 50%;
        height: 30%;
        margin-left: -25%;
        margin-top: -12%;
        *margin-top: -5%;
        *padding-left: 15%;
    }
    #barcode-container ul {
        width: 100%;
        *display: block;
    }
    #barcode-container li {
        display: inline-block;
        background-color: red;
        position: relative;
        line-height: 7rem;
        margin-right: 5%;
        *float: left;
        *line-height: 8em;
        *margin-right: 10%;
    }
    #barcode-container li:last-child {
        margin-right: 0;
    }
    #last-bar {
        margin-right: 0;
    }
</style>
<?php
include "../whm/includes/db_access.php";
//var_dump($mdrInstanceName);
?>
</head>
<body>
<div class="table">
    <div class="table-cell">
        <div id="content">
            <img src="/minder/icons/MDR_30x29.jpg" alt="company logo" />
            <h1>Warehouse Minder <br />by Barcoding & Data Collection Systems P/L</h1>
            <h2>Instance: <?php echo($mdrInstanceName); ?></h2>
            <div id="menu">
                <div class="menu-button-first"><a href="/whm">
                    <span class="hot-key">M</span>
                    <span>Mobile</span>
                </a></div>
                <div class="menu-button-second"><a href="status.php">
                    <span class="hot-key large">*</span>
                    <span>Utils</span>
                </a></div>
                <div class="menu-button-third"><a href="/minder/">
                    <span class="hot-key">D</span>
                    <span>Desktop</span>
                </a></div>
                <div class="menu-button-fourth"><a href="/cgi-bin/repwebexe.bin/login">
                    <span class="hot-key">R</span>
                    <span>Reports</span>
                </a></div>
            </div>

            <div id="barcode-container">
                <div class="table">
                    <ul class="table-cell">
                        <li style="width: 5%">&nbsp;</li>
                        <li style="width: 15%">&nbsp;</li>
                        <li style="width: 10%">&nbsp;</li>
                        <li style="width: 5%">&nbsp;</li>
                        <li id="last-bar" style="width: 10%">&nbsp;</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
