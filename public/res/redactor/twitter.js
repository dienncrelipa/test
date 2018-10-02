/**
 * Created by admin on 7/8/16.
 */
(function ($) {

	$.Redactor.prototype.twitter = function () {
		return {
			init: function () {
				this.twitter.cache = {};
				var button = this.button.addAfter('image', 'twitter', 'Twitter');

				//var button = this.button.add('twitter', 'Twitter');
				this.button.addCallback(button, this.twitter.show);

				// Patch old twitter iframe
				this.twitter.initPatch();
			},
			initPatch: function()
			{
				var iframeArray = $('iframe.twitter-iframe').toArray();
				iframeArray = iframeArray.map(function(e){
					if(!$(e).parent().hasClass('redactor-editor')) {
						return null;
					}

					return e;
				});

				var positionMap = {};

				$.each(iframeArray, function(k, e){
					if(e == null) {
						return true;
					}

					var topOfBottom = $(e).position().top + $(e).height();

					if(positionMap[topOfBottom] === undefined) {
						positionMap[topOfBottom] = [];
					}

					positionMap[topOfBottom].push(e);
				});

				for(var topOfBottom in positionMap) {
					if(!$.isArray(positionMap[topOfBottom])) {
						continue;
					}

					$(positionMap[topOfBottom]).wrapAll($('<p />'));
				}
			},
			show: function () {

				this.modal.addTemplate('twitter', this.twitter.getTemplate());
				this.modal.load('twitter', 'Twitter', 700);

				var button = this.modal.getActionButton();
				button.on('click', this.twitter.insert);

				this.modal.show();
				$('#ipt-redactor-twitter-url').focus();
			},
			getTemplate: function () {
				return String()
					+ '<div class="modal-section" id="redactor-modal-twitter-link">'
					+ '<section>'
					+ '<label>TwitterURL</label>'
					+ '<input type="url" id="ipt-redactor-twitter-url" placeholder="TwitterURLを入力" />'
					+ '</section>'
					+ '<section>'
					+ '<button id="redactor-modal-button-action">挿入</button>'
					+ '<button id="redactor-modal-button-cancel">キャンセル</button>'
					+ '</section>'
					+ '</div>';
			},
			insert: function () {
				var tweet_url = $('#ipt-redactor-twitter-url').val();
				tweet_url = encodeURIComponent(tweet_url);

				this.modal.close();

				this.insert.html('<p><iframe scrolling="no" class="twitter-iframe" onload="this.setAttribute(\'data-tweet\', Math.round(Math.random() * 1e9));this.contentWindow.postMessage({element: this.getAttribute(\'data-tweet\'),query: \'height\'},this.getAttribute(\'src\'));(function() {var that = this;window.addEventListener(\'resize\', function() {that.contentWindow.postMessage({element:that.getAttribute(\'data-tweet\'),query: \'height\'},that.getAttribute(\'src\'));});window.addEventListener(\'message\', function(e) {if(e.data.element != that.getAttribute(\'data-tweet\')){ return; }that.style.height = e.data.height + \'px\';});}).call(this)" style="border: none; height: 400px; max-width: 500px; width: 100%; margin: 0;" src="'+twitterIframeUrl+'?url='+tweet_url+'"></iframe></p>');

			},
		};
	}
})(jQuery);