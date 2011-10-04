(function() {
	tinymce.create('tinymce.plugins.imageNext', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			// Register commands
			ed.addCommand('mceimageNext', function() {
				imageselector();
			});

			// Register buttons
			ed.addButton('imageNext', {title : 'imageNext.image_desc', cmd : 'mceimageNext'});


		},

		getInfo : function() {
		}

		// Private methods
	});

	// Register plugin
	tinymce.PluginManager.add('imageNext', tinymce.plugins.imageNext);
})();