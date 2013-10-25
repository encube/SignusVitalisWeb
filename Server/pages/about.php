<style>
	button {
		background-color:#45aeea; border:none; color:white; padding:10px 18px 10px 18px;
	} button:hover { background-color:white; color:#45aeea; border: 1px #45aeea solid; }

	.alternate { color:#45aeea; background-color:white; border: 1px #45aeea solid; }
	.alternate:hover { border:none; }

	#patient-name-table {
		overflow-y:auto; height:240px; margin-top:-20px; 
		width:95%;
	}

	h3{
 		font-family:"Roboto-Light"; font-weight: normal; color:#555;
 		margin-left:-2%; margin-bottom:-3%; padding:0; margin-top:4%
	}

</style>

<h3 class='titlea'> About the System </h3>
<div style='margin-left:-3%;margin-top:5%;width:97%'>
	<script>
	$(function(){
		$('#about-menu0 a').click(function (e) {
		  e.preventDefault(); $(this).tab('show');
		})
	});
	</script>
	<ul id='about-menu0' class='nav nav-tabs'>
		<li class='active'> 
			<a href='#over'>Overview</a>
		</li>
		<li><a href='#docs'> Documents</a> </li>
		<li><a href='#prop'> Proponents</a></li>
		<li><a href='#cont'> Contact</a> </li>
	</ul>
	<div class="tab-content" style='height:200px;margin-left:-2%;padding-left:5%;padding-top:-10%'>
	  <div class="tab-pane active" id="over">
		<h3>Overview</h3>
	  </div>
	  <div class="tab-pane" id="docs">
		<h3>Documents</h3>
	  ...</div>
	  <div class="tab-pane" id="prop">
		<h3>Proponents</h3>
	  ...</div>
	  <div class="tab-pane" id="cont">
		<h3>Contact</h3>
	  ...</div>
	</div>
</div>