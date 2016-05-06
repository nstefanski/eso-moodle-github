function copySubmitButtons() {
  var copyDiv = document.getElementsByClassName("submissionaction")[0];
  if (copyDiv == null) {
    window.setTimeout(copySubmitButtons, 100);
  } else {
    var targetDiv = document.getElementById("asst-top-nav");
    if (targetDiv && copyDiv) {
      var cln = copyDiv.cloneNode(true);
      targetDiv.appendChild(cln);
      var feedbackDiv = document.getElementsByClassName("feedback")[0];
      if (feedbackDiv) {
        feedbackDiv.id = "feedbackDiv";
        var feedbackLink = document.createElement("a");
        feedbackLink.href = "#feedbackDiv";
        var newButton = document.createElement("input");
        newButton.value = "View Feedback";
        newButton.type = "button";
        feedbackLink.appendChild(newButton);
        targetDiv.appendChild(feedbackLink);
        targetDiv.style.textAlign = "center";
      }
    }
  }
}

function setFrameHeight() {
  var iframe = document.getElementById("cooking-instructions-frame");
  var innerDoc = iframe.contentDocument;
  var pageHeight = innerDoc.getElementById("page").offsetHeight;
  if(pageHeight == 0) {
    window.setTimeout(setFrameHeight, 100);
  } else {
    iframe.style.height = pageHeight + "px";
  }
}

function asstBackLink() {
	var cmid = window.location.search.substr(window.location.search.indexOf("id=")+3);
	if (cmid.indexOf("&") >= 0) {
	  cmid = cmid.substr(0, cmid.indexOf("&") );
	}
	var backLink = document.getElementById("asst-back-btn");
	backLink.href = "http://my.escoffieronline.com/mod/assign/view.php?id=" + cmid;
	backLink.style.display = "inline";
}