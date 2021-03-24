<?php echo exec('whoami'); ?>
<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once "vendor/autoload.php";
	use thiagoalessio\TesseractOCR\TesseractOCR;

	if(isset($_POST['submit'])){
		print_r(imageReader($_FILES['upFile']));
	}

	function imageReader($file){
		$fTmpPath = $file['tmp_name'];
		$fName = $file['name'];
		$explodedName = explode(".", $fName);
		$fExtension = strtolower(end($explodedName));
		$newFName = date("dmY") ."_". $fName;
		$fDestination = "uploads/".$newFName;

		if($file){
			if($fExtension == "jpg" || $fExtension == "png" || $fExtension == "pdf"){
				$success = 0;
				if(move_uploaded_file($fTmpPath, $fDestination))
					$success = 1;

				if($success){
					if($fExtension == "jpg" || $fExtension == "png"){
						$pageText = (new TesseractOCR($fDestination))->lang('sqi')->run();
						return $pageText;
					}else if($fExtension == "pdf"){
						$document = new Imagick($fDestination);
						$pages = $document->getNumberImages();
						$pagesArr = array();
						for ($i = 0; $i < $pages+1; $i++){
							echo $i."<br>";
							$im = new Imagick();
							$im->setResolution(300,300);
							$im->readimage($fDestination.'[0]');
							$im->setImageFormat('jpeg');    
							$im->writeImage('thumb.jpg'); 
							$im->clear();
							$im->destroy();

							$pageText = (new TesseractOCR('thumb.jpg'))->lang('sqi')->run();
							$pagesArr[] = $pageText;
							// unlink("thumb.png");
						}
						return $pagesArr;
					}
				}else {
					return "There was an error please try again.";
				}
			}else{
				return "Wrong file extension. Supported extensions: PDF/PNG/JPG.";
			}
		}else {
			return "Please upload an image/pdf.";
		}	
	}
	
?>
<html>
	<head>
		<title>READ FROM SCANNED FILE</title>
	</head>
	<body>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="upFile" /> <br />
			<input type="submit" name="submit" value="Submit" />
		</form>
	</body>
</html>