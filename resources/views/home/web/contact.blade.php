<!doctype html>
<html>
<head>
     <meta charset="utf-8">
     <link href="css/styles.css" rel="stylesheet" type="text/css" media="screen">
     <link href="css/js-image-slider.css" rel="stylesheet">
     <script src="js/js-image-slider.js" type="text/javascript"></script>
<!--<script src="../js/tooltip.js" type="text/javascript"></script>-->
     <script type="text/javascript">
        imageSlider.thumbnailPreview(function (thumbIndex) { return "<img src='img/thumb" + (thumbIndex + 1) + ".jpg' style='width:100px;height:60px;' />"; });
    </script>
     <title>ህዝባዊ ወያነ ሓርነት ትግራይ </title>
</head>
      <body>
            <div id="wrapper">
                              <div id="logo">
                              <img src="img/banner1.jpg" width="990" height="180">
                              </div>
                              
              <div id="topnav">
                <ul>
                  <li><a class="{{ Request::is('main*') ? 'active' : '' }}" href="{{ url('main') }}"> መልዓሊ </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('about*') ? 'active' : '' }}" href="{{ url('about') }}"> ብዛዕባና </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('structure*') ? 'active' : '' }}" href="{{ url('structure') }}"> አወዳድባ ቤት ፅሕፈት </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('mehawur*') ? 'active' : '' }}" href="{{ url('mehawur') }}"> መሓዉር </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('download*') ? 'active' : '' }}" href="{{ url('download') }}"> አዉርድ </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('resources*') ? 'active' : '' }}" href="{{ url('resources') }}"> ርኢቶታት </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('vacancy*') ? 'active' : '' }}" href="{{ url('vacancy') }}"> ክፍቲ መደብ ስራሕ </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('contact*') ? 'active' : '' }}" href="{{ url('contact') }}"> ርኸቡና </a></li>
                                     <li> | </li>
                                     <li><a class="{{ Request::is('login*') ? 'active' : '' }}" href="{{ url('login') }}"> ናብ ሲስተም እቶ </a></li>
                </ul>
              </div>                                            
              <div id="content-full">
                <h1>መልዕኽቲ ይኽን ሓበሬታ ንኽተብፅሑና  .,...</h1> 
                                     <p>ናብ ቤት ፅሕፈት ህዝባዊ ወያነ ሓርነት ትግራይ ክበፅሕ እትደልይዎ መልእኽቲ ይኹን ሓበሬታ እንተልይኩም በዚ ዝስዕብ ቕጥጢ ብምጥቃም ክተብፅሑና ትኽእሉ ምኻንኩም እናሓበርና ንዝሃብኩምና ሃናፃይ ሓበሬታ ይኹን መልእኽቲ ብሽም ቤት ፅሕፈትና እናመስገና ብዝምልከቶ አካል ምላሽ ከምዝወሃበኩም ክንገልፀልኹም ንፈቱ ፡፡ </p>
                                     <h3> መልዕኽቲ ይኽን ሓበሬታ መልአኺ ቕጥዒ</h3>
                                     <form action="" method="post" name="form1">
                       
                                     </form>
              </div>
                                             
                               
              <div id="footer">
                              <p> &copy; 2018 . መብቱ ብሕጊ ዝተሓለወ እዩ ! ቤት ፅሕፈት ህዝባዊ ወያነ ሓርነት ትግራይ </p>
              </div>        
            
      </div>
      </body>
</html>
