<html>
<head>
<style type="text/css">
td {
	width: 50px;
	text-align: center;
	height: 50px;
	border: 1px solid #ccc;
	cursor: pointer;
}
</style>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
var symbol = '<?php echo $_GET['symbol'] ?>';

$(document).ready(function() {
	$('td').click(function() {
		var id = $(this).attr('id');
		var str = id.replace('field_', '');
		var zeugs = str.split('_');

		var x = zeugs[0];
		var y = zeugs[1];

		var td = $(this);

		$.get('index.php?action=tick&x=' + x + '&y=' + y, function(result) {
			if (result == 1) {
				td.html(symbol);
			}
		});
	});
});

function wait() {
	$.get('index.php?action=wait', function(result) {
		var zeugs = result.split(' ');

		var x = zeugs[0];
		var y = zeugs[1];

		if (x >= 0 && y >= 0) {
			$('#field_' + x + '_' + y).html(symbol == 'X' ? 'O' : 'X');
		}
		
		setTimeout('wait()', 1000);
	});
}

setTimeout('wait()', 1000);
</script>
</head>
<body>
	<table>
		<tr>
			<td id="field_0_0">&nbsp;</td>
			<td id="field_0_1">&nbsp;</td>
			<td id="field_0_2">&nbsp;</td>
		</tr>
		<tr>
			<td id="field_1_0">&nbsp;</td>
			<td id="field_1_1">&nbsp;</td>
			<td id="field_1_2">&nbsp;</td>
		</tr>
		<tr>
			<td id="field_2_0">&nbsp;</td>
			<td id="field_2_1">&nbsp;</td>
			<td id="field_2_2">&nbsp;</td>
		</tr>
	</table>
</body>
</html>