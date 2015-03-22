<div>
	<div>
		Sample Input
		<span style="float: right;">
			<select id="inputLang">
				<? foreach ($languages as $language) { ?>
				<option value="<? echo $language->L_ID;?>"><? echo $language->name;?></option>
				<? } ?>
			</select>
		</span>
	</div>
	<div>
		<textarea id="input" style="height: 200px; width: 100%;"></textarea>
	</div>
</div>


<div>
	<div>
		Sample Output
		<span style="float: right;">
			<select id="outputLang">
				<? foreach ($languages as $language) { ?>
				<option value="<? echo $language->L_ID;?>"><? echo $language->name;?></option>
				<? } ?>
			</select>
		</span>
	</div>
	<div>
		<textarea id="output" style="height: 200px; width: 100%;"></textarea>
	</div>
</div>

<div><button id="proceed">Decode</button></div>
<div id="test"></div>
<script type="text/javascript">
	$('#proceed').click( function() {
		$('#test').text('');
		$.post('http://adapt.com/code/translateTest', {
			lid: $('#inputLang').val(),
			code: $('#input').val(),
			target: $('#outputLang').val()
		}, function(data) {	
			$('#test').append(data);
		});

		$('#output').val('');
		$.post('http://adapt.com/code/translate', {
			lid: $('#inputLang').val(),
			code: $('#input').val(),
			target: $('#outputLang').val()
		}, function(data) {
			// console.log(data);
			// data = data.replace("\\n","\n");
			// console.log(data);
			$('#output').val(data);
			$('#output').val($('#output').val().replace(/\\n/g,"\n"));
		});
	});
</script>