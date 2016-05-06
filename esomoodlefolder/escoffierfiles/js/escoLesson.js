function copyLessonButtons() {
	var copyDiv = document.getElementsByClassName("branchbuttoncontainer")[0];
	if (copyDiv == null) {
		window.setTimeout(copyLessonButtons, 100);
	} else {
		var targetDiv = document.getElementById("lesson-branch-top-nav");
		if (targetDiv && copyDiv) {
			var cln = copyDiv.cloneNode(true);
			targetDiv.appendChild(cln);
		}
	}
}

function setPdfFrameHeight() {
  var iframe = document.getElementById("lesson-pdf-frame");
  var innerDoc = iframe.contentDocument;
  var pageHeight = innerDoc.getElementById("viewer").offsetHeight;
  if(pageHeight == 0) {
    window.setTimeout(setPdfFrameHeight, 100);
  } else {
    document.getElementById("lesson-pdf-div").style.height = pageHeight + "px";
  }
}