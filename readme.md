This library is targeted Codeigniter framwork. But I can be easily ported to other framework such as CakePHP or Zend.

to use:

download, unzip.
open config folder, copy config file and paste to your application->config folder.
open controller folder, copy optimize.php and past to your application->controllers folder.
open libraries folder copy all the files, paste to your application->libraries folder.

everytime your project reach a milestone and you have published new code. You run controller optimize.php. Then enlable the config["enable_compress"] = TRUE

you will need to change your js links to $this->asset->js_link();

you will need to change your css links to $this->asset->css_link();

What does this do ?

it will read all the .css file in your css folder. Extract the text, then put them all in one file called main.css, then minify the file. then save the file to server folder in your root

it will read all the .js file in your css folder. Extract the text, then put them all in one file called main.js, then minify the file. then save the file to server folder in your root

it will read all the file in your views folder, Extract the text, then it will compress each file, then save each file to server folder in your root.

it will read all background url in your css files, and put the values into an array, then it will look for those images in your image folder, it creates a map of every images it found in css files. and put on a transparent canvas, called sprite.png.
Then it will rewrite your background url to use sprite.png and assign position of images for you automatically.

What you can

you can let the script knows where your folders are
you can tell the script how to name the compressed files
you can tell what images shouldn't be inscluded in the sprite.png
you can tell what css shouldn't be compressed and put into main.css
you can tell what js shouldn't be compressed and put in main.js
you can tell how long those file should be cached in browser


