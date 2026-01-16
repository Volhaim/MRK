<?php 
include 'db_connect.php';
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM task_list where id = ".$_GET['id'])->fetch_array();
	foreach($qry as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<dl>
		<dt><b class="border-bottom border-primary">Задача</b></dt>
		<dd><?php echo ucwords($task) ?></dd>
	</dl>
	<dl>
		<dt><b class="border-bottom border-primary">Статус</b></dt>
		<dd>
			<?php 
        	if($status == 1){
		  		echo "<span class='badge badge-secondary'>На рассмотрении</span>";
        	}elseif($status == 2){
		  		echo "<span class='badge badge-primary'>В работе</span>";
        	}elseif($status == 3){
		  		echo "<span class='badge badge-success'>Завершен</span>";
        	}elseif($status == 4){
				echo "<span class='badge badge-danger'>Просрочен</span>";
            }
        	?>
		</dd>
	</dl>
	<dl>
		<dt><b class="border-bottom border-primary">Описание</b></dt>
		<dd><?php echo html_entity_decode($description) ?></dd>
	</dl>
</div>
