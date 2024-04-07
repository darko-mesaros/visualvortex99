<!DOCTYPE html>
<html>
  <head>
    <title>VisualVortex 99</title>
    <style>
      body {
	background-color: #D3D3D3;
      }
      .links-div {
	background-color: yellow;
	text-align: center;
      }
      .title-header {
	color: red;
      }
      .subtext {
	color: blue;
      }
      .counter-div {
	background-color: cyan;
      }
    </style>
  </head>
  <body>
    <h1 class="title-header">VisualVortex 99</h1>
    <p class="subtext">
      <i>Ride the Cutting-Edge Wave of Pixel-Perfect AI Imagery</i>
    </p>
    <hr>
    <form action="/generate.php" method=get>
      <p> Tell us what kind of image would you like:</p>
      <textarea name="image_prompt" rows=4 cols=40 size="1024" align=left></textarea>
      <br>
      <input name="submit" type="submit" value="Submit" align=left>
    </form>
    <marquee>When you hit "Submit", your request will be sent to a state of the art machine learning algorithm that will generate this image through <i>"Generative Artificial Intelligence"</i></marquee>

    <hr>
    <div class="links-div">
      <a href="info.php">Now with PHP!</a> <a href="about.html">About us</a>
    </div>
  <div class="counter-div">
    <?php include("counter.php");?>
  </div>
  </body>
</html>
