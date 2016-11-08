<html>
<head>
  <meta charset="UTF-8">
  <title>YouTube Search</title>
  <link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon"/>
  <link rel="stylesheet" href="css/youtube.css">
  <script src="js/youtube.js"></script>
  <script src="js/jquery-2.2.0.min.js"></script>
</head>
<body>
<div class="wrapper">
  <div class="bar-wrapper">
    <div class="bar" id="bar">
      <div class="progress" id="progress"></div>
    </div>
    <span class="pg-text" id="pg-text">0%</span>
  </div>
  <div class="contorls">
    <div class="start" id="start"></div>
    <div class="download" id="download"></div>
  </div>
  <div class="options">
    <span>Region: <select id="lang-select">
      <option value="usa">USA</option>
      <option value="uk">UK</option>
      <option value="denmark">Denmark</option>
      <option value="japan">Japan</option>
    </select></span>
    <span style="margin-left: 20px;">Location: <input style="width: 190px;" type="text" id="location" placeholder="36.859579, -76.187269 e.g."></span>
    <span style="margin-left: 20px;">Max. results: <input type="text" name="max_res_0" style="width:50px;margin-top: 24px;" placeholder="100"/></span>
  </div>
  <form method="POST" action="youtube" class="form" id="main_form">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
   <div class="input-line">
    <div class="border" style="z-index: 1;"><div class="input-wrapper"><span class="input-title">Search term</span><input type="text" name="term_1" class="term1" placeholder="Search" /></div></div>
    <div class="border"><div class="input-wrapper second"><span class="input-title">Secondary term</span><input type="text" name="sterm_1" class="term2" placeholder="Search" /></div></div>
   </div>
    <!--<input type="submit" value="submit" style="display: none;">-->
  </form>
  <a href="" id="link" style="cursor:default; opacity:0;" target="_blank">dl</a>
</div>
</body>
</html>