A codeigniter libary to compress HTML, CSS, Javascript and make sprite image

## Installing

Copy the asset.php, jsmin.php, cssmin.php file and paste it to your library folder

## Usage

Load it in your controller

$this->load->library('asset');

to comppress css:

```
$this->asset->compress_css('my_production_css_folder/','my_distribution_css_folder/');
```

to compress js

```
$this->asset->compress_css('my_production_js_folder/','my_distribution_js_folder/');
```

to compress HTML (which might contain CSS and JS)

```
$this->asset->compress_html('app/views/','app/my_distribution_views folder/');
```


