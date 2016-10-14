var mapzen = mapzen || {};
mapzen.whosonfirst = mapzen.whosonfirst || {};
mapzen.whosonfirst.photos = mapzen.whosonfirst.photos || {};

// for the time being this assumes jQuery is present
// that decision may change some day but not today
// (20160121/thisisaaronland)

mapzen.whosonfirst.photos.edit = (function() {

	var self = {

		init: function(){
			$('.wof-save-target img').click(function(e){
				self.photo_save(e.target);
			});
		},

		photo_save(el){
			var $figure = $(el).closest('figure');

			var onsuccess = function(){
				$figure.addClass('wof-saved');
			};

			var onerror = function(){
				// Something something log error
			};

			var data = {
				wof_id: $('input[name="wof_id"]').val(),
				ext_id: $figure.data('ext-id'),
				type: $figure.data('type')
			};
			mapzen.whosonfirst.photos.api.api_call("wof.photos_save", data, onsuccess, onerror);
		}

	};

	return self;

})();

$(document).ready(function(){
	mapzen.whosonfirst.photos.edit.init();
});
