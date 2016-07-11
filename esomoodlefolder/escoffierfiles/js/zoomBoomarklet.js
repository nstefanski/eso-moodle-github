javascript:
//set grade options
//document.getElementById('id_modstandardgrade').getElementsByTagName('a')[0].setAttribute("aria-expanded", true);
var i,gradetype = document.getElementById('id_grade_modgrade_type').children;
for(i=0;i<gradetype.length;i++){
	if(gradetype[i].value == "point"){
		gradetype[i].selected = true;
		break;
	}
}
var gradepoint = document.getElementById('id_grade_modgrade_point');
gradepoint.removeAttribute("disabled");
gradepoint.value = 1;
var gradecat = document.getElementById('id_gradecat');
gradecat.removeAttribute("disabled");
gradecat = gradecat.children;
for(i=0;i<=gradecat.length;i++){
	if(i==gradecat.length){
		alert("Could not find Category: Ungraded -- add category in gradebook before proceeding");
		break;
	} else if(gradecat[i].innerHTML == "Ungraded"){
		gradecat[i].selected = true;
		break;
	}
}
//set completion
var completion = document.getElementById('id_completion').children;
for(i=0;i<completion.length;i++){
	if(completion[i].value == 2){
		completion[i].selected = true;
		break;
	}
}
var usegrade = document.getElementById('id_completionusegrade');
usegrade.removeAttribute("disabled");
usegrade.checked = true;
//set join before host
document.getElementById('id_option_jbh').checked = true;
//get course code
var cc = document.getElementById('region-main').getElementsByTagName('h1')[0].innerHTML.split(" ")[0];
//get time values
var j,opts,selects = document.getElementById('fitem_id_start_time').getElementsByTagName('select');
var dp = new Array(5); //day,mo,yr,hr,min
for(i=0;i<selects.length;i++){
	opts = selects[i].children;
	for(j=0;j<opts.length;j++){
		if(opts[j].selected){
			dp[i] = opts[j].value;
			break;
		}
	}
}
//date using moment.js
var sessDate = moment({ y:dp[2], M:dp[1]-1, d:dp[0], h:dp[3], m:dp[4]}); //yr,mo,day,hr,min
var m=cc+" Live Session: "+sessDate.format("dddd, MMMM Do [@] h:mm A");
var n=document.getElementById("id_name");
//remove event listeners, they mess with this bookmarklet
n.removeAttribute("onchange");
n.removeAttribute("onblur");
n.setAttribute("value", m);
//add event listeners back
n.setAttribute("onblur","validate_mod_zoom_mod_form_name(this,'name')");
n.setAttribute("onchange","validate_mod_zoom_mod_form_name(this,'name')");