function cvConvertInputs() {
	if (document.getElementById("block-region-side-pre")==null) {
		window.setTimeout(cvConvertInputs, 100);
	} else { 
		var past = false;
		var dateStr = document.getElementById('region-main').getElementsByTagName('td')[0].innerHTML;
		dateStr = dateStr.substring(0, dateStr.search('<'));
		if (isNaN(Date.parse(dateStr)) == false) {
			var d = new Date(dateStr);
			var today = new Date();
			today.setHours(0,0,0,0);
			past = d < today;
		}
		var i,inputs = document.getElementById('region-main').getElementsByTagName('input');
		for (i = 0; i < inputs.length; i++) {
			if (inputs[i].type == 'text') {
				inputs[i].type = 'number';
				inputs[i].min = '0';
				inputs[i].removeAttribute('maxlength');
			}
			/*if (past) {
				inputs[i].setAttribute("disabled",1);
			}*/
		}
		var table = document.getElementById('region-main').getElementsByTagName('form')[2].getElementsByTagName('table')[0];
		var copy = table.getElementsByTagName('thead')[0].getElementsByTagName('tr')[0];
		var target = table.getElementsByTagName('tbody')[0];
		var cln = copy.cloneNode(true);
		target.appendChild(cln);
	}
}