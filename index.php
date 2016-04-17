<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	//$targ_w = $targ_h = 150;
	$targ_w=$_POST['width']==''||$_POST['width']==0?$_POST['w']:$_POST['width'];
	$targ_h=$_POST['height']==''||$_POST['height']==0?$_POST['h']:$_POST['height'];
	$jpeg_quality = 90;
	
	$nx = (int)$_POST['x'];
	$ny = (int)$_POST['y'];
	$nw = (int)$_POST['w'];
	$nh = (int)$_POST['h'];
	// Check if upload file exist
	if( 0 < $_FILES['file']['error']){
		$src = 'yellow_mountain.jpg';
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
									$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		
		header('Content-type: image/jpeg');
		imagejpeg($dst_r,null,$jpeg_quality);
	}else{

		$filename = $_FILES['file']['name'];
		$src = $_FILES['file']['tmp_name'];
		$size = getimagesize($src);
		if($size[0]>500 || $size[1]>500){
			//ratio image width and height
			$ratio = $size[0]/$size[1]; // width/height
			if($ratio > 1) {
				$width = 500;
				$height = 500/$ratio;
				$skala = $size[0]/500;
			}else{
				$width = 500*$ratio;
				$height = 500;
				$skala = $size[1]/500;
			}
			$nx = (int)$_POST['x']*$skala;
			$ny = (int)$_POST['y']*$skala;
			$nw = (int)$_POST['w']*$skala;
			$nh = (int)$_POST['h']*$skala;
			if(($_POST['width']==''||$_POST['width']==0) || ($_POST['height']==''||$_POST['height']==0)){
				$targ_w= $targ_w*$skala;
				$targ_h= $targ_h*$skala;
			}
		}
		
		//check image type
		if ($_FILES["file"]["type"] == 'image/jpeg'){
			$src = imagecreatefromjpeg($src);
		}elseif ($_FILES["file"]["type"] == 'image/gif'){
			$src = imagecreatefromgif($src);
		}elseif ($_FILES["file"]["type"] == 'image/png'){
			$src = imagecreatefrompng($src);
		}
		$dst_r = ImageCreateTrueColor($targ_w, $targ_h);
		
		imagecopyresampled($dst_r,$src,0,0,$nx,$ny,$targ_w,$targ_h,$nw,$nh);
		//imagecopyresampled($dst_r,$src,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//imagecopyresampled($dst_r,$src,0,0,0,0,$targ_w,$targ_h,$size[0],$size[1]);

		
		$filename=str_replace(" ","_",$filename);
		if ($_FILES["file"]["type"] == 'image/jpeg'){
			header("Content-type: image/jpeg");
		}elseif ($_FILES["file"]["type"] == 'image/gif'){
			header("Content-type: image/gif");
		}elseif ($_FILES["file"]["type"] == 'image/png'){
			header("Content-type: image/png");
		}
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$filename);
		if ($_FILES["file"]["type"] == 'image/jpeg'){
			imagejpeg($dst_r,null,$jpeg_quality);
		}elseif ($_FILES["file"]["type"] == 'image/gif'){
			imagegif($dst_r);
		}elseif ($_FILES["file"]["type"] == 'image/png'){
			imagepng($dst_r);
		}
	}
	exit;
}
// If not a POST request, display page below:
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Live Cropping Image</title>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<script src="js/jquery-1.8.3.min.js"></script>
<script src="js/jquery.Jcrop.js"></script>
<link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />
<style>
*{margin:0;padding:0;}
body{
	background:#eee;
	font-family:Arial;
	color:#333;
	font-size:14px;
}
a{text-decoration:none;color:#2b9ec0;}
hr{margin-top:5px;color:#fff;}
.content{
	position:relative;
	background:#fff;
	width:600px;
	margin:30px auto;
	text-align:center;
	padding:30px;
	border-radius:3px;
	border:1px solid #CCCCCC;
}
.prevImg{
	display: inline-block;
	position:relative;
	max-width:500px;
	margin:5px auto;
	text-align:center;
}
.insize{
	width:50px;
	text-align:right;
	padding:3px;
}
#size{
	width:50px;
	padding:3px;
}
</style>
</head>
<body>
<div id='getdiv' style='height: 1in; left: -100%; position: absolute; top: -100%; width: 1in;'></div>
<div class="content">
	<h1>Live Cropping Image</h1>
	<br><hr><br>
	<div id="info"></div>
	<!-- This is the form that our event handler fills -->
	<form action="" method="post" onsubmit="return checkCoords();" enctype="multipart/form-data">
		<input type="file" name="file" id="imgInp" />
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="hidden" id="width" name="width" />
		<input type="hidden" id="height" name="height" />
		<label>
			<input type="checkbox" id="square">Square
		</label>
		<select id="size" name="size">
			<option value="cm">cm</option>
			<option value="mm">mm</option>
		</select>
		<input type="text" class="insize" id="ww" name="ww" onkeyup="setSize()" placeholder="Width" />
		<input type="text" class="insize" id="hh" name="hh" onkeyup="setSize()" placeholder="Height" />
		<input type="submit" value="Crop" />
		<!-- This is the image we're attaching Jcrop to -->
		<div class="prevImg">
			<img src="yellow_mountain.jpg" id="imagecrop" style="max-width:500px;max-height:500px;" />
		</div>
	</form>
	<br>
	<span class="createwith">Created with <a href="http://deepliquid.com/content/Jcrop.html">Jcrop</a> | &copy; <a href="https://github.com/hangsbreaker/">Hangs Breaker</span>
	<br>
</div>
<script type='text/javascript'>
var dpi_x = document.getElementById('getdiv').offsetWidth;
var dpi_y = document.getElementById('getdiv').offsetHeight;
var info = document.getElementById('info');
var dpi=dpi_x;
var jcrop_api;
jQuery(function($){
   initJcrop();
});

function initJcrop(){
	// Invoke Jcrop in typical fashion
	$('#imagecrop').Jcrop({
		onRelease: true,
		onSelect: updateCoords
	},function(){
		jcrop_api = this;
		jcrop_api.animateTo([0,0,100,100]);
		$('#w').val(100);$('#h').val(100);
		setSize();
	});
};

function updateCoords(c){
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#w').val(c.w);
	$('#h').val(c.h);
};

function checkCoords(){
	if (parseInt($('#w').val())) return true;
	alert('Please choose file and select a crop region.');
	return false;
};
// Preview Image
document.getElementById("imgInp").onchange = function () {
	var allowfile = checkfile('imgInp');
	if(allowfile){
		var reader = new FileReader();

		reader.onload = function (e) {
			// get loaded data and render thumbnail.
			//$('#imagecrop').removeAttr('style');
			$('#imagecrop').attr("style","max-width:500px;max-height:500px;");
			
			jcrop_api.destroy();
			
			document.getElementById("imagecrop").src = e.target.result;
			jcrop_api.setImage(e.target.result);
			jcrop_api.setOptions({ bgOpacity: .6 });
			
			initJcrop();
		};

		// read the image file as a data URL.
		reader.readAsDataURL(this.files[0]);
	}
};
// Checkbox square
document.getElementById("square").onchange = function () {
	//set jcrop ratio
	if(this.checked){
		jcrop_api.setOptions({ aspectRatio: 1 });
	}else{
		jcrop_api.setOptions({ aspectRatio: 0 });
	}
};

function setSize(){
	var size = document.getElementById('size').value;
	var ww = document.getElementById('ww').value;
	var hh = document.getElementById('hh').value;
	if(size=='mm'){
		ww= ww/10;
		hh= hh/10;
	}
	ww = dpi_cm_to_pixels(dpi,ww);
	hh = dpi_cm_to_pixels(dpi,hh);
	document.getElementById('width').value=ww;
	document.getElementById('height').value=hh;
	//set jcrop ratio
	jcrop_api.setOptions({ aspectRatio: parseInt(ww)/parseInt(hh) });
}
// Converter dpi/cm/px
function cm_pixels_to_dpi(px,cm) {
	if (cm == 0) {
		alert("cm cannot be zero");
	} else {
		return 2.54*px/cm;
	}
}
function pixels_dpi_to_cm(dpix,px) {
	if (dpix == 0 || dpix=='') {
		alert("dpi cannot be zero");
	} else {
		return 2.54*px/dpix;
	}
}
function dpi_cm_to_pixels(dpix,cm) {
	return dpix*cm/2.54;
}

//check file type and file size
function checkfile(id){
	id = document.getElementById(id);
	var fileName = id.files[0].name;
	var fileSize = id.files[0].size;
	var ext = fileName.split('.')[fileName.split('.').length - 1].toLowerCase();
	var allowext = ['jpg','jpeg','png'];

	if(allowext.indexOf(ext) > -1){
		if(fileSize < 4000000){
			return true;
		}else{
			alert('File max size 4 Mb');
			id.value='';
			return false;
		}
	}else{
		alert('File must be (.jpg)/(.png)');
		id.value='';
		return false;
	}
}
</script>

</body>
</html>
<!-- Created By: Hangs Breaker -->