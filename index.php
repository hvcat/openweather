<?php
echo "<pre>"; /* print_r($_POST); */ echo "</pre>";

error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

include_once 'config/Database.php';
include_once 'class/Items.php';
include_once 'class/Temp.php';
include_once 'class/Wind.php';

$database = new Database();
$db = $database->getConnection();
 
$items = new Items($db); // model city

$data = [];
$city = '';
if (isset($_POST['city'])) $city = htmlspecialchars(strip_tags($_POST['city']));


// actions
if (isset($_POST['submit'])) { // get data (by api)
	
	$result = file_get_contents("http://api.openweathermap.org/data/2.5/forecast?q=".$city."&units=metric&appid=e4b8b08c185638b825af37facfe1fabb"); 
	
	$result = json_decode($result);
	
	if (!is_object($result)) die('Api Error!');
	
	if ($result->cod != '404' && $result->cod != '400') // city not found, nothing to geocode
	foreach ($result->list as $i=>$forecast){
		$data[$i]['datetime'] = date('Y-m-d h:i:s a', $forecast->dt);
		$data[$i]['temp_min'] = $forecast->main->temp_min;
		$data[$i]['temp_max'] = $forecast->main->temp_max;
		$data[$i]['w_speed'] = $forecast->wind->speed;
	}

	// echo '<pre>';
	// print_r($result);
}
elseif (isset($_POST['read'])) { // read data from db
	
	
	$items->c_city_name = $city;
	
	if($items = $items->read()){         
        // success
		// echo 'Success';
		$temp = new Temp($db);
		$temp = $temp->read($items['c_id']);
		
		
		$data[0]['datetime'] = $temp ? date('Y-m-d h:i:s a', strtotime($temp['t_date_time'])) : '';
		$data[0]['temp_max'] = $temp ? $temp['t_temp_max'] : '';
		$data[0]['temp_min'] = $temp ? $temp['t_temp_min'] : '';
		
		$wind = new Wind($db);
		$wind = $wind->read($items['c_id']);
		
		$data[0]['w_speed'] = $wind ? $wind['w_speed'] : '';
		
		$data[0]['updated_at'] = date('Y-m-d h:i:s a', strtotime($items['updated_at']));
		
    } else{
		// fail
        // echo 'Not Found';
    }
}
elseif (isset($_POST['save'])){ // create(update) data
	
	
	$result = file_get_contents("http://api.openweathermap.org/data/2.5/forecast?q=".$city."&units=metric&appid=e4b8b08c185638b825af37facfe1fabb"); 

	$result = json_decode($result);
	
	if (!is_object($result)) die('Api Error!');
	
	// $result->list[0]->dt;
	
	$items->c_city_name = htmlspecialchars(strip_tags($result->city->name));
    $items->created_at = gmdate("Y-m-d H:i:s", time());
	$items->updated_at = gmdate("Y-m-d H:i:s", time()); 
	
	if ($c_id = $items->is_exist()){
		if ($items->update($c_id)){
			$temp = new Temp($db);
			$temp->t_temp_max = htmlspecialchars(strip_tags($result->list[0]->main->temp_max));
			$temp->t_temp_min = htmlspecialchars(strip_tags($result->list[0]->main->temp_min));
			$temp->t_city_id = $c_id;
			$temp->t_date_time = htmlspecialchars(strip_tags(date('Y-m-d H:i:s', $result->list[0]->dt)));
			
			$temp->update();
			
			$wind = new Wind($db);
			$wind->w_speed = htmlspecialchars(strip_tags($result->list[0]->wind->speed));
			$wind->w_city_id = $c_id;
			
			$wind->update();
		}
	}
	else{
		if($c_id = $items->create()){
			$temp = new Temp($db);
			$temp->t_temp_max = htmlspecialchars(strip_tags($result->list[0]->main->temp_max));
			$temp->t_temp_min = htmlspecialchars(strip_tags($result->list[0]->main->temp_min));
			$temp->t_city_id = $c_id;
			$temp->t_date_time = htmlspecialchars(strip_tags(date('Y-m-d H:i:s', $result->list[0]->dt)));
			
			$temp->create(); // todo if not save, del model city, show error, return false
			
			$wind = new Wind($db);
			$wind->w_speed = htmlspecialchars(strip_tags($result->list[0]->wind->speed));
			$wind->w_city_id = $c_id;
			
			$wind->create(); // success
		}
	}
		

    /* if($c_id = $items->create()){         
        // success
		$temp = new Temp($db);
		$temp->t_temp_max = htmlspecialchars(strip_tags($result->list[0]->main->temp_max));
		$temp->t_temp_min = htmlspecialchars(strip_tags($result->list[0]->main->temp_min));
		$temp->t_city_id = $c_id;
		$temp->t_date_time = htmlspecialchars(strip_tags(date('Y-m-d H:i:s', $result->list[0]->dt)));
		
		$temp->create();
    } else{         
        $items->update();
    } */
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Jacket or no Jacket</title>
	
	<style>
		input{
			width:100%
		}
		
		input[type="text"] {
		  outline: none;
		  box-shadow: none !important;
		  border: 1px solid #cccccc;
		  padding-left: 10px
		}
		
	</style>
</head>
<body class="d-flex flex-column h-100">

<!-- rendering data -->
<main role="main" class="flex-shrink-0">
    <div class="container">
        

    <center><h1><i><span class="<?= boolval(count($_POST)) ? 'text-danger' : 'text-primary' ?>">Jacket</span> or no <span class="<?= boolval(count($_POST)) ? 'text-danger' : 'text-primary' ?>">Jacket</span></i></h1></center>
</div>

	<!-- read from db vs api form -->
	<form method="post">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12"><input type="text" name="city" value="<?= $city ?>" placeholder="Enter city name here (E.g. New York)"></div>
				<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6"><input type="submit" name="submit" value="Get from API" class="btn btn-primary"></div>
				<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6"><input type="submit" name="read" value="Get from DB" class="btn btn-warning"></div>
			</div>
		</div>
	</form>
	
	<div class="container">
		<hr/>
	</div>
	
	<!-- save form (or updated at) vs data table --> <!-- todo add inputs to place from api  -->
	<?php if (isset($_POST['read']) || isset($_POST['submit'])){ ?>
		<?php if (boolval(count($data))){ ?>
			<form method="post">
				<div class="container">
				<div class="container border pt-2 pb-4">
					<h2><?= ucfirst($city) ?></h2>
					<?php if (isset($_POST['submit'])){ ?>
					
						<h5>Period</h5>
						<p>Starts at: <?= $data[0]['datetime'] ?><br>
						Ends at: <?= end($data)['datetime'] ?></p>
						<input type="hidden" name="city" value="<?= $city ?>">
						<div class="row">
							<div class="col-xl-2 col-lg-2 col-md-4 col-sm-4"><input type="submit" name="save" value="Save forecast" class="btn btn-success"></div>
						</div>
					
					<?php } elseif (isset($_POST['read'])) { ?>
						<h5>Updated at: <?= $data[0]['updated_at'] ?> UTC</h5>
					<?php } ?>
					
				</div>	
				</div>
			</form>
			
			
			<div class="container">
				<table class="table">
				<thead>
					<tr class="border-0">
						<th class="border-0"><a href="#">Datetime</a></th>
						<th class="border-0"><a href="#">Min temp</a></th>
						<th class="border-0"><a href="#">Max temp</a></th>
						<th class="border-0"><a href="#">Wind speed</a></th>
					</tr>	
				</thead>
				<tbody>
				<?php foreach ($data as $forecast){ ?>
					<tr class="border">
						<td class="border-0"><?= $forecast['datetime'] ?></td>
						<td class="border-0"><?= $forecast['temp_min'] ?>&#176;C</td>
						<td class="border-0"><?= $forecast['temp_max'] ?>&#176;C</td>
						<td class="border-0"><?= $forecast['w_speed'] ?> km/h</td>
					</tr>
				<?php } ?>
				</tbody>
				</table>

			</div>
		<?php } else { ?>
		
			<div class="container">
				<p>Not Found</p>
			</div>
		<?php } ?>
	
    <?php } elseif (isset($_POST['save'])){ ?>
		<div class="container">
			<p>Forecast saved to db</p>
		</div>
	<?php } ?>
</main>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-left">&copy; Application 2022</p>
    </div>
</footer>

<!-- 
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
<script>
    $(function()
    {
		$("input[name='save']").click(function() {
		  // alert( "Click called." );
		  $("#forecast").submit();
		});
    });
</script> -->
</body>
</html>
