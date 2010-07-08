function showInstalls(event) {
    var pos = $("#mittregnskap").offset();  
    var height = $("#mittregnskap").outerHeight();
    $("#installs").css( { position: 'absolute', "left": (pos.left) + "px", "top":(pos.top+height) + "px" } );
    $("#installs").show();
}

function hideInstalls(event) {
    $("#installs").delay(100).hide();
    $("#installs").removeClass("hovering");
}

function hideInstallsNow(event) {
    $("#installs").hide();
    $("#mittregnskap").removeClass("hovering");
}


function showInstallsNow(event) {
    $("#installs").stop(1);
    $("#installs").show();
    $("#mittregnskap").addClass("hovering");
}



$("#installs").mouseover(showInstallsNow);
$('#installs').mouseleave(hideInstallsNow);

$('#mittregnskap').mouseover(showInstalls);
$('#mittregnskap').mouseleave(hideInstalls);
