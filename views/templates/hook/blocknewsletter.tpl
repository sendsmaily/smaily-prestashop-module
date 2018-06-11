<h3>Newsletter</h3>
<div class="smailey_newsletter">

  <form action="" id="target" method="POST">
    <input type="text" value="" name="email" placeholder="Your email" required />
    <input type="text"  value="" name="name" placeholder="Your name.." required />
    <input type="submit" value="Subscribe" id="newsletter_submit">
  </form>
  <div class="alert"></div>

</div>

<script>
$("#newsletter_submit").click(function(event) {
	$('.alert').hide();
    event.preventDefault();
	var form_data = $('#target').serialize();
	$.ajax({
		type: "POST",
		url: '?fc=module&module=smaily&controller=subscribe&',
		data: form_data,
		success: function(res){
		  if( res.message == 'OK' ){
			$('#target').find('input[type="text"]').val('');
			$('.alert').html('Thanks for subscribing us !').show();
		  }	else
			  $('.alert').html('Error: '+res.message).show();
		},
		error: function() {
		 $('.alert').alert('Error: Unable to submit your request, try again').show();
			
		}
	});
	
});
</script>
<style>
	.smailey_newsletter {
    float: left;
    padding-top: 20px;

}
h3 {

    float: left;
    color: #fff;
    padding: 0 16px 0 0;

}
.alert {
    padding: 0px;
	  color: turquoise;
    font-size: 18px;
    padding: 7px 0px 0px 0px;
}

</style>