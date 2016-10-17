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
			$('.set-primary').click(function(e) {
				e.preventDefault();
				self.set_primary(e.target);
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
		},

		set_primary: function(link) {

			var $target = $(link).closest('figure');

			var onsuccess = function() {
				var $curr = $('#primary-photo-container figure');
				$curr.addClass('wof-thumb');
				$('#secondary-photo-container').prepend($curr);
				$('#primary-photo-container').append($target);
				$target.removeClass('wof-thumb');
			};

			var onerror = function() {
				alert('Oops, something went wrong.');
			}

			var data = {
				wof_id: $('#wof_name').data('id'),
				photo_id: $target.data('id')
			};
			mapzen.whosonfirst.photos.api.api_call("wof.photos_set_primary", data, onsuccess, onerror);
		}

	};

	return self;

})();

$(document).ready(function(){
	mapzen.whosonfirst.photos.edit.init();
});
