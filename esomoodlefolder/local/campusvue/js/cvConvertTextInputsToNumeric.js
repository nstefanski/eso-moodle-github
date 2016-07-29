function cvConvertTextInputsToNumeric() {
	if (document.getElementById("block-region-side-pre")==null) {
		window.setTimeout(cvConvertTextInputsToNumeric, 100);
	} else { 
		var i,inputs = document.getElementById('region-main').getElementsByTagName('input');
		for (i = 0; i < inputs.length; i++) {
			if (inputs[i].type == 'text') {
				inputs[i].type = 'number';
				inputs[i].min = '0';
				inputs[i].removeAttribute('maxlength');
			}
		}
	}
}