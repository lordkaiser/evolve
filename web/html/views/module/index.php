<style type="text/css">
	.container {
		width: 100%;
		padding: 0;
	}

	#module {
		background-color: black;
    border-radius: 0 20px 20px 0;
    color: white;
    left: 0;
    padding: 5px;
    position: absolute;
    top: 235px;
    width: 35px;
	}

	#module:hover {
		cursor: pointer;
	}

	#module_container {
    background-color: black;
    color: white;
    height: 525px;
    left: -285px;
    overflow-y: scroll;
    padding: 5px;
    position: absolute;
    top: 50px;
    width: 285px;
	}

	.module_list_container {
		list-style-type: none;
	}

	.module_list li:hover {
		cursor: pointer;
		background-color: #555555;
	}

	#module_unordered {
		padding: 0;
	}
</style>
<div style="width: 20%; float: left; max-height: 525px; overflow-y: scroll;">
	<ul id="module_unordered">
		<? foreach ($modules as $module) { ?>
		<li><?=$module->name;?></li>
		<li class="module_list_container">
			<ol class="module_list">
				<? $post['mid'] = $module->M_ID; ?>
				<? $samples = $master->getData('sample/getAllUnderModule', array(), $post); ?>
				<? foreach ($samples as $sample) { ?>
				<li class="module_single" mid="<?=$module->M_ID;?>" sid="<?=$sample->S_ID;?>"><?=$sample->name;?></li>
				<? } ?>
			</ol>
		</li>
		<? } ?>
	</ul>
</div>

<div style="width: 40%; background-color: rgb(188, 188, 188); float:left; min-height: 525px;">
	<div>
		<button id="prev">Previous</button>
		<button id="next">Next</button>
		<button id="play">Play</button>
		<button id="stop">Stop</button>
	</div>
	<div id="test"></div>
</div>
<div style="width: 40%; float: left;">
	<div>Variables</div>
	<div id="vars" style="background-color: white; border: 2px inset; width: 96%; margin: 2%; min-height: 212px;"></div>
	<div>Output</div>
	<div id="outs" style="background-color: white; border: 2px inset; width: 96%; margin: 2%; min-height: 212px;"></div>
