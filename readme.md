This library is targeted Codeigniter framwork.

USER GUIDE:

Copy the required files to your project.

Autoload config asset and library asset

Once done that. Run controller optimize. There you go, your asset is now compressed and optimized and stored in folder "service". the original assets remain intact.

Now to use the new asset in the views, you put this code in your view

<?=$this->asset->css_link('css/','service/css/')?>// if fail to load compressed css, it will load all the orginal css files in css folder
<?=$this->asset->js_link('js/', 'service/js/')?>// if fail to load compressed js, it will load all the orignal js files in js folder



