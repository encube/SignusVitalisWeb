

//TODO: Transfer this to somewhere...
var _address = {

	server: "http://192.168.1.5/vsrs/Server/",
	server_address: function(URI){
		return this.server+URI;
	}

}

var _clientData = {
	// HTML5 chu chu
	wardID: sessionStorage.wardID,
	update_ward_id: function(id){ sessionStorage.wardID = id; wardID = id;}
}


$(function(){

	//TODO: Transfer this to somewhere
	$('#application-pane-container').hide(0);

	//End TODO:

	var startup = $('#welcome-pane-status'); setTimeout(function(){ 
		
		startup.html('Verifying Machine IP Address...');
		
		var ipVerifyReq = $.ajax({ url: _address.server_address('session.php?w') });
		ipVerifyReq.success(function(data){

			var ipData = JSON && JSON.parse(data) || $.parseJSON(data);
			
			if (ipData.wardID == ""){
				startup.html('Verifying machine IP address... Failed.');

				var msg = "<p>Please consult your administrator to check whether your unit has the correct IP address, or this unit might have a conflict in IP address assignment to other computers. Press OK to refresh the page.</p>";
				//alert_modal.update_alert_content("Sorry but we do not know you...", msg, "refresh");
				//alert_modal.show();
				alert(msg);
			} else {
				_clientData.update_ward_id(ipData.wardID);

				startup.html('Verifying machine IP address... IP Address verified.');
				setTimeout(function(){
					startup.html('Getting application data.');

					var homePageInfo = $.ajax({ url:_address.server_address('pages.php?p=home'), 
						data:{id:_clientData.wardID} });

					homePageInfo.done(function(data){
						$('#application-pane-container').html(data);
						startup.html('Loading application...');

						//TODO: Make this on the object
						$('html').fadeTo(500, 0, function(){
						    $(this).css('background', 'none');
						}).fadeTo(500, 1);
						$('#welcome-pane-container').hide('fade', 500, function(){
							$('#application-title').html("Signus Vitalis - Mission Control");
							$('#application-pane-container').show('fade', 500);
						});
					})
					

				}, 300); //Get app data
			} //else
		}); //ipVerifyReq
	}, 300);
	
});