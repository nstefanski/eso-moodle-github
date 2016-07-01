function escoFillAccountInfo() {
	if (document.getElementById("page-login-signup")==null) {
		//do nothing
	} else if (document.getElementById("id_lastname")==null) {
		window.setTimeout(escoFillAccountInfo, 100);
	} else { 
		var params = {};

		if (location.search) {
			var parts = location.search.substring(1).split('&');

			for (var i = 0; i < parts.length; i++) {
				var nv = parts[i].split('=');
				if (!nv[0]) continue;
				params[nv[0]] = nv[1] || true;
			}
		}
	
		var email = params.email ? params.email : '';
		var firstname = params.firstname ? params.firstname : '';
		var lastname = params.lastname ? params.lastname : '';
		var city = params.city ? params.city : '';
		var country = params.country ? params.country : 'US';
		
		document.getElementById("id_username").value = email.toLowerCase();
		document.getElementById("id_email").value = email;
		document.getElementById("id_email2").value = email;
		document.getElementById("id_firstname").value = firstname;
		document.getElementById("id_lastname").value = lastname;
		document.getElementById("id_city").value = city;
		document.getElementById("id_country").value = country;
	}
}