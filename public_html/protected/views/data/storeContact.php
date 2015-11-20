<div class="page-header">
	<h1>Store</h1>
</div>

<div style="min-height: 500px">
<?php

if(is_null($Store))
{
?>
	<div class="alert alert-danger">Store ID <?php print $_GET['id']; ?> not found</div>
<?php
}
else
{
?>
	<pre>
<?php

print_r($Store->attributes);

?>
	</pre>
<?php
}
?>
</div>