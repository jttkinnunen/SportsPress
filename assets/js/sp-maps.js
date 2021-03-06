(function($) {
	function sp_maps() {
		$maps = $('.sp-google-map');
		$maps.each(function() {
			$self = $(this);
			address = $self.attr('data-address');
			latitude = $self.attr('data-latitude');
			longitude = $self.attr('data-longitude');
			var ll = new google.maps.LatLng(latitude,longitude);
			var mapOptions = {
				scrollwheel: false,
				zoom: 12,
				center: ll
			};
			var map = new google.maps.Map($self[0], mapOptions)
			var marker = new google.maps.Marker({
				position: ll,
				map: map,
				animation: google.maps.Animation.DROP,
				flat: true,
				title: address
			});
		});
	}
	google.maps.event.addDomListener(window, "load", sp_maps);
})(jQuery);