</div>
<script type="text/javascript">
	var module_open = false;
	var module_speed = 1000;
	var codeLine = 0;
	var codeMax = 0;
	var fullcode;
	var play = false;
	var startLoop = [];
	var endLoop = [];
	var inLoop = false;
	var runTimes = [];

	$('#module').click( function() {
		if (!module_open) {
		  $( "#module" ).animate({
		    left: "+=285"
		  }, module_speed, function() {
		    // Animation complete.
		  });
		  $( "#module_container" ).animate({
		    left: "+=285"
		  }, module_speed, function() {
		    // Animation complete.
		  });
		  module_open = true;
		} else {
		  $( "#module" ).animate({
		    left: "-=285"
		  }, module_speed, function() {
		    // Animation complete.
		  });
		  $( "#module_container" ).animate({
		    left: "-=285"
		  }, module_speed, function() {
		    // Animation complete.
		  });
		  module_open = false;
		}
	});

	$('.module_single').click(function () {
		$('#test').html('');
		var mid = $(this).attr('mid');
		var sid = $(this).attr('sid');
		$.post('http://adapt.com/module/get', {
			mid: mid,
			sid: sid
		}, function(data) {
			var lines = $.parseJSON(data);
			fullcode = [];
			var newTable = $('<table/>', {
		    id: 'code_table',
		    style: 'background-color: white; border: 2px inset; width: 96%; margin: 2%;'
			});
			$.each(lines, function( index, value ) {
				var newRow = $('<tr/>', {
			    id: 'code_row' + index
				}).appendTo(newTable);

				var cleanVal = value.replace(/\t/g, '');
				fullcode.push(cleanVal);
				var newRow = $('<td/>', {
			    id: 'code_cell' + index,
			    class: 'code_cell',
			    text: cleanVal
				}).appendTo(newRow);
				codeMax = index;
			});
			$('#test').append(newTable);
			$('#code_cell1').css('background-color', '#f0f080');
			codeLine = 1;
		});
	});

	$('#next').click( function() {
		if(codeLine < codeMax - 1) {
			codeLine++;

			var regEnd = /\s*}\s*/g;
			codepiece = $('#code_cell' + codeLine).text();
			if (codepiece.match(regEnd) != null && inLoop) {
				if (endLoop.length == 0) {
					endLoop.push(codeLine);
					if (runTimes.length == 0) runTimes.push(0);
				} else if (endLoop[endLoop.length - 1] != codeLine) {
					endLoop.push(codeLine);
					if (runTimes.length == 0) runTimes.push(0);
				}
				if (startLoop.length != 0) {
					codeLine = startLoop[startLoop.length - 1];
				}
			} else if (!inLoop) {
				if (endLoop.length > 0 && startLoop.length > 0 && runTimes.length > 0) {
					startLoop.pop();
					endLoop.pop();
					runTimes.pop();
				}
			}

			$('.code_cell').each( function( index, value ) {
			  $(value).css('background-color', '');
			});
			$('#code_cell' + codeLine).css('background-color', '#f0f080');

			$('#outs').html('');
			$.post('http://adapt.com/module/compiler', {
				code: fullcode,
				num: codeLine,
				type: 'output'
			}, function(data) {
				$('#outs').html(data);
			});

			$('#vars').html('');
			$.post('http://adapt.com/module/compiler', {
				code: fullcode,
				num: codeLine,
				runTimes: (runTimes.length == 0 ? 0 : runTimes[runTimes.length - 1]),
				type: 'var'
			}, function(data) {	
				var vars = $.parseJSON(data);
				var totalText = '';
				inLoop = vars['inLoop'];

				regEnd = /^\s*(do|while|for).*/g;
				var codepiece2 = $('#code_cell' + codeLine).text();
				if (codepiece2.match(regEnd) != null && inLoop) {
					if (runTimes.length != 0) runTimes[runTimes.length - 1]++;
					if (startLoop.length == 0) {
						startLoop.push(codeLine);
						runTimes.push(0);
					} else if (startLoop[startLoop.length - 1] != codeLine) {
						startLoop.push(codeLine);
						runTimes.push(0);
					}					
				}
				$.each(vars, function(index, value) {
					totalText += '$' + index + '=' + value + '</br>';
				});
				$('#vars').html(totalText);
			});
		} else {
			$('#next').prop('disabled', false);
			$('#prev').prop('disabled', false);
			play = false;
		}
	});

	$('#prev').click( function() {
		if(codeLine > 1) {
			
			var regStart = /^\s*(do|while|for).*/g;
			codepiece = $('#code_cell' + codeLine).text();
			if (codepiece.match(regStart) != null && inLoop) {
				if (startLoop.length == 0) {
					startLoop.push(codeLine);
					if (runTimes.length == 0) runTimes.push(0);
				} else if (startLoop[startLoop.length - 1] != codeLine) {
					startLoop.push(codeLine);
					if (runTimes.length == 0) runTimes.push(0);
				}
				if (endLoop.length != 0) {
					var saveCodeLine = codeLine;
					codeLine = endLoop[endLoop.length - 1];
					if (runTimes.length != 0) {
						if (runTimes[runTimes.length - 1] == 0) {
							codeLine = saveCodeLine;
							startLoop.pop();
							endLoop.pop();
							runTimes.pop();
						}
						if (runTimes[runTimes.length - 1] > 0) runTimes[runTimes.length - 1]--;
					}
				}
			}

			codeLine--;

			$('.code_cell').each( function( index, value ) {
			  $(value).css('background-color', '');
			});
			$('#code_cell' + codeLine).css('background-color', '#f0f080');

			$('#outs').html('');
			$.post('http://adapt.com/module/compiler', {
				code: fullcode,
				num: codeLine,
				type: 'output'
			}, function(data) {
				$('#outs').html(data);
			});

			$('#vars').html('');
			$.post('http://adapt.com/module/compiler', {
				code: fullcode,
				num: codeLine,
				runTimes: (runTimes.length == 0 ? 0 : runTimes[runTimes.length - 1]),
				type: 'var'
			}, function(data) {	
				var vars = $.parseJSON(data);
				var totalText = '';
				inLoop = vars['inLoop'];

				regStart = /\s*}\s*/g;
				var codepiece2 = $('#code_cell' + codeLine).text();
				if (codepiece2.match(regStart) != null && inLoop) {
					if (endLoop.length == 0) {
						endLoop.push(codeLine);
						if (runTimes.length == 0) runTimes.push(0);
					} else if (endLoop[endLoop.length - 1] != codeLine) {
						endLoop.push(codeLine);
						runTimes.push(0);
					}
				}
				$.each(vars, function(index, value) {
					totalText += '$' + index + '=' + value + '</br>';
				});
				$('#vars').html(totalText);
			});
		}
	});

	$('#play').click( function() {
		play = true;
		$('#next').prop('disabled', true);
		$('#prev').prop('disabled', true);
		function myLoop () {
			setTimeout(function () {
			  $('#next').trigger('click');
			  if (play && codeLine < codeMax) {
			    myLoop();
			  }
			}, 1500)
		}

		myLoop();
	});

	$('#stop').click( function() {
		play = false;
		$('#next').prop('disabled', false);
		$('#prev').prop('disabled', false);
	});

	function parseItUp() {
		var codepiece = $('#code_cell' + codeLine).text();
		var regStart = /\s*for\s*\(.*\)\s*{/g;

		if(codepiece.match(regStart) != null) {
			inLoop = true;
			var tempConditions = codepiece.substring(codepiece.indexOf('(') + 1, codepiece.indexOf(')'));
			var tempSplit = tempConditions.split(';');
				
			var tempInitiator = tempSplit[0].trim().split(' ');
			var statusChecker = tempInitiator[0].replace(/(\$.*)/g, "loopStatus['$1']");
			statusChecker = '(typeof ' + statusChecker + ' == "undefined")';
			if (eval(statusChecker)) {
				startLoop.push(codeLine - 1);
				var evalTemp = '';
				for (var i = 0; i < tempInitiator.length; i++) {
					var evalTemp1 = tempInitiator[i].replace(/(\$.*)/g, "loopStatus['$1']");
					evalTemp += evalTemp1;
				};
				evalTemp += ';';
				eval(evalTemp);
			} else {
				var tempConditional = tempSplit[1].trim().split(' ');
				var evalTemp = '';
				for (var i = 0; i < tempConditional.length; i++) {
					var evalTemp1 = tempConditional[i].replace(/(\$[^+]*)/g, "loopStatus['$1']");
					evalTemp += evalTemp1;
				};
				// eval(evalTemp);
				console.log(evalTemp);

				if (eval(evalTemp)) {
					var tempIterator = tempSplit[2].trim().split(' ');
					var evalTemp = '';
					for (var i = 0; i < tempIterator.length; i++) {
						var evalTemp1 = tempIterator[i].replace(/(\$[^+]*)/g, "loopStatus['$1']");
						evalTemp += evalTemp1;
					};
					evalTemp += ';';
					eval(evalTemp);
					console.log(evalTemp);
				} else {
					inLoop = false;
					codeLine = endLoop.pop();
					startLoop.pop();
				}
			}
		}

		postItUp();

		if (inLoop) {
			tempLine = codeLine + 1;

			if(endLoop.length == 0) {
				endLoop.push(tempLine);
			} else {
				if (endLoop[endLoop.length - 1] != tempLine) endLoop.push(tempLine);
			}

			var regEnd = /\s*}\s*/g;
			codepiece = $('#code_cell' + tempLine).text();
			if (codepiece.match(regEnd) != null) {
				var tempStart = startLoop[startLoop.length - 1];
				codeLine = tempStart;
			}
		}
	}

	function postItUp() {
		$('#vars').html('');
		$.post('http://adapt.com/module/compiler', {
			code: fullcode,
			num: codeLine,
			type: 'var'
		}, function(data) {	
			var vars = $.parseJSON(data);
			var totalText = '';
			$.each(vars, function(index, value) {
				totalText += '$' + index + '=' + value + '</br>';
			});
			$('#vars').html(totalText);
		});

		$('#outs').html('');
		$.post('http://adapt.com/module/compiler', {
			code: fullcode,
			num: codeLine,
			type: 'output'
		}, function(data) {
			$('#outs').html(data);
		});
	}
</script>